<?php
namespace App\Controller;

use Adldap\Adldap;
use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\Log\Log;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use \FeedIo\Factory;
use \FeedIo\Feed;
use FeedIo\Feed\Node;
use Cake\View\View;

/**
 * Events Controller
 *
 * @property \App\Model\Table\EventsTable $Events
 */
class EventsController extends AppController
{

    public $paginate = ['sortWhitelist' => ['Events.event_start','Events.created']];

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        $this->Auth->allow(['calendar', 'cron', 'embed', 'feed', 'index', 'view']);

        $this->Crud->mapAction('all', 'Crud.Index');
        $this->Crud->mapAction('attendance', 'Crud.Edit');
        $this->Crud->mapAction('assignments', 'Crud.Edit');
        $this->Crud->mapAction('attending', 'Crud.Index');
        $this->Crud->mapAction('calendar', 'Crud.Index');
        $this->Crud->mapAction('embed', 'Crud.Index');
        $this->Crud->mapAction('pending', 'Crud.Index');
        $this->Crud->mapAction('submitted', 'Crud.Index');
        $this->Crud->mapAction('approve', 'Crud.Edit');
        $this->Crud->mapAction('cancel', 'Crud.Edit');
        $this->Crud->mapAction('processRejection', 'Crud.View');
        $this->Crud->mapAction('reject', 'Crud.Edit');
        $this->Crud->mapAction('pendingHonoraria', 'Crud.Index');
        $this->Crud->mapAction('acceptedHonoraria', 'Crud.Index');
        $this->Crud->mapAction('rejectedHonoraria', 'Crud.Index');

        $this->Crud->disable(['Delete']);

