<?php

namespace App\Controller;

use App\Model\Table\RegistrationsTable;
use Braintree_ClientToken;
use Braintree_Configuration;
use Braintree_Transaction;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\Utility\Security;

/**
 * Registrations Controller
 *
 * @property RegistrationsTable $Registrations
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

    public function event($eventId = null)
    {
        if ($this->Auth->user() && $this->Registrations->exists([
                'event_id' => $eventId,
                'ad_username' => $this->Auth->user('samaccountname')
            ])) {
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

        $this->Crud->on('beforeRender', function (Event $event) {
            $eventInfo = $this->Events->get($this->passedArgs[0], [
                'contain' => 'RequiresPrerequisites'
            ]);
            $this->set('event', $eventInfo);
            $this->set(
                'continuedDates',
                $this->Events->find('all')
                    ->select(['class_number', 'event_start', 'event_end'])
                    ->where(['part_of_id' => $this->passedArgs[0]])
                    ->order('class_number ASC')
                    ->toArray()
            );
            $this->set('authUser', $this->Auth->user());

            if (!empty($eventInfo->requires_prerequisite)) {
                if ($this->Auth->user()['ssologin']) {
                    $this->set('meetsPreq', parent::currentUserInGroup($eventInfo->requires_prerequisite->ad_group, /* $forceRefreshGroups= */ true));
                } else {
                    $this->set('meetsPreq', parent::inAdminstrativeGroup($this->Auth->user(), $eventInfo->requires_prerequisite->ad_group));
                }
            }

            $paidSpacesAvailable = $this->Events->hasPaidSpaces($this->passedArgs[0]);

            if ($paidSpacesAvailable) {
                $this->__configureBraintree();
                $this->set('clientToken', Braintree_ClientToken::generate());
            }

            $this->set('hasFreeSpaces', $this->Events->hasFreeSpaces($this->passedArgs[0]));
            $this->set('hasPaidSpaces', $paidSpacesAvailable);
            $this->set('editKey', bin2hex(Security::randomBytes(16)));
        });

        $this->Crud->on('beforeSave', function (Event $event) {
            $this->Events = TableRegistry::get('Events');
            $registration = $event->getSubject()->entity;
            if ($registration->type == 'paid') {
                if (!$this->Events->hasPaidSpaces($registration->event_id)) {
                    $this->Flash->error('This event no longer has any paid spaces available. You have not been charged.');
                    $registration->errors('type', ['This event no longer has any paid spaces available.']);
                    $event->stopPropagation();
                } else {
                    $nameParts = explode(' ', $registration->name);
                    $firstName = $nameParts[0];
                    $lastName = '';
                    $partCount = count($nameParts);
                    for ($i = 1; $i < $partCount; $i++) {
                        $lastName .= $nameParts[$i] . ' ';
                    }

                    $eventData = $this->Events->get($registration->event_id);

                    $this->__configureBraintree();
                    $result = Braintree_Transaction::sale([
                        'amount' => $eventData->cost,
                        'paymentMethodNonce' => $registration->payment_method_nonce,
                        'options' => ['submitForSettlement' => true],
                        'customer' => [
                            'email' => $registration->email,
                            'phone' => $registration->phone,
                            'firstName' => $firstName,
                            'lastName' => $lastName
                        ],
                        'customFields' => [
                            'event_id' => $registration->event_id,
                            'event_name' => $eventData->name
                        ]
                    ]);

                    if (isset($result->success) && $result->success) {
                        $event->getSubject()->entity->transaction_id = $result->transaction->id;
                    } else {
                        $this->Flash->error($result->message);
                        $event->getSubject()->entity->errors('transaction_id', [$result->message]);
                        //$event->stopPropagation();
                    }
                }
            } else {
                if (!$this->Events->hasFreeSpaces($registration->event_id)) {
                    $this->Flash->error('This event no longer has any free spaces available.');
                    $$registration->errors('type', ['This event no longer has any free spaces available.']);
                    $event->stopPropagation();
                }
            }
        });

        $this->Crud->on('afterSave', function (Event $event) {
            $this->Events = TableRegistry::get('Events');
            /** @var \App\Model\Entity\Event $eventReference */
            $eventReference = $this->Events->get(
                $event->getSubject()->entity->event_id,
                ['contain' => 'Contacts']
            );
            $registration = $event->getSubject()->entity;

            if ($eventReference->attendees_require_approval) {
                $this->Email->sendRegistrationPending(
                    $registration, //Registration
                    $eventReference //Event
                );

                $this->Email->sendRegistrationRequested(
                    $registration, //Registration
                    $eventReference, //Event
                );

            } else {
                $this->Email->sendRegistrationConfirmation(
                    $registration, //Registration
                    $eventReference //Event
                );

                if ($eventReference->notifyInstructorRegistrations) {
                    $this->Email->sendRegistrationToInstructor(
                        $registration, //Registration
                        $eventReference //Event
                    );
                }
            }
        });

        $this->Crud->on('beforeRedirect', function (Event $event) {
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

    private function __configureBraintree()
    {
        Braintree_Configuration::environment(Configure::read('Braintree.environment'));
        Braintree_Configuration::merchantId(Configure::read('Braintree.merchantId'));
        Braintree_Configuration::publicKey(Configure::read('Braintree.publicKey'));
        Braintree_Configuration::privateKey(Configure::read('Braintree.privateKey'));
    }

    public function view($id = null)
    {
        // Manual check in method to allow non-members to edit their data via an edit key
        if (!$this->isAuthorized($this->Auth->user())) {
            return $this->redirect($this->referer());
        }

        $this->Crud->on('beforeFind', function (Event $event) {
            $event->getSubject()->query->contain(['Events']);
        });

        return $this->Crud->execute();
    }

    public function isAuthorized($user = null)
    {
        $regId = (int)$this->request->params['pass'][0];

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

    public function cancel($id = null)
    {
        // Manual check in method to allow non-members to edit their data via an edit key
        if (!$this->isAuthorized($this->Auth->user())) {
            return $this->redirect($this->referer());
        }

        $this->Crud->on('beforeFind', function (Event $event) {
            $event->getSubject()->query->contain(['Events']);
        });

        $this->Crud->on('beforeSave', function (Event $event) {
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

        $this->Crud->on('afterSave', function (Event $event) {
            $this->Events = TableRegistry::get('Events');
            /** @var \App\Model\Entity\Event $eventReference */
            $eventReference = $this->Events->get(
                $event->getSubject()->entity->event_id,
                ['contain' => 'Contacts']
            );
            $registration = $event->getSubject()->entity;

            $this->Email->sendRegistrationCancelled($registration, $eventReference);

            if ($eventReference->notifyInstructorCancellations) {
                $this->Email->sendCancellationToInstructor(
                    $registration, //Registration
                    $eventReference //Event
                );
            }
        });

        $this->Crud->on('beforeRedirect', function (Event $event) {
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

        $this->Crud->on('beforeFind', function (Event $event) {
            $event->getSubject()->query->contain(['Events']);
        });

        $this->Crud->on('beforeSave', function (Event $event) {
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

        $this->Crud->on('afterSave', function (Event $event) {
            $this->Events = TableRegistry::get('Events');
            $eventReference = $this->Events->get($event->getSubject()->entity->event_id);
            $registration = $event->getSubject()->entity;
            $this->Email->sendRegistrationApproved($registration, $eventReference);
        });

        $this->Crud->on('beforeRedirect', function (Event $event) {
            $event->getSubject()->url = $this->referer();
        });

        return $this->Crud->execute();
    }

    public function reject($id = null)
    {
        $this->request->allowMethod(['POST']);

        $this->Crud->on('beforeFind', function (Event $event) {
            $event->getSubject()->query->contain(['Events']);
        });

        $this->Crud->on('beforeSave', function (Event $event) {
            $now = new Time();
            if ($now > $event->getSubject()->entity->event->attendee_cancellation) {
                $this->Flash->error('RSVPs for this event can no longer be rejected. The cutoff time for cancellations has already passed.');
                $event->stopPropagation();
            } else {
                $event->getSubject()->entity->status = 'rejected';
                $this->Registrations->refund($this->passedArgs[0]);
            }
        });

        $this->Crud->on('afterSave', function (Event $event) {
            $this->Events = TableRegistry::get('Events');
            $eventReference = $this->Events->get($event->getSubject()->entity->event_id);
            $registration = $event->getSubject()->entity;
            $this->Email->sendRegistrationRejected($registration, $eventReference);
        });

        $this->Crud->on('beforeRedirect', function (Event $event) {
            $event->getSubject()->url = $this->referer();
        });

        return $this->Crud->execute();
    }
}
