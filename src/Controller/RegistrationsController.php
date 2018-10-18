<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use Cake\Utility\Security;

/**
 * Registrations Controller
 *
 * @property \App\Model\Table\RegistrationsTable $Registrations
 */
class RegistrationsController extends AppController
{
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        $this->Auth->allow(['cancel', 'event', 'view']);

        $this->Crud->mapAction('event', 'Crud.Add');
        $this->Crud->mapAction('accept', 'Crud.Edit');
        $this->Crud->mapAction('cancel', 'Crud.Edit');
        $this->Crud->mapAction('reject', 'Crud.Edit');

        $this->Crud->disable(['Add', 'Edit', 'Index', 'Delete']);
    }

    public function isAuthorized($user = null)
    {
        $regId = (int) $this->request->params['pass'][0];

        if (in_array($this->request->getParam('action'), ['accept', 'cancel', 'reject', 'view'])) {
            if (isset($user['samaccountname'])) {
                $registration = $this->Registrations->get($regId, [
                    'contain' => ['Events']
                ]);

                if ($user['samaccountname'] == $registration->event->created_by) {
                    return true;
                }
            }
        }
        
        $this->set('isAdmin', parent::inAdminstrativeGroup($user, 'Calendar Admins'));

        return $this->Registrations->isOwnedBy($regId, [
            'ad_username' => (isset($user['samaccountname']) ? $user['samaccountname'] : null),
            'edit_key' => (isset($this->request->query['edit_key']) ? $this->request->query['edit_key'] : null)
        ]) || parent::isAuthorized($user);
    }

    public function event($eventId = null)
    {
        if ($this->Auth->user() &&
            $this->Registrations->exists(['event_id' => $eventId, 'ad_username' => $this->Auth->user('samaccountname')])
        ) {
            $registration = $this->Registrations->find('all')
                ->select(['id'])
                ->where([
                    'event_id' => $eventId,
                    'ad_username' => $this->Auth->user('samaccountname')
                ])
                ->first();

            return $this->redirect(['action' => 'view', $registration->id]);
        }

        $this->Events = TableRegistry::get('Events');
        $now = new Time();
        if (!$this->Events->exists([
            'id' => $eventId,
            'status' => 'approved',
            'part_of_id IS NULL',
        ])) {
            return $this->redirect($this->referer());
        }

        $event = $this->Events->get($eventId);
        $now = new Time();
        $cutoff = new Time($event->attendee_cancellation);
        $cutoff->addMinutes($event->extend_registration);

        if ($now > $cutoff) {
            return $this->redirect($this->referer());
        }

        $this->Crud->on('beforeRender', function (\Cake\Event\Event $event) {
            $eventInfo = $this->Events->get($this->passedArgs[0], [
                'contain' => 'RequiresPrerequisites'
            ]);
            $this->set('event', $eventInfo);
            $this->set('continuedDates',
                $this->Events->find('all')
                    ->select(['class_number', 'event_start', 'event_end'])
                    ->where(['part_of_id' => $this->passedArgs[0]])
                    ->order('class_number ASC')
                    ->toArray()
            );
            $this->set('authUser', $this->Auth->user());

            if (!empty($eventInfo->requires_prerequisite)) {
                $this->set('meetsPreq', parent::inAdminstrativeGroup($this->Auth->user(), $eventInfo->requires_prerequisite->ad_group));
            }

            $paidSpacesAvailable = $this->Events->hasPaidSpaces($this->passedArgs[0]);

            if ($paidSpacesAvailable) {
                $this->__configureBraintree();
                $this->set('clientToken', \Braintree_ClientToken::generate());
            }

            $this->set('hasFreeSpaces', $this->Events->hasFreeSpaces($this->passedArgs[0]));
            $this->set('hasPaidSpaces', $paidSpacesAvailable);
            $this->set('editKey', bin2hex(Security::randomBytes(16)));
        });

        $this->Crud->on('beforeSave', function (\Cake\Event\Event $event) {
            $this->Events = TableRegistry::get('Events');

            if ($event->getSubject()->entity->type == 'paid') {
                if (!$this->Events->hasPaidSpaces($event->getSubject()->entity->event_id)) {
                    $this->Flash->error('This event no longer has any paid spaces available. You have not been charged.');
                    $event->getSubject()->entity->errors('type', ['This event no longer has any paid spaces available.']);
                    $event->stopPropagation();
                } else {
                    $nameParts = explode(' ', $event->getSubject()->entity->name);
                    $firstName = $nameParts[0];
                    $lastName = '';
                    for ($i = 1; $i < count($nameParts); $i++) {
                        $lastName .= $nameParts[$i] . ' ';
                    }

                    $eventData = $this->Events->get($event->getSubject()->entity->event_id);

                    $this->__configureBraintree();
                    $result = \Braintree_Transaction::sale([
                        'amount' => $event->getSubject()->entity->cost,
                        'paymentMethodNonce' => $event->getSubject()->entity->payment_method_nonce,
                        'options' => ['submitForSettlement' => true],
                        'customer' => [
                            'email' => $event->getSubject()->entity->email,
                            'phone' => $event->getSubject()->entity->phone,
                            'firstName' => $firstName,
                            'lastName' => $lastName
                        ],
                        'customFields' => [
                            'event_id' => $event->getSubject()->entity->event_id,
                            'event_name' => $eventData->name
                        ]
                    ]);

                    if (isset($result->success) && $result->success) {
                        $event->getSubject()->entity->transaction_id = $result->transaction->id;
                    } else {
                        $this->Flash->error($result->message);
                        $event->getSubject()->entity->errors('transaction_id', [$result->message]);
                        $event->stopPropagation();
                    }
                }
            } else {
                if (!$this->Events->hasFreeSpaces($event->getSubject()->entity->event_id)) {
                    $this->Flash->error('This event no longer has any free spaces available.');
                    $event->getSubject()->entity->errors('type', ['This event no longer has any free spaces available.']);
                    $event->stopPropagation();
                }
            }
        });

        $this->Crud->on('afterSave', function (\Cake\Event\Event $event) {
            $this->Events = TableRegistry::get('Events');
            $eventReference = $this->Events->get($event->getSubject()->entity->event_id, [
                'contain' => 'Contacts'
            ]);
            $time = new Time($eventReference->event_start);
            $formattedTime = $time->i18nFormat('EEEE MMMM d, h:mma', 'America/Chicago');

            Email::configTransport('sparkpost', [
                'className' => 'SparkPost.SparkPost',
                'apiKey' => Configure::read('SparkPost.Api.key')
            ]);

            try {
                $message = $event->getSubject()->entity->name . ",<br/><br/>You're confirmed for an event! Keep this email for your records.<br/><br/>" . $eventReference->name . "<br/>" . $formattedTime . "<br/><br/>If you need to review or cancel your RSVP you can do so at https://calendar.dallasmakerspace.org/registrations/view/" . $event->getSubject()->entity->id . "?edit_key=" . $event->getSubject()->entity->edit_key . "<br/><br/>Dallas Makerspace";
                $subject = 'Event Confirmation: ' . $eventReference->name;

                if ($eventReference->attendees_require_approval) {
                    $message = $event->getSubject()->entity->name . ",<br/><br/>You're registration has been submitted for an event. The event host will need to accept your RSVP before you will be confirmed for the event. A follow up notification will be sent once your registration is approved or rejected.<br/><br/>" . $eventReference->name . "<br/>" . $formattedTime . "<br/><br/>If you need to review or cancel your RSVP you can do so at https://calendar.dallasmakerspace.org/registrations/view/" . $event->getSubject()->entity->id . "?edit_key=" . $event->getSubject()->entity->edit_key . "<br/><br/>Dallas Makerspace";
                    $subject = 'Event Pending: ' . $eventReference->name;
                }

                $email = new Email();
                $email->transport('sparkpost');
                $email->from(['admin@dallasmakerspace.org' => 'Dallas Makerspace']);
                $email->to([$event->getSubject()->entity->email => $event->getSubject()->entity->name]);
                $email->subject($subject);
                $email->send($message);
            } catch (\Exception $e) {
                $this->log($e);
            }

            // Notify event host
            if ($eventReference->attendees_require_approval) {
                try {
                    $message = $eventReference->contact->name . ",<br/><br/>Someone new has requested to attend " . $eventReference->name . ".<br/><br/>" . $event->getSubject()->entity->name . "<br/>" . $event->getSubject()->entity->email . "<br/><br/>Approve or deny this request at your earliest convenience at https://calendar.dallasmakerspace.org/events/view/" . $eventReference->id . "<br/><br/>Dallas Makerspace";

                    $email = new Email();
                    $email->transport('sparkpost');
                    $email->from(['admin@dallasmakerspace.org' => 'Dallas Makerspace']);
                    $email->to([$eventReference->contact->email => $eventReference->contact->name]);
                    $email->subject('Attendance Request: ' . $eventReference->name);
                    $email->send($message);
                } catch (\Exception $e) {
                    $this->log($e);
                }
            }
        });

        $this->Crud->on('beforeRedirect', function (\Cake\Event\Event $event) {
            $event->getSubject()->url = [
                'action' => 'view',
                $event->getSubject()->entity->id
            ];

            if (!$event->getSubject()->entity->ad_username) {
                $event->subject->url['?'] = ['edit_key' => $event->getSubject()->entity->edit_key];
            }
        });

        return $this->Crud->execute();
    }

    public function view($id = null)
    {
        // Manual check in method to allow non-members to edit their data via an edit key
        if (!$this->isAuthorized($this->Auth->user())) {
            return $this->redirect($this->referer());
        }

        $this->Crud->on('beforeFind', function (\Cake\Event\Event $event) {
            $event->getSubject()->query->contain(['Events']);
        });

        return $this->Crud->execute();
    }

    public function cancel($id = null)
    {
        // Manual check in method to allow non-members to edit their data via an edit key
        if (!$this->isAuthorized($this->Auth->user())) {
            return $this->redirect($this->referer());
        }

        $this->Crud->on('beforeFind', function (\Cake\Event\Event $event) {
            $event->getSubject()->query->contain(['Events']);
        });

        $this->Crud->on('beforeSave', function (\Cake\Event\Event $event) {
            $now = new Time();
            if ($now > $event->getSubject()->entity->event->attendee_cancellation && !parent::inAdminstrativeGroup($this->Auth->user(), 'Calendar Admins')) {
                $this->Flash->error('Your RSVP to this event could not be cancelled. The cutoff time for cancellations has already passed.');
                $event->stopPropagation();
            } else {
                $event->getSubject()->entity->status = 'cancelled';
                $this->Flash->success('Your RSVP to this event has been cancelled.');
                $this->Registrations->refund($this->passedArgs[0]);
            }
        });

        $this->Crud->on('afterSave', function (\Cake\Event\Event $event) {
            $this->Events = TableRegistry::get('Events');
            $eventReference = $this->Events->get($event->getSubject()->entity->event_id);

            Email::configTransport('sparkpost', [
                'className' => 'SparkPost.SparkPost',
                'apiKey' => Configure::read('SparkPost.Api.key')
            ]);

            try {
                $message = $event->getSubject()->entity->name . ",<br/><br/>Your RSVP for the following event has been cancelled.<br/><br/>" . $eventReference->name . "<br/><br/>If you paid to register for this event then a refund has been submitted for processing.<br/><br/>Dallas Makerspace";

                $email = new Email();
                $email->transport('sparkpost');
                $email->from(['admin@dallasmakerspace.org' => 'Dallas Makerspace']);
                $email->to([$event->getSubject()->entity->email => $event->getSubject()->entity->name]);
                $email->subject('Event Cancellation: ' . $eventReference->name);
                $email->send($message);
            } catch (\Exception $e) {
                $this->log($e);
            }
        });

        $this->Crud->on('beforeRedirect', function (\Cake\Event\Event $event) {
            $event->getSubject()->url = [
                'action' => 'view',
                $event->getSubject()->entity->id
            ];

            if (!$event->getSubject()->entity->ad_username) {
                $event->subject->url['?'] = ['edit_key' => $event->getSubject()->entity->edit_key];
            }
        });

        return $this->Crud->execute();
    }

    public function accept($id = null)
    {
        $this->request->allowMethod(['POST']);

        $this->Crud->on('beforeFind', function (\Cake\Event\Event $event) {
            $event->getSubject()->query->contain(['Events']);
        });

        $this->Crud->on('beforeSave', function (\Cake\Event\Event $event) {
            $now = new Time();
            $cutoff = new Time($event->getSubject()->entity->event->attendee_cancellation);
            $cutoff->addMinutes($event->getSubject()->entity->event->extend_registration);
            if ($now > $cutoff) {
                $this->Flash->error('RSVPs for this event can no longer be approved. The cutoff time for cancellations has already passed.');
                $event->stopPropagation();
            } else {
                $event->getSubject()->entity->status = 'confirmed';
            }
        });

        $this->Crud->on('afterSave', function (\Cake\Event\Event $event) {
            $this->Events = TableRegistry::get('Events');
            $eventReference = $this->Events->get($event->getSubject()->entity->event_id);
            $time = new Time($eventReference->event_start);
            $formattedTime = $time->i18nFormat('EEEE MMMM d, h:mma', 'America/Chicago');

            Email::configTransport('sparkpost', [
                'className' => 'SparkPost.SparkPost',
                'apiKey' => Configure::read('SparkPost.Api.key')
            ]);

            try {
                $message = $event->getSubject()->entity->name . ",<br/><br/>You've been approved to attend an event! Keep this email for your records.<br/><br/>" . $eventReference->name . "<br/>" . $formattedTime . "<br/><br/>If you need to review or cancel your RSVP you can do so at https://calendar.dallasmakerspace.org/registrations/view/" . $event->getSubject()->entity->id . "?edit_key=" . $event->getSubject()->entity->edit_key . "<br/><br/>Dallas Makerspace";

                $email = new Email();
                $email->transport('sparkpost');
                $email->from(['admin@dallasmakerspace.org' => 'Dallas Makerspace']);
                $email->to([$event->getSubject()->entity->email => $event->getSubject()->entity->name]);
                $email->subject('Update: You have been approved to attend ' . $eventReference->name);
                $email->send($message);
            } catch (\Exception $e) {
                $this->log($e);
            }
        });

        $this->Crud->on('beforeRedirect', function (\Cake\Event\Event $event) {
            $event->getSubject()->url = $this->referer();
        });

        return $this->Crud->execute();
    }

    public function reject($id = null)
    {
        $this->request->allowMethod(['POST']);

        $this->Crud->on('beforeFind', function (\Cake\Event\Event $event) {
            $event->getSubject()->query->contain(['Events']);
        });

        $this->Crud->on('beforeSave', function (\Cake\Event\Event $event) {
            $now = new Time();
            if ($now > $event->getSubject()->entity->event->attendee_cancellation) {
                $this->Flash->error('RSVPs for this event can no longer be rejected. The cutoff time for cancellations has already passed.');
                $event->stopPropagation();
            } else {
                $event->getSubject()->entity->status = 'rejected';
                $this->Registrations->refund($this->passedArgs[0]);
            }
        });

        $this->Crud->on('afterSave', function (\Cake\Event\Event $event) {
            $this->Events = TableRegistry::get('Events');
            $eventReference = $this->Events->get($event->getSubject()->entity->event_id);

            Email::configTransport('sparkpost', [
                'className' => 'SparkPost.SparkPost',
                'apiKey' => Configure::read('SparkPost.Api.key')
            ]);

            try {
                $message = $event->getSubject()->entity->name . ",<br/><br/>Your RSVP for the following event has been cancelled due to the event organizer rejecting your registration.<br/><br/>" . $eventReference->name . "<br/><br/>If you paid to register for this event then a refund has been submitted for processing.<br/><br/>Dallas Makerspace";

                $email = new Email();
                $email->transport('sparkpost');
                $email->from(['admin@dallasmakerspace.org' => 'Dallas Makerspace']);
                $email->to([$event->getSubject()->entity->email => $event->getSubject()->entity->name]);
                $email->subject('Update: Your request to attend ' . $eventReference->name . ' has been rejected');
                $email->send($message);
            } catch (\Exception $e) {
                $this->log($e);
            }
        });

        $this->Crud->on('beforeRedirect', function (\Cake\Event\Event $event) {
            $event->getSubject()->url = $this->referer();
        });

        return $this->Crud->execute();
    }

    private function __configureBraintree()
    {
        \Braintree_Configuration::environment(Configure::read('Braintree.environment'));
        \Braintree_Configuration::merchantId(Configure::read('Braintree.merchantId'));
        \Braintree_Configuration::publicKey(Configure::read('Braintree.publicKey'));
        \Braintree_Configuration::privateKey(Configure::read('Braintree.privateKey'));
    }
}