        $this->Security->config('unlockedActions', ['exportHonoraria']);
    }

    public function isAuthorized($user = null)
    {
        if (in_array($this->request->action, ['add', 'attending', 'submitted'])) {
            return !is_null($user);
        }

        if (in_array($this->request->action, ['attendance', 'assignments', 'cancel', 'edit'])) {
            $eventId = (int) $this->request->params['pass'][0];
            return ($this->Events->isOwnedBy($eventId, $user['samaccountname']) || parent::isAuthorized($user));
        }

        // Calendar Admins only
        if ($this->request->action === 'pending' || $this->request->action === 'all') {
            return parent::isAuthorized($user);
        }

        // Honorarium Admins only
        if (in_array($this->request->action, ['acceptedHonoraria', 'pendingHonoraria', 'rejectedHonoraria'])) {
            return parent::inAdminstrativeGroup($user, 'Honorarium Admins');
        }

        // Finance Reporting only
        if (in_array($this->request->action, ['exportHonoraria', 'exportHonorariaCsv'])) {
            return parent::inAdminstrativeGroup($user, 'Financial Reporting');
        }

        if (in_array($this->request->action, ['approve', 'reject', 'processRejection'])) {
            $eventId = (int) $this->request->params['pass'][0];
            if ($this->Events->hasHonorarium($eventId)) {
                return parent::inAdminstrativeGroup($user, 'Honorarium Admins');
            }

            return parent::isAuthorized($user);
        }

        return false;
    }

    public function all()
    {
        $this->Crud->on('beforePaginate', function (\Cake\Event\Event $event) {
            if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
                $start_date = new \DateTime($_GET['start_date'] . ' 23:59:59', new \DateTimeZone('America/Chicago'));
                $start_date->setTimezone(new \DateTimeZone('UTC'));
                $end_date = new \DateTime($_GET['end_date'] . ' 23:59:59', new \DateTimeZone('America/Chicago'));
                $end_date->setTimezone(new \DateTimeZone('UTC'));

                $event->subject()->query
                    ->where([
                        'Events.part_of_id IS NULL',
                        'Events.event_start >=' => $start_date->format('Y-m-d H:i:s'),
                        'Events.event_start <=' => $end_date->format('Y-m-d H:i:s'),
                    ])
                    ->order(['Events.created' => 'DESC']);
            } else {
                $event->subject()->query
                    ->where([
                        'Events.part_of_id IS NULL'
                    ])
                    ->order(['Events.created' => 'DESC']);
            }
            $this->paginate['limit'] = 50;
        });




        return $this->Crud->execute();
    }

    public function attendance($id = null)
    {
        $this->Crud->on('beforeFind', function (\Cake\Event\Event $event) {
            $event->subject()->query->contain(['Registrations']);
        });

        $this->Crud->on('beforeRedirect', function (\Cake\Event\Event $event) {
            $event->subject()->url = $this->referer();
        });

        return $this->Crud->execute();
    }

    public function assignments($id = null)
    {
        $this->Crud->on('beforeFind', function (\Cake\Event\Event $event) {
            $event->subject()->query->contain(['FulfillsPrerequisites', 'Registrations']);
        });

        $this->Crud->on('beforeRedirect', function (\Cake\Event\Event $event) {
            $event->subject()->url = $this->referer();
        });

        $this->Crud->on('beforeSave', function (\Cake\Event\Event $event) {
            $adldap = new \Adldap\Adldap();
            $provider = new \Adldap\Connections\Provider(Configure::read('ActiveDirectory'));
            $adldap->addProvider('default', $provider);
            $adldap->connect('default');

            foreach ($event->subject()->entity->registrations as $registration) {
                if ($registration->ad_assigned && $registration->ad_username) {
                    $group = $provider->search()->groups()->find($event->subject()->entity->fulfills_prerequisite->ad_group);
                    $user = $provider->search()->find($registration->ad_username);
                    $result = $group->addMember($user);
                }
            }
        });

        return $this->Crud->execute();
    }

    public function feed($type="vcal")
    {
        // TODO: Accept arguments like calendar view and include location
        $this->autoRender = false;

        $today = new Time('America/Chicago');
        $today->startOfDay()->timezone('UTC');
        $now = Time::now();
        $events = $this->Events->find('all')
            ->select([
                'Events.id',
                'Events.event_start',
                'Events.event_end',
                'Events.name',
                'Events.room_id',
                'Events.short_description',
            	'Events.long_description',
                'Rooms.name',
            	'Contacts.name',
            	'Events.modified'
            ])
            ->where([
                'Events.event_start >=' => $today,
                'Events.status' => 'approved'
            ])
            ->contain(['Rooms','Contacts', 'Categories'])
            ->order(['event_start' => 'ASC']);

		if ($type === "vcal") {
	        echo "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//hacksw/handcal//NONSGML v1.0//EN\r\nCALSCALE:GREGORIAN\r\n";
	
	        foreach ($events as $event) {
	        	$event_url = Router::url(['controller' => 'Events', 'action' => 'view',	'id' => $event->id], true);
	        	
	            echo "BEGIN:VEVENT\r\n";
	            echo 'DTSTART:' . $this->__icsDate($event->event_start) . "\r\n";
	            echo 'DTEND:' . $this->__icsDate($event->event_end) . "\r\n";
	            echo 'DTSTAMP:' . $this->__icsDate($now) . "\r\n";
	            echo 'UID:dmsevtv3' . $event->id . "@calendar.dallasmakerspace.org\r\n";
	            echo 'SUMMARY:' . $this->__icsEscapeString($event->name) . "\r\n";
	            echo 'DESCRIPTION:' . $this->__icsEscapeString($event->short_description . ' Event details at '.$event_url) . "\r\n";
	            echo 'LOCATION:' . $event->room->name . "\r\n";
	            echo 'URL;VALUE=URI:' . $this->__icsEscapeString($event_url) . "\r\n";
	            echo "END:VEVENT\r\n";
	        }
	
	        echo 'END:VCALENDAR';
		}
		else {
			$feed = new Feed();
			
			//Setting the channel elements
			$feed->setTitle('Dallas Makerspace Events & Classes');
			$feed->setLink(Router::url('/', true));
			$feed->setDescription('Events and Classes avaliable at the Dallas Makerspace');
			
			foreach($events as $event) {
				$view = new View($this->request);
				$view->set('event', $event);
				$desc_html = $view->render('Events/feed_contents', false);
				
				$url = Router::url(['controller' => 'Events', 'action' => 'view', 'id' => $event->id], true);
				
				$feed_event = $feed->newItem();
				$feed_event->setTitle($event->name);
				$feed_event->setLink($url);
				//$feed_event->setLastModified(new \DateTime($event->modified));
				$feed_event->setLastModified(new \DateTime($event->event_start));
				$feed_event->setDescription($desc_html);
				$feed_event->setPublicId($url, false);
				
				$feed_author = $feed_event->newAuthor();
				$feed_author->getName($event->contact->name);
				$feed_author->setUri("");
				$feed_author->setEmail("");
				$feed_event->setAuthor($feed_author);
		
				foreach($event->categories as $category) {
					$feed_category = $feed_event->newCategory();
					$feed_category->setLabel($category->name);
					$feed_event->addCategory($feed_category);
				}				
				
				$feed->add($feed_event);
			}
			
			$feedIo = Factory::create()->getFeedIo();
			
			if ($type === "atom")
				echo $feedIo->format($feed, 'atom');
			else if ($type === "json")
				echo $feedIo->format($feed, 'json');
			else if ($type === "rss")
				echo $feedIo->format($feed, 'rss');
			else 
				echo $feedIo->format($feed, 'rss');
		}
    }
    
    public function index()
    {
        $this->Crud->on('beforePaginate', function (\Cake\Event\Event $event) {
            $today = new Time('America/Chicago');
            $today->startOfDay()->timezone('UTC');

            $event->subject()->query
                ->select([
                    'Events.id',
                    'Events.event_start',
                    'Events.event_end',
                    'Events.name',
                    'Events.cost',
                    'Events.short_description',
                    'Events.created',
                    'Rooms.id',
                    'Rooms.name'
                ])
                ->where([
                    'Events.event_start >=' => $today,
                    'Events.status' => 'approved'
                ])
                ->contain(['Rooms']);

            $this->__applyQueryFilters($event);

            $this->paginate['limit'] = 2147483647;
        });

        $this->Crud->on('afterPaginate', [$this, '_applyAddress']);

        $this->Crud->on('beforeRender', [$this, '_filterContent']);

        return $this->Crud->execute();
    }

    public function embed()
    {
        $this->Crud->on('beforePaginate', function (\Cake\Event\Event $event) {
            $today = new Time('America/Chicago');
            $today->startOfDay()->timezone('UTC');

            $event->subject()->query
                ->select([
                    'Events.id',
                    'Events.event_start',
                    'Events.event_end',
                    'Events.name',
                    'Events.short_description',
                    'Rooms.id',
                    'Rooms.name'
                ])
                ->where([
                    'Events.event_start >=' => $today,
                    'Events.status' => 'approved'
                ])
                ->contain(['Rooms'])
                ->order(['event_start' => 'ASC']);

            $this->__applyQueryFilters($event);

            $this->paginate['limit'] = 2147483647;
        });

        $this->Crud->on('afterPaginate', [$this, '_applyAddress']);

        $this->Crud->on('beforeRender', [$this, '_filterContent']);

        return $this->Crud->execute();
    }

    public function calendar($year = null, $month = null, $day = null)
    {
        $this->Crud->on('beforePaginate', function (\Cake\Event\Event $event) {
            $year = empty($this->passedArgs[0]) ? null : $this->passedArgs[0];
            $month = empty($this->passedArgs[1]) ? null : $this->passedArgs[1];
            $day = empty($this->passedArgs[2]) ? null : $this->passedArgs[2];

            $now = new Time('America/Chicago');
            $highlight = true;

            if ($day != null && $day >= 1 && $day <= 31) {
                $now->day($day);

                if ($year != null && $year != $now->year && $year >= 2016 && $year <= 2166) {
                    $now->year($year);
                    $highlight = false;
                }

                if ($month != null && $month != $now->month && $month >= 1 && $month <= 12) {
                    $now->month($month);
                    $highlight = false;
                }

                $start = new Time($now);
                $start->startOfDay();

                $end = new Time($now);
                $end->endOfDay();

                $this->render('index_daily');
            } else {
                $now->day(1);

                if ($year != null && $year != $now->year && $year >= 2016 && $year <= 2166) {
                    $now->year($year);
                    $highlight = false;
                }

                if ($month != null && $month != $now->month && $month >= 1 && $month <= 12) {
                    $now->month($month);
                    $highlight = false;
                }

                $start = new Time($now);
                $start->startOfMonth();

                $end = new Time($now);
                $end->endOfMonth();

                $this->set('currentMonth', $start->timezone('America/Chicago'));
                $this->set('startOfMonth', $start->dayOfWeek < 7 ? $start->dayOfWeek : 0);
            }

            $event->subject()->query
                ->select([
                    'Events.id',
                    'Events.event_start',
                    'Events.event_end',
                    'Events.name',
                    'Events.short_description',
                    'Rooms.id',
                    'Rooms.name'
                ])
                ->where([
                    'Events.event_start >=' => $start->timezone('UTC'),
                    'Events.event_start <=' => $end->timezone('UTC'),
                    'Events.status IN' => ['approved', 'completed'],
                ])
                ->contain(['Rooms'])
                ->order(['event_start' => 'ASC']);

            $this->__applyQueryFilters($event);

            $this->paginate['limit'] = 2147483647;

            $this->set('currentDate', $now);
            $this->set('highlight', $highlight);
        });

        $this->Crud->on('afterPaginate', [$this, '_applyAddress']);

        $this->Crud->on('beforeRender', [$this, '_filterContent']);

        return $this->Crud->execute();
    }

    public function submitted()
    {
        $this->Crud->on('beforePaginate', function (\Cake\Event\Event $event) {
            $today = new Time('America/Chicago');
            $today->startOfDay()->timezone('UTC');

            $event->subject()->query
                ->select([
                    'Events.id',
                    'Events.event_start',
                    'Events.event_end',
                    'Events.name',
                    'Events.short_description',
                    'Events.status',
                    'Rooms.id',
                    'Rooms.name'
                ])
                ->where([
                    //'Events.event_start >=' => $today,
                    'Events.status IN' => ['approved', 'cancelled', 'completed', 'pending', 'rejected'],
                    'Events.created_by' => $this->Auth->user('samaccountname')
                ])
                ->contain(['Rooms'])
                ->order(['event_start' => 'ASC']);

            $this->paginate['limit'] = 2147483647;
        });

        $this->Crud->on('afterPaginate', [$this, '_applyAddress']);

        return $this->Crud->execute();
    }

    public function attending()
    {
        $this->Crud->on('beforePaginate', function (\Cake\Event\Event $event) {
            $today = new Time('America/Chicago');
            $today->startOfDay()->timezone('UTC');

            $event->subject()->query
                ->select([
                    'Events.id',
                    'Events.event_start',
                    'Events.event_end',
                    'Events.name',
                    'Events.short_description',
                    'Rooms.id',
                    'Rooms.name'
                ])
                ->where([
                    //'Events.event_start >=' => $today,
                    'Events.status IN' => ['approved', 'completed', 'pending']
                ])
                ->innerJoinWith(
                    'Registrations', function ($q) {
                        return $q->where(['Registrations.ad_username' => $this->Auth->user('samaccountname')]);
                    }
                )
                ->contain(['Rooms'])
                ->order(['event_start' => 'ASC']);

            $this->paginate['limit'] = 2147483647;
        });

        $this->Crud->on('afterPaginate', [$this, '_applyAddress']);

        return $this->Crud->execute();
    }

    public function pending()
    {
        $this->Crud->on('beforePaginate', function (\Cake\Event\Event $event) {
            $event->subject()->query
                ->where([
                    'Events.part_of_id IS NULL',
                    'Events.status' => 'pending',
                    'Honoraria.id IS NULL'
                ])
                ->contain(['Honoraria'])
                ->order(['Events.created' => 'ASC']);

            $this->paginate['limit'] = 50;
        });

        return $this->Crud->execute();
    }

    public function exportHonoraria()
    {
        if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
            $this->Honoraria = TableRegistry::get('Honoraria');

            if (isset($_POST['paid']) && is_array($_POST['paid'])) {
                foreach ($_POST['paid'] as $id => $status) {
                    $honorarium = $this->Honoraria->get($id);
                    $honorarium->paid = $status;
                    $this->Honoraria->save($honorarium);
                }
            }

            $start_date = new \DateTime($_GET['start_date'] . ' 23:59:59', new \DateTimeZone('America/Chicago'));
            $start_date->setTimezone(new \DateTimeZone('UTC'));
            $end_date = new \DateTime($_GET['end_date'] . ' 23:59:59', new \DateTimeZone('America/Chicago'));
            $end_date->setTimezone(new \DateTimeZone('UTC'));

            $data = $this->Events->find('all')
                ->contain([
                    'Honoraria',
                    'Contacts',
                    'Honoraria.Committees',
                    'Registrations' => function ($q) {
                        return $q->where(['Registrations.attended' => 1]);
                    },
                    'OldRegistrations' => function ($q) {
                        return $q->where(['OldRegistrations.status' => 'confirmed']);
                    }
                ])
                ->where([
                    'Events.status' => 'completed',
                    'Events.event_start >=' => $start_date->format('Y-m-d H:i:s'),
                    'Events.event_start <=' => $end_date->format('Y-m-d H:i:s'),
                    'Honoraria.id IS NOT' => null
                ])
                ->order([
                    'Events.event_start' => 'ASC'
                ]);

            $this->set('honoraria', $data);
            $this->set('oldCutoff', new Time('2017-01-01 00:00', 'America/Chicago'));
        }
    }

    public function exportHonorariaCsv()
    {
        $export = array();
        $export[] = [
            'Event ID',
            'Event Time',
            'Event Name',
            'Committee',
            'Contact Name',
            'Contact Email',
            'Contact Phone',
            'Attendees',
            'Requirements Met',
            'Pay Contact?',
            'Paid'
        ];

        $oldCutoff = new Time('2017-01-01 00:00', 'America/Chicago');
        $start_date = new \DateTime($_GET['start_date'] . ' 23:59:59', new \DateTimeZone('America/Chicago'));
        $start_date->setTimezone(new \DateTimeZone('UTC'));
        $end_date = new \DateTime($_GET['end_date'] . ' 23:59:59', new \DateTimeZone('America/Chicago'));
        $end_date->setTimezone(new \DateTimeZone('UTC'));

        $data = $this->Events->find('all')
            ->contain([
                'Honoraria',
                'Contacts',
                'Honoraria.Committees',
                'Registrations' => function ($q) {
                    return $q->where(['Registrations.attended' => 1]);
                },
                'OldRegistrations' => function ($q) {
                    return $q->where(['OldRegistrations.status' => 'confirmed']);
                }
            ])
            ->where([
                'Events.status' => 'completed',
                'Events.event_start >=' => $start_date->format('Y-m-d H:i:s'),
                'Events.event_start <=' => $end_date->format('Y-m-d H:i:s'),
                'Honoraria.id IS NOT' => null
            ])
            ->order([
                'Events.event_start' => 'ASC'
            ]);

        $payTypes = [
            'Not Paid',
            'Paid',
            'Pending',
            'Missing Info',
            'Denied',
            'Paid by Script'
        ];

        foreach ($data as $row) {
            if (count($row->registrations) > 2) {
                $export[] = [
                    $row->id,
                    $row->event_start->i18nFormat('MM-dd-yyyy h:mma', 'America/Chicago'),
                    $row->name,
                    $row->honorarium->committee->name,
                    $row->contact->name,
                    $row->contact->email,
                    $row->contact->phone,
                    count($row->registrations),
                    'Honoraria Met',
                    ($row->honorarium->pay_contact ? 'Yes' : 'No'),
                    $payTypes[$row->honorarium->paid]
                ];
            } else {
                $export[] = [
                    $row->id,
                    $row->event_start->i18nFormat('MM-dd-yyyy h:mma', 'America/Chicago'),
                    $row->name,
                    $row->honorarium->committee->name,
                    $row->contact->name,
                    $row->contact->email,
                    $row->contact->phone,
                    count($row->registrations),
                    'Honoraria Not Met',
                    "Don't Pay",
                    'N/A'
                ];
            }
        }

        $_serialize = 'export';

        $this->viewBuilder()->className('CsvView.Csv');
        $this->set(compact('export', '_serialize'));
    }

    public function pendingHonoraria()
    {
        $this->Crud->on('beforePaginate', function (\Cake\Event\Event $event) {
            $event->subject()->query->where([
                'Events.part_of_id IS NULL',
                'Events.status' => 'pending',
                'Honoraria.id IS NOT NULL'
            ])
            ->contain(['Honoraria']);

            if (!$_GET['sort']) {
                $event->subject()->query->order(['event_start' => 'DESC']);
            }

            $this->paginate['limit'] = 50;
        });

        return $this->Crud->execute();
    }

    public function acceptedHonoraria()
    {
        $this->Crud->on('beforePaginate', function (\Cake\Event\Event $event) {
            $event->subject()->query->where([
                'Events.part_of_id IS NULL',
                'Events.status IN' => ['approved', 'completed'],
                'Honoraria.id IS NOT NULL'
            ])
            ->contain(['Honoraria']);

            if (!$_GET['sort']) {
                $event->subject()->query->order(['event_start' => 'DESC']);
            }

            $this->paginate['limit'] = 50;
        });

        return $this->Crud->execute();
    }

    public function rejectedHonoraria()
    {
        $this->Crud->on('beforePaginate', function (\Cake\Event\Event $event) {
            $event->subject()->query->where([
                'Events.part_of_id IS NULL',
                'Events.status' => 'rejected',
                'Honoraria.id IS NOT NULL'
            ])
            ->contain(['Honoraria']);

            if (!$_GET['sort']) {
                $event->subject()->query->order(['event_start' => 'DESC']);
            }

            $this->paginate['limit'] = 50;
        });

        return $this->Crud->execute();
    }

    public function approve($id = null)
    {
        $this->Crud->on('beforeSave', function (\Cake\Event\Event $event) {
            $event->subject()->entity->status = 'approved';
        });

        // Approve multi-part dates
        $this->Crud->on('afterSave', function (\Cake\Event\Event $event) {
            $this->Events->query()->update()
                ->set(['status' => 'approved'])
                ->where(['part_of_id' => $event->subject()->entity->id])
                ->execute();
        });

        return $this->Crud->execute();
    }

    public function processRejection($id = null)
    {
        return $this->Crud->execute();
    }

    public function reject($id = null)
    {
        Email::configTransport('sparkpost', [
            'className' => 'SparkPost.SparkPost',
            'apiKey' => Configure::read('SparkPost.Api.key')
        ]);

        $this->Crud->on('beforeSave', function (\Cake\Event\Event $event) {
            $event->subject()->entity->status = 'rejected';
            $event->subject()->entity->rejection_reason = $this->request->data('event.rejection_reason');
            $event->subject()->entity->rejected_by = $this->Auth->user('samaccountname');
        });

        // Reject multi-part dates
        $this->Crud->on('afterSave', function (\Cake\Event\Event $event) {
            $this->Events->query()->update()
                ->set(['status' => 'rejected'])
                ->where(['part_of_id' => $event->subject()->entity->id])
                ->execute();

            // Get contact info by created_by id
            $contact = $this->Events->Contacts
                ->find()
                ->where(['ad_username' => $event->subject()->entity->created_by])
                ->first();

            try {
                $rejection_reason = ($event->subject()->entity->rejection_reason ? $event->subject()->entity->rejection_reason : 'No additional information given.');
                $message = $contact->name . ",<br/><br/>The following even you submitted to the Dallas Makerspace Calendar has been rejected.<br/><br/>" . $event->subject()->entity->name . "<br/><br/>Reason: " . $rejection_reason . ".<br/><br/>Dallas Makerspace";

                $email = new Email();
                $email->transport('sparkpost');
                $email->from(['admin@dallasmakerspace.org' => 'Dallas Makerspace']);
                $email->to([$contact->email => $contact->name]);
                //$email->subject('DMS Event Rejection: ' . $event->subject()->entity->name);
                $email->subject('DMS Event Rejection: ' . (strlen($event->subject()->entity->name) > 45 ? substr($event->subject()->entity->name, 0, 45) . "..." : $event->subject()->entity->name));
                $email->send($message);
            } catch (\Exception $e) {
                $this->log($e);
            }
        });

        // TODO: Dispatch rejection message to person who submitted event

        return $this->Crud->execute();
    }

    public function view($id = null)
    {
        // Redirect to primary event if event is a continuation of a multi-part event
        if ($baseId = $this->Events->get($id)->part_of_id) {
            $this->redirect(['action' => 'view', $baseId]);
        }

        $this->Configurations = TableRegistry::get('Configurations');
        $config = $this->Configurations->find('list')->toArray();
        $this->set('config', $config);

        $this->Crud->on('beforeFind', function (\Cake\Event\Event $event) {
            $event->subject()->query->contain([
                'Categories',
                'Contacts',
                'Files',
                'FulfillsPrerequisites',
                'Registrations',
                'RequiresPrerequisites',
                'Rooms',
                'Tools'
            ]);
        });

        $this->Crud->on('beforeRender', function (\Cake\Event\Event $event) {
            $continuedDates = $this->Events->find('all')
                ->select(['class_number', 'event_start', 'event_end'])
                ->where(['part_of_id' => $event->subject()->entity->id])
                ->order('class_number ASC')
                ->toArray();
            $this->set('continuedDates', $continuedDates);
            $this->set('authUsername', $this->Auth->user('samaccountname'));

            $this->Registrations = TableRegistry::get('Registrations');
            if ($this->Auth->user()) {
                $this->set('hasRegistration', $this->Registrations->exists(['event_id' => $this->passedArgs[0], 'ad_username' => $this->Auth->user('samaccountname')]));
            } else {
                $this->set('hasRegistration', false);
            }

            /*$this->set('attendees', $this->Registrations->find('all')
                ->where(['event_id' => $this->passedArgs[0]])
                ->order(['name' => 'ASC'])
            );*/

            $this->set('hasOpenSpaces', $this->Events->hasOpenSpaces($this->passedArgs[0]));
        });

        $this->Crud->on('beforeRender', [$this, '_applyAddress']);

        return $this->Crud->execute();
    }

    public function add()
    {
        $this->Configurations = TableRegistry::get('Configurations');
        $config = $this->Configurations->find('list')->toArray();
        $this->set('config', $config);
        $this->set('contactError', $this->Auth->user('contact_error'));
        $this->set('blacklisted', $this->Auth->user('blacklisted'));

        $this->Crud->action()->saveOptions([
            'associated' => ['Categories', 'Contacts', 'Contacts.W9s', 'Files', 'Honoraria', 'Tools']
        ]);

        $this->__constructPostForMarshal('add');

        $this->Crud->on('beforeSave', [$this, '_beforeCreate']);
        $this->Crud->on('afterSave', [$this, '_afterCreate']);
        $this->Crud->on('beforeRender', [$this, '_formContent']);
        $this->Crud->on('beforeRender', function (\Cake\Event\Event $event) {
            if (isset($this->request->query['copy'])) {
                if ($this->Events->isOwnedBy($this->request->query['copy'], $this->Auth->user('samaccountname')) || parent::isAuthorized($user)) {
                    if (!$this->request->is(array('post', 'put'))) {
                        $event->subject()->entity = $this->Events->get($this->request->query['copy'], [
                            'contain' => ['Categories', 'Contacts', 'Files', 'FulfillsPrerequisites', 'Honoraria', 'Honoraria.Committees', 'RequiresPrerequisites', 'Tools']
                        ]);
                    } else {
                        $copy = $this->Events->get($this->request->query['copy'], [
                            'contain' => ['Files']
                        ]);
                        $event->subject->entity->files = $copy->files;
                    }

                    $event->subject()->entity->attendee_cancellation = $this->Events->convertToOffset($event->subject()->entity->attendee_cancellation, $event->subject()->entity->event_start, 'attendee_cancellation');
                    $event->subject()->entity->booking_start = $this->Events->convertToOffset($event->subject()->entity->booking_start, $event->subject()->entity->event_start, 'booking_start');
                    $event->subject()->entity->booking_end = $this->Events->convertToOffset($event->subject()->entity->booking_end, $event->subject()->entity->event_end, 'booking_end');

                    $categories = $event->subject()->entity->categories;
                    $event->subject()->entity->categories = [];
                    $event->subject()->entity->optional_categories = [];

                    foreach ($categories as $category) {
                        if ($category->id <= 2) {
                            $event->subject()->entity->categories[] = $category;
                        } else {
                            $event->subject()->entity->optional_categories[] = $category;
                        }
                    }

                    if (!empty($event->subject()->entity->honorarium)) {
                        $event->subject()->entity->request_honorarium = 1;
                    }

                    unset(
                        $event->subject()->entity->eventbrite_link,
                        $event->subject()->entity->event_start,
                        $event->subject()->entity->event_end,
                        $event->subject()->entity->status,
                        $event->subject()->entity->type,
                        $event->subject()->entity->contact->name,
                        $event->subject()->entity->contact->email,
                        $event->subject()->entity->contact->phone
                    );
                }
            }
        });

        return $this->Crud->execute();
    }

    public function edit($id = null)
    {
        // Redirect to primary event if event is a continuation of a multi-part event
        if ($baseId = $this->Events->get($id)->part_of_id) {
            $this->redirect(['action' => 'edit', $baseId]);
        }

        if (!parent::isAuthorized($this->Auth->user())) {
            $this->Events->removeBehavior('FriendlyTime');
            $this->Events->removeBehavior('RelationalTime');
        }

        $this->Configurations = TableRegistry::get('Configurations');
        $config = $this->Configurations->find('list')->toArray();
        $this->set('config', $config);

        $this->Crud->action()->saveOptions([
            'associated' => ['Categories', 'Files', 'Tools']
        ]);

        $this->__constructPostForMarshal('edit');

        $this->Crud->on('beforeFind', function (\Cake\Event\Event $event) {
            $event->subject()->query->contain(['Categories', 'Contacts', 'Files', 'FulfillsPrerequisites', 'Honoraria', 'Honoraria.Committees', 'RequiresPrerequisites', 'Tools']);
        });
        $this->Crud->on('beforeSave', [$this, '_beforeUpdate']);
        $this->Crud->on('afterSave', [$this, '_afterUpdate']);
        $this->Crud->on('beforeRender', [$this, '_formContent']);
        $this->Crud->on('beforeRender', function (\Cake\Event\Event $event) {
            $categories = $event->subject()->entity->categories;

            if (parent::isAuthorized($this->Auth->user())) {
                $event->subject()->entity->attendee_cancellation = $this->Events->convertToOffset($event->subject()->entity->attendee_cancellation, $event->subject()->entity->event_start, 'attendee_cancellation');
                $event->subject()->entity->booking_start = $this->Events->convertToOffset($event->subject()->entity->booking_start, $event->subject()->entity->event_start, 'booking_start');
                $event->subject()->entity->booking_end = $this->Events->convertToOffset($event->subject()->entity->booking_end, $event->subject()->entity->event_end, 'booking_end');

                $event->subject()->entity->event_start = $this->Events->convertToFormat($event->subject()->entity->event_start);
                $event->subject()->entity->event_end = $this->Events->convertToFormat($event->subject()->entity->event_end);
            }

            $event->subject()->entity->categories = [];
            $event->subject()->entity->optional_categories = [];

            foreach ($categories as $category) {
                if ($category->id <= 2) {
                    $event->subject()->entity->categories[] = $category;
                } else {
                    $event->subject()->entity->optional_categories[] = $category;
                }
            }

            $continuedDates = $this->Events->find('all')
                ->select(['class_number', 'event_start', 'event_end'])
                ->where(['part_of_id' => $event->subject()->entity->id])
                ->order('class_number ASC')
                ->toArray();
            $this->set('continuedDates', $continuedDates);

            $nextDate = 2;
            foreach ($continuedDates as $continuedDate) {
                $event->subject()->entity['event_start_' . $nextDate] = $this->Events->convertToFormat($continuedDate['event_start']);
                $event->subject()->entity['event_end_' . $nextDate] = $this->Events->convertToFormat($continuedDate['event_end']);
                $nextDate++;
            }

            $this->set('unlockedEdits', (parent::isAuthorized($this->Auth->user()) ? true : false));
        });

        return $this->Crud->execute();
    }

    public function cancel($id = null)
    {
        $this->Crud->on('beforeSave', function (\Cake\Event\Event $event) {
            $event->subject()->entity->status = 'cancelled';
        });

        // Cancel multi-part dates
        $this->Crud->on('afterSave', function (\Cake\Event\Event $event) {
            $this->Registrations = TableRegistry::get('Registrations');
            $registrations = $this->Registrations->find('all')
                ->where(['event_id' => $this->passedArgs[0], 'status IN' => ['confirmed', 'pending']]);

            Email::configTransport('sparkpost', [
                'className' => 'SparkPost.SparkPost',
                'apiKey' => Configure::read('SparkPost.Api.key')
            ]);

            foreach ($registrations as $registration) {
                try {
                    $message = $registration->name . ",<br/><br/>An event that you RSVP'd for has been cancelled.<br/><br/>" . $event->subject()->entity->name . "<br/><br/>If you paid to register for this event then a refund has been submitted for processing.<br/><br/>Dallas Makerspace";

                    $email = new Email();
                    $email->transport('sparkpost');
                    $email->from(['admin@dallasmakerspace.org' => 'Dallas Makerspace']);
                    $email->to([$registration->email => $registration->name]);
                    $email->subject('Update: ' . $event->subject()->entity->name . ' has been Cancelled');
                    $email->send($message);
                } catch (\Exception $e) {
                    $this->log($e);
                }

                if ($registration->phone && $registration->send_text) {
                    $this->__sendText($registration->phone, 'DMS Event Update: ' . $event->subject()->entity->name . ' has been cancelled.');
                }

                $this->Registrations->refund($registration->id);
                $registration->status = 'cancelled';
                $this->Registrations->save($registration);
            }

            $this->Events->query()->update()
                ->set(['status' => 'cancelled'])
                ->where(['part_of_id' => $event->subject()->entity->id])
                ->execute();
        });

        return $this->Crud->execute();
    }

    public function cron()
    {
        Email::configTransport('sparkpost', [
            'className' => 'SparkPost.SparkPost',
            'apiKey' => Configure::read('SparkPost.Api.key')
        ]);

        $this->Registrations = TableRegistry::get('Registrations');
        $this->Configurations = TableRegistry::get('Configurations');

        // Change event status to completed
        $now = new Time();
        $completedEvents = $this->Events->find('all')
            ->where(['status' => 'approved', 'event_end <' => $now]);

        foreach ($completedEvents as $event) {
            if (!$event->part_of_id) {
                $registrations = $this->Registrations->find('all')
                    ->where(['event_id' => $event->id, 'status' => 'pending']);

                foreach ($registrations as $registration) {
                    try {
                        $message = $registration->name . ",<br/><br/>Your registration for the following event was cancelled automatically by our system.<br/><br/>" . $event->name . "<br/><br/>If you paid to register for this event then a refund has been submitted for processing.<br/><br/>Dallas Makerspace";

                        $email = new Email();
                        $email->transport('sparkpost');
                        $email->from(['admin@dallasmakerspace.org' => 'Dallas Makerspace']);
                        $email->to([$registration->email => $registration->name]);
                        $email->subject($event->name . ' Registration Cancelled');
                        $email->send($message);
                    } catch (\Exception $e) {
                        $this->log($e);
                    }

                    $this->Registrations->refund($registration->id);
                    $registration->status = 'cancelled';
                    $this->Registrations->save($registration);
                }

                $event->status = 'completed';
                $this->Events->save($event);
            }
        }

        // Auto-approve Events
        $approvalTime = $this->Configurations->get(1);
        $approvedTime = new Time();
        $approvedTime->modify('-' . $approvalTime->value . ' days');
        $autoApprovedEvents = $this->Events->find('all')
            ->where([
                'Events.status' => 'pending',
                'Events.created <' => $approvedTime,
                'Honoraria.id IS NULL'
            ])
            ->contain(['Honoraria']);

        foreach ($autoApprovedEvents as $event) {
            $event->status = 'approved';
            $this->Events->save($event);
        }

        // Auto-approve Honoraria
        $honorariaTime = $this->Configurations->get(2);
        $honorariadTime = new Time();
        $honorariadTime->modify('-' . $honorariaTime->value . ' days');
        $autoApprovedHonoraria = $this->Events->find('all')
            ->where([
                'Events.status' => 'pending',
                'Events.created <' => $honorariadTime,
                'Honoraria.id IS NOT NULL'
            ])
            ->contain(['Honoraria']);

        foreach ($autoApprovedHonoraria as $event) {
            $event->status = 'approved';
            $this->Events->save($event);
        }

        // Notify attendees that they can cancel for another 24 hours
        $tomorrow = new Time();
        $tomorrow->modify('+1 days');
        $cancelNotices = $this->Events->find('all')
            ->where([
                'Events.status' => 'approved',
                'Events.attendee_cancellation <' => $tomorrow,
                'Events.part_of_id IS NULL',
                'Events.cancel_notification' => 0
            ]);

        foreach ($cancelNotices as $event) {
            $registrations = $this->Registrations->find('all')
                ->where(['event_id' => $event->id, 'status IN' => ['confirmed', 'pending']]);

            foreach ($registrations as $registration) {
                try {
                    $message = $registration->name . ",<br/><br/>This is a reminder that you don't have much time left to cancel your RSVP for the following event. If you are still planning on attending you can ignore this reminder.<br/><br/>" . $event->name . "<br/><br/>If you need to review or cancel your RSVP you can do so at https://calendar.dallasmakerspace.org/registrations/view/" . $registration->id . "?edit_key=" . $registration->edit_key . "<br/><br/>Dallas Makerspace";

                    $email = new Email();
                    $email->transport('sparkpost');
                    $email->from(['admin@dallasmakerspace.org' => 'Dallas Makerspace']);
                    $email->to([$registration->email => $registration->name]);
                    $email->subject('Reminder: ' . $event->name . ' Cancellation Cutoff is Soon');
                    $email->send($message);
                } catch (\Exception $e) {
                    $this->log($e);
                }

                if ($registration->phone && $registration->send_text) {
                    $this->__sendText($registration->phone, 'DMS Event Reminder: ' . $event->name . ' cancellation deadline is soon.');
                }
            }

            $event->cancel_notification = 1;
            $this->Events->save($event);
        }

        // Notify attendees that they have an event in 24 hours
        $startNotices = $this->Events->find('all')
            ->where([
                'Events.status' => 'approved',
                'Events.event_start <' => $tomorrow,
                'Events.reminder_notification' => 0
            ]);

        foreach ($startNotices as $event) {
            $time = new Time($event->event_start);
            $formattedTime = $time->i18nFormat('EEEE MMMM d, h:mma', 'America/Chicago');

            $registrations = $this->Registrations->find('all')
                ->where(['event_id' => $event->id, 'status IN' => ['confirmed']]);

            foreach ($registrations as $registration) {
                try {
                    $message = $registration->name . ",<br/><br/>This is a reminder that you have an event starting soon at Dallas Makerspace.<br/><br/>" . $event->name . "<br/>" . $formattedTime . "<br/><br/>Full event details are available at https://calendar.dallasmakerspace.org/events/view/" . $event->id . ".<br/><br/>Dallas Makerspace";

                    $email = new Email();
                    $email->transport('sparkpost');
                    $email->from(['admin@dallasmakerspace.org' => 'Dallas Makerspace']);
                    $email->to([$registration->email => $registration->name]);
                    $email->subject('Reminder: ' . $event->name . ' Starts Soon');
                    $email->send($message);
                } catch (\Exception $e) {
                    $this->log($e);
                }

                if ($registration->phone && $registration->send_text) {
                    $this->__sendText($registration->phone, 'DMS Event Reminder: ' . $event->name . ' starts ' . $formattedTime . '.');
                }
            }

            $event->reminder_notification = 1;
            $this->Events->save($event);
        }

        $this->autoRender = false;
    }

    public function _applyAddress(\Cake\Event\Event $event)
    {
        if (isset($event->subject()->entities)) {
            foreach ($event->subject()->entities as $entity) {
                $entity->address = '1825 Monetary Ln #104 Carrollton, TX 75006';

                if ($entity->room && $entity->room->id == 23) {
                    $entity->address = null;
                }
            }
        } else {
            $event->subject()->entity->address = '1825 Monetary Ln #104 Carrollton, TX 75006';

            if ($event->subject()->entity->room && $event->subject()->entity->room->id == 23) {
                $event->subject()->entity->address = null;
            }
        }
    }

    public function _filterContent(\Cake\Event\Event $event)
    {
        $categories = $this->Events->Categories->find('list')->where(['id >' => 2])->order('name ASC')->toArray();
        $tools = $this->Events->Tools->find('list')->order('name ASC')->toArray();
        $this->set(compact('categories', 'tools'));
    }

    public function _formContent(\Cake\Event\Event $event)
    {
        $rooms = $this->Events->Rooms->find('list')->order('name ASC');
        $contacts = $this->Events->Contacts->find('list', [
            'keyField' => 'id',
            'valueField' => 'contact_list_label'
        ])->where([
            'ad_username IS NULL',
            'blacklisted' => false
        ])->order('name ASC');

        $fulfillsPrerequisites = $this->Events->FulfillsPrerequisites->find('list')->order('name ASC');
        $requiresPrerequisites = $this->Events->RequiresPrerequisites->find('list')->order('name ASC');
        $partOfs = $this->Events->PartOfs->find('list')->order('name ASC');
        $copyOfs = $this->Events->CopyOfs->find('list')->order('name ASC');
        $categories = $this->Events->Categories->find('list')->where(['id <=' => 2])->order('name ASC');
        $optionalCategories = $this->Events->Categories->find('list')->where(['id >' => 2])->order('name ASC');
        $tools = $this->Events->Tools->find('list')->order('name ASC');
        $committees = $this->Events->Honoraria->Committees->find('list')->order('name ASC');
        $this->set(compact('event', 'rooms', 'contacts', 'fulfillsPrerequisites', 'requiresPrerequisites', 'partOfs', 'copyOfs', 'categories', 'optionalCategories', 'tools', 'committees'));
    }

    public function _afterCreate(\Cake\Event\Event $event)
    {
        if ($event->subject()->success) {
            $continuedEvents = $this->__constructContinuedEventsForCreate();
            // Use the base event's id to properly link continued event dates and save
            foreach ($continuedEvents as $continuedEvent) {
                $continuedEvent->set('part_of_id', $event->subject()->entity->id);
                $this->Events->save($continuedEvent);
            }

            if (isset($event->subject()->entity->files_to_copy)) {
                foreach ($event->subject()->entity->files_to_copy as $copyFile) {
                    $copying = $this->Events->Files->get($copyFile['id']);

                    $copied = $this->Events->Files->newEntity();
                    $copied->file = $copying->file;
                    $copied->dir = $copying->dir;
                    $copied->type = $copying->type;
                    $copied->event_id = $event->subject()->entity->id;
                    $copied->private = $copying->private;
                    $this->Events->Files->save($copied);
                }
            }

            $this->Flash->success(__('The event has been created. Your event will appear in 48 hours (non honorarium) or 72 hours (honorarium) unless there is an objection.'));
        } else {
            $this->__resetCategorySplits();

            $this->Flash->error(__('The event could not be created. Errors are highlighted in red below. Make any necessary adjustments and try submitting again.'));

            $x = $event->subject()->entity->errors();
            if ($x) {
                debug($event);
                debug($x);
                return false;
            }
        }
    }

    public function _afterUpdate(\Cake\Event\Event $event)
    {
        if ($event->subject()->success) {
            $continuedEvents = $this->__constructContinuedEventsForUpdate($event->subject()->entity->id);
            foreach ($continuedEvents as $continuedEvent) {
                $this->Events->save($continuedEvent);
            }

            $this->Flash->success(__('The event has been updated.'));
        } else {
            $this->__resetCategorySplits();

            $this->Flash->error(__('The event could not be updated. Errors are highlighted in red below. Make any necessary adjustments and try submitting again.'));

            /*$x = $event->subject()->entity->errors();
            if ($x) {
                debug($event);
                debug($x);
                return false;
            }*/
        }
    }

    public function _beforeCreate(\Cake\Event\Event $event)
    {
        if (!empty($event->subject()->entity->contact->w9['file']['name'])) {
            $event->subject()->entity->contact->w9_on_file = true;
        }

        if ($event->subject()->entity->request_honorarium && $event->subject()->entity->honorarium->pay_contact && !$event->subject()->entity->contact->w9_on_file) {
            $event->subject()->entity->honorarium->errors('pay_contact', ['A W-9 is required to be on file for honorarium.']);
            $event->stopPropagation();
        }

        $continuedEvents = $this->__constructContinuedEventsForCreate();
        $completeSave = true;
        foreach ($continuedEvents as $continuedEvent) {
            if ($continuedEvent->errors()) {
                $completeSave = false;
                $event->subject()->entity->errors($continuedEvent->errors());
            }
        }

        if (!$completeSave) {
            $event->stopPropagation();
        }
    }

    public function _beforeUpdate(\Cake\Event\Event $event)
    {
        $continuedEvents = $this->__constructContinuedEventsForUpdate($event->subject()->entity->id);
        $completeSave = true;
        foreach ($continuedEvents as $continuedEvent) {
            if ($continuedEvent->errors()) {
                $completeSave = false;
                $event->subject()->entity->errors($continuedEvent->errors());
            }
        }

        if (!$completeSave) {
            $event->stopPropagation();
        }
    }

    private function __applyQueryFilters(&$event)
    {
        if (!empty($this->request->query['tool'])) {
            $event->subject()->query->matching('Tools', function ($q) {
                return $q->where(['Tools.id' => $this->request->query['tool']]);
            });
        }

        if (!empty($this->request->query['type']) || !empty($this->request->query['category'])) {
            $event->subject()->query->matching('Categories', function ($q) {
                $categories = [];
                $set = 0;

                if (!empty($this->request->query['type'])) {
                    $categories[] = $this->request->query['type'];
                }

                if (!empty($this->request->query['category'])) {
                    $categories[] = $this->request->query['category'];
                }

                return $q->where(['Categories.id IN' => $categories]);
            });
        }

        if (!empty($this->request->query['type']) && !empty($this->request->query['category'])) {
            $event->subject()->query
                ->group('Events.id')
                ->having([
                    $event->subject()->query->newExpr('COUNT(DISTINCT Categories.id) = 2')
                ]);
        }
    }

    private function __constructPostForMarshal($mode = 'add')
    {
        if ($this->request->is(['patch', 'post', 'put'])) {
            if ($mode == 'add') {
                $this->request->data['created_by'] = $this->Auth->user('samaccountname');

                /**
                 * Unset contact information if event is not sponsored and update contact reference
                 * to match the current auth user. If contact information is present and empty the
                 * submission will fail validation.
                 */
                if (isset($this->request->data['sponsored']) && !$this->request->data['sponsored']) {
                    unset($this->request->data['contact']['name']);
                    unset($this->request->data['contact']['email']);
                    unset($this->request->data['contact']['phone']);
                    unset($this->request->data['contact']['w9_on_file']);
                    $this->request->data['contact_id'] = $this->Auth->user('contact_id');
                }

                /**
                 * Unset contact_id if a contact is not selected. If the id field is present and
                 * empty the submission will fail validation.
                 */
                if (!$this->request->data['contact_id']) {
                    unset($this->request->data['contact_id']);
                } else {
                    unset($this->request->data['contact']['name']);
                    unset($this->request->data['contact']['email']);
                    unset($this->request->data['contact']['phone']);
                    unset($this->request->data['contact']['w9_on_file']);
                }

                /**
                 * Set base contact information based on presence of an existing contact in the
                 * submitted data.
                 */
                if (isset($this->request->data['contact_id'])) {
                    $this->Contacts = TableRegistry::get('Contacts');
                    $contact = $this->Contacts->get($this->request->data['contact_id']);
                    $this->request->data['contact']['id'] = $contact->id;
                    $this->request->data['contact']['name'] = $contact->name;
                    $this->request->data['contact']['email'] = $contact->email;
                    $this->request->data['contact']['phone'] = $contact->phone;
                    $this->request->data['contact']['w9_on_file'] = $contact->w9_on_file;
                }

                /**
                 * Unset w9 file field when not used. If file fields are present and empty
                 * the submission will fail validation.
                 */
                if (isset($this->request->data['contact']['w9']['file']['name']) && empty($this->request->data['contact']['w9']['file']['name'])) {
                    unset($this->request->data['contact']['w9']);
                }

                /**
                 * Unset contact data if no relevant data is present in the submission.
                 * If an empty contact is present the submission will fail validation.
                 */
                if (isset($this->request->data['contact']) && empty($this->request->data['contact'])) {
                    unset($this->request->data['contact']);
                }

                /**
                 * Unset honoratium information is honorarium is not requested.
                 */
                if (isset($this->request->data['sponsored']) && !$this->request->data['request_honorarium']) {
                    unset($this->request->data['honorarium']);
                }

                /**
                 * Set event as members only if a prerequisite is required. This dependency is also
                 * handled from the front end via jQuery, but this serves as the hard check.
                 */
                if (isset($this->request->data['requires_prerequisite_id']) && !empty($this->request->data['requires_prerequisite_id'])) {
                    $this->request->data['members_only'] = 1;
                }
            }

            /**
             * Unset file fields which are not used. If file fields are present and empty
             * the submission will fail validation.
             */
            if (isset($this->request->data['files'])) {
                foreach ($this->request->data['files'] as $index => $file) {
                    if (empty($file['file']['name'])) {
                        unset($this->request->data['files'][$index]);
                    }
                }
            }

            /**
             * Combine category data, separated on the front end for ease of use.
             */
            if (isset($this->request->data['optional_categories']['_ids']) && !empty($this->request->data['optional_categories']['_ids'])) {
                $this->request->data['categories']['_ids'] = array_merge($this->request->data['categories']['_ids'], $this->request->data['optional_categories']['_ids']);
            }
        }
    }

    private function __constructContinuedEventsForCreate()
    {
        /**
         * Validate continued dates for multipart events. These are validated first so that
         * errors can be returned before saving the primary event and so the continued events
         * can be properly linked to the first event in the series.
         */
        $continuedEvents = array();
        if (isset($this->request->data['multipart_event']) && $this->request->data['multipart_event']) {
            for ($i = 2; $i < 6; $i++) {
                if (!empty($this->request->data['event_start_' . $i])) {
                    // Copy source data to build a representative entity
                    $copyData = $this->request->data;

                    // Update copy's data
                    $copyData['event_start'] = $this->request->data['event_start_' . $i];
                    $copyData['event_end'] = $this->request->data['event_end_' . $i];
                    $copyData['request_honorarium'] = 0;
                    unset($copyData['honorarium']);
                    $copyData['class_number'] = $i;

                    // Validate copy's data
                    $continuedEvent = $this->Events->newEntity();
                    $continuedEvents[] = $this->Events->patchEntity($continuedEvent, $copyData);
                } else {
                    break;
                }
            }
        }
        return $continuedEvents;
    }

    private function __constructContinuedEventsForUpdate($id = null)
    {
        /**
         * Validate continued dates for multipart events. These are validated first so that
         * errors can be returned before saving the primary event and so the continued events
         * can be properly linked to the first event in the series.
         */
        $continuedEvents = array();

        $events = $this->Events->find('all')
            ->where(['part_of_id' => $id]);

        $copyData = $this->request->data;

        unset(
            $copyData['event_start'],
            $copyData['event_end'],
            $copyData['class_number']
        );

        $i = 2;
        foreach ($events as $event) {
            if (parent::isAuthorized($this->Auth->user())) {
                $copyData['event_start'] = $this->request->data['event_start_' . $i];
                $copyData['event_end'] = $this->request->data['event_end_' . $i];
            } else {
                $copyData['booking_start'] = $event->booking_start->i18nFormat('yyyy-MM-dd HH:mm:ss');
                $copyData['booking_end'] = $event->booking_end->i18nFormat('yyyy-MM-dd HH:mm:ss');
            }
            $continuedEvents[] = $this->Events->patchEntity($event, $copyData);
            $i++;
        }

        return $continuedEvents;
    }

    private function __icsDate($date)
    {
        return $date->format('Ymd\THis\Z');
    }

    private function __icsEscapeString($string)
    {
        return preg_replace('/([\,;])/', '\\\$1', $string);
    }

    private function __resetCategorySplits()
    {
        /**
         * Separate radio category data for redisplay.
         */
        if (!empty($this->request->data['optional_categories']['_ids'])) {
            $this->request->data['categories']['_ids'] = current($this->request->data['categories']['_ids']);
        }
    }

    private function __sendText($to = null, $message = null)
    {
        $textNumber = preg_replace('/\D/', '', $to);
        if (substr($textNumber, 0, 1) != '1') {
            $textNumber = '1' . $textNumber;
        }

        if (strlen($textNumber) == 11) {
            $accountSid = Configure::read('Twilio.accountSid');
            $authToken = Configure::read('Twilio.authToken');
            $fromNumber = Configure::read('Twilio.phone');

            $client = new \Services_Twilio($accountSid, $authToken);

            try {
                $client->account->messages->create([
                    'From' => $fromNumber,
                    'To' => '+' . $textNumber,
                    'Body' => $message,
                ]);
            } catch (\Services_Twilio_RestException $e) {
                $this->log($e);
            }
        }
    }
}
