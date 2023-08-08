<?php
namespace App\Controller;


use App\Controller\Component\EmailComponent;
use App\Model\Entity\Contact;
use LdapRecord\Models\ActiveDirectory\User;
use LdapRecord\Models\ActiveDirectory\Group;
use LdapRecord\Container;
use LdapRecord\Connection;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\View\View;
use FeedIo\Factory;
use FeedIo\Feed;


/**
 * Events Controller
 *
 * @property \App\Model\Table\EventsTable $Events
 * @property EmailComponent $Email
 */
class EventsController extends AppController
{
    public $paginate = [
       'sortWhitelist' => ['Events.event_start', 'Events.created'],
       'order' => [
             'Events.event_start' => 'asc'
       ],
       'maxLimit' => PHP_INT_MAX
     ];

    public function initialize(){
        parent::initialize();
        $this->loadComponent('Email');
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        $this->Auth->allow(['calendar', 'cron', 'embed', 'feed', 'index', 'view', 'ics']);
        $this->Security->setConfig('unlockedActions', ['edit']);

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
        $this->Crud->mapAction('upcomingHonoraria', 'Crud.Index');

        $this->Crud->disable(['Delete']);

        $this->Security->setConfig('unlockedActions', ['exportHonoraria']);
    }

    public function isAuthorized($user = null)
    {
        if (in_array($this->request->getParam('action'), ['add', 'attending', 'submitted'])) {
            return !is_null($user);
        }

        if (in_array($this->request->getParam('action'), ['attendance', 'assignments', 'cancel', 'edit'])) {
            $eventId = (int)$this->request->params['pass'][0];

            return ($this->Events->isOwnedBy($eventId, $user['samaccountname']) || parent::isAuthorized($user));
        }

        // Calendar Admins only
        if ($this->request->getParam('action') === 'pending' || $this->request->getParam('action') === 'all') {
            return parent::isAuthorized($user);
        }

        // Honorarium Admins only
        if (in_array($this->request->getParam('action'), ['acceptedHonoraria', 'pendingHonoraria', 'rejectedHonoraria', 'upcomingHonoraria'])) {
            return parent::inAdminstrativeGroup($user, 'Honorarium Admins');
        }

        // Finance Reporting only
        if (in_array($this->request->getParam('action'), ['exportHonoraria', 'exportHonorariaCsv'])) {
            return parent::inAdminstrativeGroup($user, 'Financial Reporting');
        }

        if (in_array($this->request->getParam('action'), ['approve', 'reject', 'processRejection'])) {
            $eventId = (int)$this->request->params['pass'][0];
            if ($this->Events->hasHonorarium($eventId)) {
                return parent::inAdminstrativeGroup($user, 'Honorarium Admins');
            }

            return parent::isAuthorized($user);
        }

        return false;
    }

    public function all()
    {
        $this->Crud->on(
            'beforePaginate',
            function (\Cake\Event\Event $event) {
                if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
                    $start_date = new \DateTime($_GET['start_date'] . ' 00:00:00', new \DateTimeZone('America/Chicago'));
                    $start_date->setTimezone(new \DateTimeZone('UTC'));
                    $end_date = new \DateTime($_GET['end_date'] . ' 23:59:59', new \DateTimeZone('America/Chicago'));
                    $end_date->setTimezone(new \DateTimeZone('UTC'));

                    $event->getSubject()->query
                        ->where(
                            [
                            'Events.part_of_id IS NULL',
                            'Events.event_start >=' => $start_date->format('Y-m-d H:i:s'),
                            'Events.event_start <=' => $end_date->format('Y-m-d H:i:s'),
                            ]
                        )
                        ->order(['Events.created' => 'DESC']);
                } else {
                    $event->getSubject()->query
                        ->where(
                            [
                            'Events.part_of_id IS NULL'
                            ]
                        )
                        ->order(['Events.created' => 'DESC']);
                }
                $this->paginate['limit'] = 50;
            }
        );

        return $this->Crud->execute();
    }

    public function attendance($id = null)
    {
        $this->Crud->on(
            'beforeFind',
            function (\Cake\Event\Event $event) {
                $event->getSubject()->query->contain(['Registrations']);
            }
        );

        $this->Crud->on(
            'beforeRedirect',
            function (\Cake\Event\Event $event) {
                $event->getSubject()->url = $this->referer();
            }
        );

        return $this->Crud->execute();
    }

    public function assignments($id = null)
    {
        $this->Crud->on(
            'beforeFind',
            function (\Cake\Event\Event $event) {
                $event->getSubject()->query->contain(['FulfillsPrerequisites', 'Registrations']);
            }
        );

        $this->Crud->on(
            'beforeRedirect',
            function (\Cake\Event\Event $event) {
                $event->getSubject()->url = $this->referer();
            }
        );

        $this->Crud->on(
            'beforeSave',
            function (\Cake\Event\Event $event) {
                $cfg = Configure::read('ActiveDirectory');

                // Connecting with an an account...
                $connection = new \LdapRecord\Connection([
                    'hosts' => $cfg['domain_controllers'],
                    'base_dn' => $cfg['base_dn'],
                    'username' => $cfg['admin_username'],
                    'password' => $cfg['admin_password'],
                    'version' => 3,
                    'use_tls' => $cfg['use_tls'],
                    'options' => [
                        // See: http://php.net/ldap_set_option
                        LDAP_OPT_X_TLS_REQUIRE_CERT => LDAP_OPT_X_TLS_NEVER
                    ]
                ]);

                $connection->connect();
                Container::addConnection($connection);
                //$connection->auth()->attempt($cfg['admin_user'], $cfg['admin_pw']);

                foreach ($event->getSubject()->entity->registrations as $registration) {
                    if ($registration->ad_assigned && $registration->ad_username) {
                        $groupname = $event->getSubject()->entity->fulfills_prerequisite->ad_group;

                        $grouprec = $connection->query()->where([
                                ['cn', '=', $groupname],
                                ['objectclass', '=', 'group']
                        ])->first();

                        $group = Group::find($grouprec['dn']);
                        $user = User::findByOrFail($cfg['username_field'], $registration->ad_username);

                        $group->members()->attach($user);
                    }
                }
            }
        );

        return $this->Crud->execute();
    }

    public function feed($feedtype = "vcal")
    {
        // TODO: Accept arguments like calendar view and include location
        $this->autoRender = false;

        $today = new Time('America/Chicago');
        $today->startOfDay()->timezone('UTC');
        $now = Time::now();
        $event_query = $this->Events->find('all')
            ->select(
                [
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
                ]
            )
            ->where(
                [
                'Events.event_start >=' => $today,
                'Events.status' => 'approved'
                ]
            )
            ->contain(['Rooms', 'Contacts', 'Categories'])
            ->order(['event_start' => 'ASC']);

        // This allows us to use the applyQueryFilters method for the feed
        // It creates an event object so that applyQueryFilters is happy
        $feed_event_subject = new \stdClass();
        $feed_event_subject->query = $event_query;
        $feed_event = new \Cake\Event\Event("feed_event", $feed_event_subject);

        $this->__applyQueryFilters($feed_event);

        $events = $feed_event->getSubject()->query;

        if ($feedtype === "vcal") {
            $vcalendar = "";

            $vcalendar .= "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//hacksw/handcal//NONSGML v1.0//EN\r\nCALSCALE:GREGORIAN\r\n";

            foreach ($events as $event) {
                $event_url = Router::url(['controller' => 'Events', 'action' => 'view', 'id' => $event->id], true);

                $vcalendar .= "BEGIN:VEVENT\r\n";
                $vcalendar .= 'DTSTART:' . $this->__icsDate($event->event_start) . "\r\n";
                $vcalendar .= 'DTEND:' . $this->__icsDate($event->event_end) . "\r\n";
                $vcalendar .= 'DTSTAMP:' . $this->__icsDate($now) . "\r\n";
                $vcalendar .= 'UID:dmsevtv3' . $event->id . "@calendar.dallasmakerspace.org\r\n";
                $vcalendar .= 'SUMMARY:' . $this->__icsEscapeString($event->name) . "\r\n";
                $vcalendar .= 'DESCRIPTION:' . $this->__icsEscapeString($event->short_description . ' Event details at ' . $event_url) . "\r\n";
                $vcalendar .= 'LOCATION:' . $event->room->name . "\r\n";
                $vcalendar .= 'URL;VALUE=URI:' . $this->__icsEscapeString($event_url) . "\r\n";
                $vcalendar .= "END:VEVENT\r\n";
            }

            $vcalendar .= 'END:VCALENDAR';

            $this->response = $this->response
                ->withStringBody($vcalendar)
                ->withType('text/calendar');
        } else {
            // get id's for categories and tools
            $type = $this->request->getQuery("type");
            $category = $this->request->getQuery("category");
            $tool = $this->request->getQuery("tool");
            $room = $this->request->getQuery("room");

            // get names for categories and tools

            $title_addon = "";
            $description_addon = "";

            $subjects = [];

            if (ctype_digit($type)) {
                $subjects[] = $this->Events->Categories->find('list')->where(['id' => $type])->first();
            }

            if (ctype_digit($category)) {
                $subjects[] = $this->Events->Categories->find('list')->where(['id' => $category])->first();
            }

            if (ctype_digit($tool)) {
                $subjects[] = $this->Events->Tools->find('list')->where(['id' => $tool])->first();
            }

            if (ctype_digit($room)) {
                $subjects[] = $this->Events->Rooms->find('list')->where(['id' => $room])->first();
            }

            if (!empty($subjects)) {
                $title_addon = " - " . implode(", ", $subjects);
                $description_addon = " - Subjects: " . implode(", ", $subjects);
            } else {
                $title_addon = " - All";
                $description_addon = " - Subjects: All Included";
            }

            // create new feed
            $feed = new Feed();

            // Set the feed channel elements

            $feed->setTitle('Dallas Makerspace Calendar' . $title_addon);
            $feed->setLink(Router::url('/', true));
            $feed->setDescription('Events and Classes avaliable at the Dallas Makerspace' . $description_addon);

            // add each event/class in feed
            foreach ($events as $event) {
                $view = new View($this->request);
                $view->set('event', $event);
                $desc_html = $view->render('Events/feed_contents', false);

                $url = Router::url(['controller' => 'Events', 'action' => 'view', 'id' => $event->id], true);

                $feed_event = $feed->newItem();
                $feed_event->setTitle($event->name);
                $feed_event->setLink($url);
                $feed_event->setLastModified(new \DateTime($event->modified));
                $feed_event->setDescription($desc_html);
                $feed_event->setPublicId($url, false);

                $feed_author = $feed_event->newAuthor();
                $feed_author->getName($event->contact->name);
                $feed_author->setUri("");
                $feed_author->setEmail("");
                $feed_event->setAuthor($feed_author);

                foreach ($event->categories as $category) {
                    $feed_category = $feed_event->newCategory();
                    $feed_category->setLabel($category->name);
                    $feed_event->addCategory($feed_category);
                }

                $feed->add($feed_event);
            }

            // output feed in format specified

            $feedIo = Factory::create()->getFeedIo();

            if ($feedtype === "atom") {
                $this->response = $this->response
                    ->withStringBody($feedIo->format($feed, 'atom'))
                    ->withType('application/atom+xml');
            } elseif ($feedtype === "json") {
                $this->response = $this->response
                    ->withStringBody($feedIo->format($feed, 'json'))
                    ->withType('application/json');
            } elseif ($feedtype === "rss") {
                $this->response = $this->response
                    ->withStringBody($feedIo->format($feed, 'rss'))
                    ->withType('application/rss+xml');
            } else { // Default to RSS
                $this->response = $this->response
                    ->withStringBody($feedIo->format($feed, 'rss'))
                    ->withType('application/rss+xml');
            }
        }

        return $this->response;
    }

    public function index()
    {
        $this->Crud->on(
            'beforePaginate',
            function (\Cake\Event\Event $event) {
                $today = new Time('America/Chicago');
                $today->startOfDay()->timezone('UTC');

                $event->getSubject()->query
                    ->select(
                        [
                        'Events.id',
                        'Events.event_start',
                        'Events.event_end',
                        'Events.name',
                        'Events.cost',
                        'Events.short_description',
                        'Events.created',
                        'Events.free_spaces',
                        'Events.paid_spaces',
                        'Rooms.id',
                        'Rooms.name',
                        'Contacts.name',
                        'registration_count' => "count(Registrations.id)"
                        ]
                    )
                    ->leftJoinWith('Registrations', function ($q) {
                        return $q->where(['Registrations.status NOT IN' => ['cancelled','rejected']]);
                    })
                    ->where(
                        [
                        'Events.event_start >=' => $today,
                        'Events.status' => 'approved'
                        ]
                    )
                    ->group('Events.id')
                    ->contain(['Rooms', 'Contacts']);

                $this->__applyQueryFilters($event);

                $this->paginate['limit'] = 2147483647;
                $this->paginate['order'] = array('Events.event_start' => 'ASC');
            }
        );

        $this->Crud->on('afterPaginate', [$this, '_applyAddress']);

        $this->Crud->on('beforeRender', [$this, '_filterContent']);

        return $this->Crud->execute();
    }

    public function embed()
    {
        $this->Crud->on(
            'beforePaginate',
            function (\Cake\Event\Event $event) {
                $today = new Time('America/Chicago');
                $today->startOfDay()->timezone('UTC');

                $event->getSubject()->query
                    ->select(
                        [
                        'Events.id',
                        'Events.event_start',
                        'Events.event_end',
                        'Events.name',
                        'Events.short_description',
                        'Rooms.id',
                        'Rooms.name',
                        'Contacts.name'
                        ]
                    )
                    ->where(
                        [
                        'Events.event_start >=' => $today,
                        'Events.status' => 'approved'
                        ]
                    )
                    ->contain(['Rooms', 'Contacts'])
                    ->order(['event_start' => 'ASC']);

                $this->__applyQueryFilters($event);

                $this->paginate['limit'] = 2147483647;
            }
        );

        $this->Crud->on('afterPaginate', [$this, '_applyAddress']);

        $this->Crud->on('beforeRender', [$this, '_filterContent']);

        return $this->Crud->execute();
    }

    public function calendar($year = null, $month = null, $day = null)
    {
        $this->Crud->on(
            'beforePaginate',
            function (\Cake\Event\Event $event) {
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

                $event->getSubject()->query
                    ->select(
                        [
                        'Events.id',
                        'Events.event_start',
                        'Events.event_end',
                        'Events.name',
                        'Events.short_description',
                        'Rooms.id',
                        'Rooms.name'
                        ]
                    )
                    ->where(
                        [
                        'Events.event_start >=' => $start->timezone('UTC'),
                        'Events.event_start <=' => $end->timezone('UTC'),
                        'Events.status IN' => ['approved', 'completed'],
                        ]
                    )
                    ->contain(['Rooms'])
                    ->order(['event_start' => 'ASC']);

                $this->__applyQueryFilters($event);

                $this->paginate['limit'] = 2147483647;

                $this->set('currentDate', $now);
                $this->set('highlight', $highlight);
            }
        );

        $this->Crud->on('afterPaginate', [$this, '_applyAddress']);

        $this->Crud->on('beforeRender', [$this, '_filterContent']);

        return $this->Crud->execute();
    }

    public function submitted()
    {
        $this->Crud->on(
            'beforePaginate',
            function (\Cake\Event\Event $event) {
                $today = new Time('America/Chicago');
                $today->startOfDay()->timezone('UTC');

                $event->getSubject()->query
                    ->select(
                        [
                        'Events.id',
                        'Events.event_start',
                        'Events.event_end',
                        'Events.name',
                        'Events.short_description',
                        'Events.status',
                        'Rooms.id',
                        'Rooms.name'
                        ]
                    )
                    ->where(
                        [
                        //'Events.event_start >=' => $today,
                        'Events.status IN' => ['approved', 'cancelled', 'completed', 'pending', 'rejected'],
                        'Events.created_by' => $this->Auth->user('samaccountname')
                        ]
                    )
                    ->contain(['Rooms'])
                    ->order(['event_start' => 'DESC']);

                $this->paginate['limit'] = 2147483647;
            }
        );

        $this->Crud->on('afterPaginate', [$this, '_applyAddress']);

        return $this->Crud->execute();
    }

    public function attending()
    {
        $this->Crud->on(
            'beforePaginate',
            function (\Cake\Event\Event $event) {
                $today = new Time('America/Chicago');
                $today->startOfDay()->timezone('UTC');

                $event->getSubject()->query
                    ->select(
                        [
                        'Events.id',
                        'Events.event_start',
                        'Events.event_end',
                        'Events.name',
                        'Events.short_description',
                        'Rooms.id',
                        'Rooms.name'
                        ]
                    )
                    ->where(
                        [
                        //'Events.event_start >=' => $today,
                        'Events.status IN' => ['approved', 'completed', 'pending']
                        ]
                    )
                    ->innerJoinWith(
                        'Registrations',
                        function ($q) {
                            return $q->where(['Registrations.ad_username' => $this->Auth->user('samaccountname')]);
                        }
                    )
                ->contain(['Rooms'])
                ->order(['event_start' => 'DESC']);

                $this->paginate['limit'] = 2147483647;
            }
        );

        $this->Crud->on('afterPaginate', [$this, '_applyAddress']);

        return $this->Crud->execute();
    }

    public function pending()
    {
        $this->Crud->on(
            'beforePaginate',
            function (\Cake\Event\Event $event) {
                $event->getSubject()->query
                    ->where(
                        [
                        'Events.part_of_id IS NULL',
                        'Events.status' => 'pending',
                        'Honoraria.id IS NULL'
                        ]
                    )
                    ->contain(['Honoraria'])
                    ->order(['Events.created' => 'ASC']);

                $this->paginate['limit'] = 50;
            }
        );

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

            $start_date = new \DateTime($_GET['start_date'] . ' 00:00:00', new \DateTimeZone('America/Chicago'));
            $start_date->setTimezone(new \DateTimeZone('UTC'));
            $end_date = new \DateTime($_GET['end_date'] . ' 23:59:59', new \DateTimeZone('America/Chicago'));
            $end_date->setTimezone(new \DateTimeZone('UTC'));

            $data = $this->Events->find('all')
                ->contain(
                    [
                    'Honoraria',
                    'Contacts',
                    'Honoraria.Committees',
                    'Registrations' => function ($q) {
                        return $q->where(['Registrations.attended' => 1]);
                    },
                    'OldRegistrations' => function ($q) {
                        return $q->where(['OldRegistrations.status' => 'confirmed']);
                    }
                    ]
                )
                ->where(
                    [
                    'Events.status' => 'completed',
                    'Events.event_start >=' => $start_date->format('Y-m-d H:i:s'),
                    'Events.event_start <=' => $end_date->format('Y-m-d H:i:s'),
                    'Honoraria.id IS NOT' => null
                    ]
                )
                ->order(
                    [
                    'Events.event_start' => 'ASC'
                    ]
                );

            $this->set('honoraria', $data);
            $this->set('oldCutoff', new Time('2017-01-01 00:00', 'America/Chicago'));
        }
    }

    public function exportHonorariaCsv()
    {
        $export = [];
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
        $start_date = new \DateTime($_GET['start_date'] . ' 00:00:00', new \DateTimeZone('America/Chicago'));
        $start_date->setTimezone(new \DateTimeZone('UTC'));
        $end_date = new \DateTime($_GET['end_date'] . ' 23:59:59', new \DateTimeZone('America/Chicago'));
        $end_date->setTimezone(new \DateTimeZone('UTC'));

        $data = $this->Events->find('all')
            ->contain(
                [
                'Honoraria',
                'Contacts',
                'Honoraria.Committees',
                'Registrations' => function ($q) {
                    return $q->where(['Registrations.attended' => 1]);
                },
                'OldRegistrations' => function ($q) {
                    return $q->where(['OldRegistrations.status' => 'confirmed']);
                }
                ]
            )
            ->where(
                [
                'Events.status' => 'completed',
                'Events.event_start >=' => $start_date->format('Y-m-d H:i:s'),
                'Events.event_start <=' => $end_date->format('Y-m-d H:i:s'),
                'Honoraria.id IS NOT' => null
                ]
            )
            ->order(
                [
                'Events.event_start' => 'ASC'
                ]
            );

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

        $this->response = $this->response->withDownload('honoria_export.csv');
	    $this->viewBuilder()->className('CsvView.Csv');
        $this->set(compact('export', '_serialize'));
    }

    public function pendingHonoraria()
    {
        $this->Crud->on(
            'beforePaginate',
            function (\Cake\Event\Event $event) {
                $event->getSubject()->query->where(
                    [
                    'Events.part_of_id IS NULL',
                    'Events.status' => 'pending',
                    'Honoraria.id IS NOT NULL'
                    ]
                )
                    ->contain(['Honoraria' => ['Committees'],'Contacts']);

                if (!isset($_GET['sort'])) {
                    $event->getSubject()->query->order(['event_start' => 'DESC']);
                }

                $this->paginate['limit'] = 50;
            }
        );

        return $this->Crud->execute();
    }

    public function upcomingHonoraria()
    {
        $months = [];
        for ($i = 0; $i < 4; $i++) {
            $nextMonth = date('M', strtotime('+' . $i . ' month'));
            $nextMonthStart = date('Y-m-01 00:00:00', strtotime('+' . $i . ' month'));
            $nextMonthEnd = date('Y-m-31 23:59:59', strtotime('+' . $i . ' month'));
            $events = TableRegistry::get('Events');
            $results = $events->find('all')
                ->where(
                    [
                        'Events.part_of_id IS NULL',
                        'Events.status IN' => ['approved', 'completed'],
                        'Events.event_start >=' => $nextMonthStart,
                        'Events.event_end <=' => $nextMonthEnd,
                        'Honoraria.id IS NOT NULL'
                    ]
                )->contain(['Honoraria'])->toList();
            $months[$nextMonth] = count($results);
        }

        $this->set(compact('months'));
    }

    public function acceptedHonoraria()
    {
        $this->Crud->on(
            'beforePaginate',
            function (\Cake\Event\Event $event) {
                $event->getSubject()->query->where(
                    [
                        'Events.part_of_id IS NULL',
                        'Events.status IN' => ['approved', 'completed'],
                        'Honoraria.id IS NOT NULL'
                    ]
                )
                    ->contain(['Honoraria']);

                if (!isset($_GET['sort']) || !$_GET['sort']) {
                    $event->getSubject()->query->order(['event_start' => 'DESC']);
                }

                $this->paginate['limit'] = 50;
            }
        );

        return $this->Crud->execute();
    }

    public function rejectedHonoraria()
    {
        $this->Crud->on(
            'beforePaginate',
            function (\Cake\Event\Event $event) {
                $event->getSubject()->query->where(
                    [
                        'Events.part_of_id IS NULL',
                        'Events.status' => 'rejected',
                        'Honoraria.id IS NOT NULL'
                    ]
                )
                    ->contain(['Honoraria']);

                if (!isset($_GET['sort']) || !$_GET['sort']) {
                    $event->getSubject()->query->order(['event_start' => 'DESC']);
                }

                $this->paginate['limit'] = 50;
            }
        );

        return $this->Crud->execute();
    }

    public function approve($id = null)
    {
        $this->Crud->on(
            'beforeSave',
            function (\Cake\Event\Event $event) {
                $event->getSubject()->entity->status = 'approved';
            }
        );

        // Approve multi-part dates
        $this->Crud->on(
            'afterSave',
            function (\Cake\Event\Event $event) {
                $this->Events->query()->update()
                    ->set(['status' => 'approved'])
                    ->where(['part_of_id' => $event->getSubject()->entity->id])
                    ->execute();

                /** @var Contact $contact */
                $contact = $this->Events->Contacts
                    ->find()
                    ->where(['ad_username' => $event->getSubject()->entity->created_by])
                    ->first();

                // Send an email to the submitter with some information
                $this->Email->sendEventApproved(
                    $contact,
                    $event->getSubject()->entity, //Event
                );

            }
        );

        return $this->Crud->execute();
    }

    public function processRejection($id = null)
    {
        return $this->Crud->execute();
    }

    public function reject($id = null)
    {
        $this->Crud->on(
            'beforeSave',
            function (\Cake\Event\Event $event) {
                $event->getSubject()->entity->status = 'rejected';
                $event->getSubject()->entity->rejection_reason = $this->request->data('event.rejection_reason');
                $event->getSubject()->entity->rejected_by = $this->Auth->user('samaccountname');
            }
        );

        // Reject multi-part dates
        $this->Crud->on(
            'afterSave',
            function (\Cake\Event\Event $event) {
                $this->Events->query()->update()
                    ->set(['status' => 'rejected'])
                    ->where(['part_of_id' => $event->getSubject()->entity->id])
                    ->execute();

                // Get contact info by created_by id
                $contact = $this->Events->Contacts
                    ->find()
                    ->where(['ad_username' => $event->getSubject()->entity->created_by])
                    ->first();


                $this->Email->sendEventRejected(
                    $contact,
                    $event->getSubject()->entity, //Event
                );

            }
        );

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

        $this->Crud->on(
            'beforeFind',
            function (\Cake\Event\Event $event) {
                $event->getSubject()->query->contain(
                    [
                        'Categories',
                        'Contacts',
                        'Files',
                        'FulfillsPrerequisites',
                        'Registrations',
                        'RequiresPrerequisites',
                        'Rooms',
                        'Tools'
                    ]
                );
            }
        );

        $this->Crud->on(
            'beforeRender',
            function (\Cake\Event\Event $event) {
                $continuedDates = $this->Events->find('all')
                    ->select(['class_number', 'event_start', 'event_end'])
                    ->where(['part_of_id' => $event->getSubject()->entity->id])
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

                $totalSpaces = $this->Events->getTotalSpaces($this->passedArgs[0]);
                $filledSpaces = $this->Events->getFilledSpaces($this->passedArgs[0]);
                if (is_int($totalSpaces) && is_int($filledSpaces)) {
                    $openSpaces = $totalSpaces - $filledSpaces;
                } else {
                    $openSpaces = true;
                }

                $this->set('hasOpenSpaces', $this->Events->hasOpenSpaces($this->passedArgs[0]));
                $this->set('openSpaces', $openSpaces);
                $this->set('totalSpaces', $totalSpaces);

                // Add to calendar links
                $this->set('addToCalLinks', $this->getAddToCalLinks($event->getSubject()->entity));
            }
        );

        $this->Crud->on('beforeRender', [$this, '_applyAddress']);

        return $this->Crud->execute();
    }

    public function getAddToCalLinks(object $event) {
        $start_date_iso8601 = $this->getISO8601Date($event->event_start);
        $end_date_iso8601 = $this->getISO8601Date($event->event_end);
        $start_date_c = urlencode($event->event_start->setTimezone('America/Chicago')->i18nFormat("yyyy-MM-dd'T'HH:mm:ss")); // 2020-04-19T13:30:00Z
        $end_date_c = urlencode($event->event_end->setTimezone('America/Chicago')->i18nFormat("yyyy-MM-dd'T'HH:mm:ss"));
        $title = urlencode($event->name);
        $description = urlencode($event->short_description);
        $address = '1825%20Monetary%20Ln%20%23104%20Carrollton%2C%20TX%2075006';
        $gcal = [
            "icon" => "gcal.svg",
            "url" => "https://calendar.google.com/calendar/render?action=TEMPLATE&dates=$start_date_iso8601%2F$end_date_iso8601&details=$description&location=$address&text=$title",
            "hint" => "Add to Google Calendar"
        ];
        $outlook = [
            "icon" => "outlook.svg",
            "url" => "https://outlook.live.com/calendar/0/deeplink/compose?allday=false&body=$description&enddt=$end_date_c&location=$address&path=%2Fcalendar%2Faction%2Fcompose&rru=addevent&startdt=$start_date_c&subject=$title",
            "hint" => "Add to Outlook"
        ];
        $apple = [
            "icon" => "apple.svg",
            "url" => Router::url(['controller' => 'Events', 'action' => 'ics', $event->id], true),
            "hint" => "Add to iPhone / mac"
        ];
        return [$gcal, $outlook, $apple];
    }

    public function getISO8601Date(\Cake\I18n\FrozenTime $start_date) {
        return $start_date->i18nFormat("yyyyMMdd'T'HHmmss'Z'");
    }

    public function ics($id = null)
    {
        $this->autoRender = false;
        $now = Time::now();
        $event = $this->Events->find()
            ->where(['id' => $id])->first();
        $vcalendar = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//hacksw/handcal//NONSGML v1.0//EN\r\nCALSCALE:GREGORIAN\r\n";

        $event_url = Router::url(['controller' => 'Events', 'action' => 'view', $event->id], true);

        $vcalendar .= "BEGIN:VEVENT\r\n";
        $vcalendar .= 'DTSTART:' . $this->__icsDate($event->event_start) . "\r\n";
        $vcalendar .= 'DTEND:' . $this->__icsDate($event->event_end) . "\r\n";
        $vcalendar .= 'DTSTAMP:' . $this->__icsDate($now) . "\r\n";
        $vcalendar .= 'UID:dmsevtv3' . $event->id . "@calendar.dallasmakerspace.org\r\n";
        $vcalendar .= 'SUMMARY:' . $this->__icsEscapeString($event->name) . "\r\n";
        $vcalendar .= 'DESCRIPTION:' . $this->__icsEscapeString($event->short_description . ' Event details at ' . $event_url) . "\r\n";
        $vcalendar .= 'LOCATION:' . $this->__icsEscapeString("1825 Monetary Ln #104 Carrollton, TX 75006") . "\r\n";
        $vcalendar .= 'URL;VALUE=URI:' . $this->__icsEscapeString($event_url) . "\r\n";
        $vcalendar .= "END:VEVENT\r\n";

        $vcalendar .= 'END:VCALENDAR';

        $filename = 'dmsevtv3_' . $event->id . '.ics';

        $this->response = $this->response
                ->withType('ics')
                ->withDownload($filename)
                ->withStringBody("$vcalendar");

    }

    public function add()
    {
        $this->calendarConfigurations = TableRegistry::get('CalendarSuperConfigurations');
	    $this->set('honorariaMessage', $this->calendarConfigurations->get(1)->value);

        $this->Configurations = TableRegistry::get('Configurations');
        $config = $this->Configurations->find('list')->toArray();
        $this->set('config', $config);
        $this->set('contactError', $this->Auth->user('contact_error'));
        $this->set('blacklisted', $this->Auth->user('blacklisted'));

        $this->Crud->action()
            ->saveOptions(
                [
                'associated' => ['Categories', 'Contacts', 'Contacts.W9s', 'Files', 'Honoraria', 'Tools']
                ]
            );

        $this->__constructPostForMarshal('add');

        $this->Crud->on('beforeSave', [$this, '_beforeCreate']);
        $this->Crud->on('afterSave', [$this, '_afterCreate']);
        $this->Crud->on('beforeRender', [$this, '_formContent']);
        $this->Crud->on(
            'beforeRender',
            function (\Cake\Event\Event $event) {
                if ($this->request->getQuery('copy') !== null) {
                    if ($this->Events->isOwnedBy($this->request->getQuery('copy'), $this->Auth->user('samaccountname')) || parent::isAuthorized($this->Auth->user())) {
                        if (!$this->request->is(['post', 'put'])) {
                            $event->getSubject()->entity = $this->Events->get(
                                $this->request->getQuery('copy'),
                                [
                                    'contain' => ['Categories', 'Contacts', 'Files', 'FulfillsPrerequisites', 'Honoraria', 'Honoraria.Committees', 'RequiresPrerequisites', 'Tools']
                                ]
                            );
                        } else {
                            $copy = $this->Events->get(
                                $this->request->getQuery('copy'),
                                [
                                    'contain' => ['Files']
                                ]
                            );
                            $event->subject->entity->files = $copy->files;
                        }

                        $event->getSubject()->entity->attendee_cancellation = $this->Events->convertToOffset($event->getSubject()->entity->attendee_cancellation, $event->getSubject()->entity->event_start, 'attendee_cancellation');
                        $event->getSubject()->entity->booking_start = $this->Events->convertToOffset($event->getSubject()->entity->booking_start, $event->getSubject()->entity->event_start, 'booking_start');
                        $event->getSubject()->entity->booking_end = $this->Events->convertToOffset($event->getSubject()->entity->booking_end, $event->getSubject()->entity->event_end, 'booking_end');

                        $categories = $event->getSubject()->entity->categories;
                        $event->getSubject()->entity->categories = [];
                        $event->getSubject()->entity->optional_categories = [];

                        foreach ($categories as $category) {
                            if ($category->id <= 2) {
                                $event->getSubject()->entity->categories[] = $category;
                            } else {
                                $event->getSubject()->entity->optional_categories[] = $category;
                            }
                        }

                        //Log::write("warning", var_export($event->getSubject()->entity, true));

                        if (!empty($event->getSubject()->entity->honorarium)) {
                            $event->getSubject()->entity->request_honorarium = 1;
                        }

                        unset(
                            $event->getSubject()->entity->eventbrite_link,
                            $event->getSubject()->entity->event_start,
                            $event->getSubject()->entity->event_end,
                            $event->getSubject()->entity->status,
                            $event->getSubject()->entity->type,
                            $event->getSubject()->entity->contact->name,
                            $event->getSubject()->entity->contact->email,
                            $event->getSubject()->entity->contact->phone
                        );
                    }
                }
            }
        );

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

        $this->Crud->action()
            ->saveOptions(
                [
                'associated' => ['Categories', 'Files', 'Tools']
                ]
            );

        $this->__constructPostForMarshal('edit');

        $this->Crud->on(
            'beforeFind',
            function (\Cake\Event\Event $event) {
                $event->getSubject()->query->contain(['Categories', 'Contacts', 'Files', 'FulfillsPrerequisites', 'Honoraria', 'Honoraria.Committees', 'RequiresPrerequisites', 'Tools']);
            }
        );
        $this->Crud->on('beforeSave', [$this, '_beforeUpdate']);
        $this->Crud->on('afterSave', [$this, '_afterUpdate']);
        $this->Crud->on('beforeRender', [$this, '_formContent']);
        $this->Crud->on(
            'beforeRender',
            function (\Cake\Event\Event $event) {
                $categories = $event->getSubject()->entity->categories;

                if (parent::isAuthorized($this->Auth->user())) {
                    $event->getSubject()->entity->attendee_cancellation = $this->Events->convertToOffset($event->getSubject()->entity->attendee_cancellation, $event->getSubject()->entity->event_start, 'attendee_cancellation');
                    $event->getSubject()->entity->booking_start = $this->Events->convertToOffset($event->getSubject()->entity->booking_start, $event->getSubject()->entity->event_start, 'booking_start');
                    $event->getSubject()->entity->booking_end = $this->Events->convertToOffset($event->getSubject()->entity->booking_end, $event->getSubject()->entity->event_end, 'booking_end');

                    $event->getSubject()->entity->event_start = $this->Events->convertToFormat($event->getSubject()->entity->event_start);
                    $event->getSubject()->entity->event_end = $this->Events->convertToFormat($event->getSubject()->entity->event_end);
                }

                $event->getSubject()->entity->categories = [];
                $event->getSubject()->entity->optional_categories = [];

                foreach ($categories as $category) {
                    if ($category->id <= 2) {
                        $event->getSubject()->entity->categories[] = $category;
                    } else {
                        $event->getSubject()->entity->optional_categories[] = $category;
                    }
                }

                $continuedDates = $this->Events->find('all')
                    ->select(['class_number', 'event_start', 'event_end'])
                    ->where(['part_of_id' => $event->getSubject()->entity->id])
                    ->order('class_number ASC')
                    ->toArray();
                $this->set('continuedDates', $continuedDates);

                $nextDate = 2;
                foreach ($continuedDates as $continuedDate) {
                    $event->getSubject()->entity['event_start_' . $nextDate] = $this->Events->convertToFormat($continuedDate['event_start']);
                    $event->getSubject()->entity['event_end_' . $nextDate] = $this->Events->convertToFormat($continuedDate['event_end']);
                    $nextDate++;
                }

                $this->set('unlockedEdits', (parent::isAuthorized($this->Auth->user()) ? true : false));
            }
        );

        return $this->Crud->execute();
    }

    public function cancel($id = null)
    {
        $this->Crud->on(
            'beforeSave',
            function (\Cake\Event\Event $event) {
                $event->getSubject()->entity->status = 'cancelled';
            }
        );

        // Cancel multi-part dates
        $this->Crud->on(
            'afterSave',
            function (\Cake\Event\Event $event) {
                $this->Registrations = TableRegistry::get('Registrations');
                $registrations = $this->Registrations->find('all')
                    ->where(['event_id' => $this->passedArgs[0], 'status IN' => ['confirmed', 'pending']]);

                $eventRef = $event->getSubject()->entity;

                foreach ($registrations as $registration) {
                    $this->Email->sendEventCancelled($eventRef, $registration);

                    if ($registration->phone && $registration->send_text) {
                        $this->__sendText($registration->phone, 'DMS Event Update: ' . $event->getSubject()->entity->name . ' has been cancelled.');
                    }

                    $this->Registrations->refund($registration->id);
                    $registration->status = 'cancelled';
                    $this->Registrations->save($registration);
                }

                $this->Events->query()->update()
                    ->set(['status' => 'cancelled'])
                    ->where(['part_of_id' => $event->getSubject()->entity->id])
                    ->execute();
            }
        );

        return $this->Crud->execute();
    }

    public function cron()
    {
        $this->Registrations = TableRegistry::get('Registrations');
        $this->Configurations = TableRegistry::get('Configurations');

        // Change event status to completed
        $now = new Time();
        $completedEvents = $this->Events->find('all')
            ->where(['status' => 'approved', 'event_end <' => $now]);

        foreach ($completedEvents as $event) {
            if (!$event->part_of_id) {
                $registrations = $this->Registrations
                    ->find('all')
                    ->where([
                        'event_id' => $event->id,
                        'status' => 'pending'
                    ]);

                foreach ($registrations as $registration) {
                    $this->Email->sendUnapprovedRegistrationCancelled($registration, $event);

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
            ->where(
                [
                'Events.status' => 'pending',
                'Events.created <' => $approvedTime,
                'Honoraria.id IS NULL'
                ]
            )
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
            ->where(
                [
                'Events.status' => 'pending',
                'Events.created <' => $honorariadTime,
                'Honoraria.id IS NOT NULL'
                ]
            )
            ->contain(['Honoraria']);

        foreach ($autoApprovedHonoraria as $event) {
            $event->status = 'approved';
            $this->Events->save($event);
        }

        // Notify attendees that they can cancel for another 24 hours
        $tomorrow = new Time();
        $tomorrow->modify('+1 days');
        $cancelNotices = $this->Events->find('all')
            ->where(
                [
                'Events.status' => 'approved',
                'Events.attendee_cancellation <' => $tomorrow,
                'Events.part_of_id IS NULL',
                'Events.cancel_notification' => 0
                ]
            );

        foreach ($cancelNotices as $event) {
            $registrations = $this->Registrations
                ->find('all')
                ->where([
                        'event_id' => $event->id,
                        'status IN' => ['confirmed', 'pending']
                ]);

            foreach ($registrations as $registration) {
                $this->Email->sendCancellationReminder($registration, $event);

                if ($registration->phone && $registration->send_text) {
                    // $this->__sendText($registration->phone, 'DMS Event Reminder: ' . $event->name . ' cancellation deadline is soon.');
                }
            }

            $event->cancel_notification = 1;
            $this->Events->save($event);
        }

        // Notify attendees that they have an event in 24 hours
        $startNotices = $this->Events->find('all')
            ->where(
                [
                'Events.status' => 'approved',
                'Events.event_start <' => $tomorrow,
                'Events.reminder_notification' => 0
                ]
            );

        foreach ($startNotices as $event) {
            $time = new Time($event->event_start);
            $formattedTime = $time->i18nFormat('EEEE MMMM d, h:mma', 'America/Chicago');

            $registrations = $this->Registrations->find('all')
                ->where(['event_id' => $event->id, 'status IN' => ['confirmed']]);

            foreach ($registrations as $registration) {
                $this->Email->sendEventStarting($registration, $event);

                if ($registration->phone && $registration->send_text) {
                    //$this->__sendText($registration->phone, 'DMS Event Reminder: ' . $event->name . ' starts ' . $formattedTime . '.');
                }
            }

            $event->reminder_notification = 1;
            $this->Events->save($event);
        }

        $this->autoRender = false;
    }

    public function _applyAddress(\Cake\Event\Event $event)
    {
        if (isset($event->getSubject()->entities)) {
            foreach ($event->getSubject()->entities as $entity) {
                $entity->address = '1825 Monetary Ln #104 Carrollton, TX 75006';

                # WARNING: Ugly hack, relies on room_ids which could change
                # Don't put our address for off-site and online classes
                if ($entity->room && in_array($entity->room->id, [23, 58])) {
                    $entity->address = null;
                }
            }
        } else {
            $event->getSubject()->entity->address = '1825 Monetary Ln #104 Carrollton, TX 75006';

            # WARNING: Ugly hack, relies on room_ids which could change
            # Don't put our address for off-site and online classes
            if ($event->getSubject()->entity->room && in_array($event->getSubject()->entity->room->id, [23, 58])) {
                $event->getSubject()->entity->address = null;
            }
        }
    }

    public function _filterContent(\Cake\Event\Event $event)
    {
        $categories = $this->Events->Categories->find('list')->where(['id >' => 2])->order('name ASC')->toArray();
        $tools = $this->Events->Tools->find('list')->order('name ASC')->toArray();
        $rooms = $this->Events->Rooms->find('list')->order('name ASC')->toArray();
        $this->set(compact('categories', 'tools', 'rooms'));
    }

    public function _formContent(\Cake\Event\Event $event)
    {
        $rooms = $this->Events->Rooms->find('list')->order('name ASC');
        $contacts = $this->Events->Contacts->find(
            'list',
            [
            'keyField' => 'id',
            'valueField' => 'contact_list_label'
            ]
        )->where(
            [
                'ad_username IS NULL',
                'blacklisted' => false
                ]
        )->order('name ASC');

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
        //Log::debug("_afterCreate");

        if ($event->getSubject()->success) {
            $continuedEvents = $this->__constructContinuedEventsForCreate();
            // Use the base event's id to properly link continued event dates and save
            foreach ($continuedEvents as $continuedEvent) {
                $continuedEvent->set('part_of_id', $event->getSubject()->entity->id);
                $this->Events->save($continuedEvent);
            }

            if (isset($event->getSubject()->entity->files_to_copy)) {
                foreach ($event->getSubject()->entity->files_to_copy as $copyFile) {
                    $copying = $this->Events->Files->get($copyFile['id']);

                    $copied = $this->Events->Files->newEntity();
                    $copied->file = $copying->file;
                    $copied->dir = $copying->dir;
                    $copied->type = $copying->type;
                    $copied->event_id = $event->getSubject()->entity->id;
                    $copied->private = $copying->private;
                    $this->Events->Files->save($copied);
                }
            }

            /** @var Contact $contact */
            $contact = $this->Events->Contacts
                ->find()
                ->where(['ad_username' => $event->getSubject()->entity->created_by])
                ->first();

            // Send an email to the submitter with some information
            $this->Email->sendEventSubmitted(
                $contact,
                $event->getSubject()->entity, //Event
            );

            $this->Flash->success(__('The event has been created. Your event will appear in 48 hours (non honorarium) or 72 hours (honorarium) unless there is an objection.'));
        } else {
            $this->__resetCategorySplits();

            $this->Flash->error(__('The event could not be created. Errors are highlighted in red below. Make any necessary adjustments and try submitting again.'));

            $x = $event->getSubject()->entity->errors();
            if ($x) {
                //debug($event);
                //debug($x);
                return false;
            }
        }
    }

    public function _afterUpdate(\Cake\Event\Event $event)
    {
        if ($event->getSubject()->success) {
            $continuedEvents = $this->__constructContinuedEventsForUpdate($event->getSubject()->entity->id);
            foreach ($continuedEvents as $continuedEvent) {
                $this->Events->save($continuedEvent);
            }

            $this->customLog("User changed :" . serialize($event->getSubject()->entity->extractOriginalChanged($event->getSubject()->entity->visibleProperties())));
            $this->Flash->success(__('The event has been updated.'));
        } else {
            $this->customLog("User failed to change :" . serialize($event->getSubject()->entity->extractOriginalChanged(['name', 'short_description'])));
            $this->__resetCategorySplits();

            $this->Flash->error(__('The event could not be updated. Errors are highlighted in red below. Make any necessary adjustments and try submitting again.'));

            /*$x = $event->getSubject()->entity->errors();
            if ($x) {
                debug($event);
                debug($x);
                return false;
            }*/
        }
    }

    public function _beforeCreate(\Cake\Event\Event $event)
    {
        // We are now assuming they have files a W9 with legal, not here - so this bypasses
        $event->getSubject()->entity->contact->w9_on_file = true;

        $continuedEvents = $this->__constructContinuedEventsForCreate();
        $completeSave = true;
        foreach ($continuedEvents as $continuedEvent) {
            if ($continuedEvent->errors()) {
                $completeSave = false;
                $event->getSubject()->entity->errors($continuedEvent->errors());
            }
        }

        if (!$completeSave) {
            $event->stopPropagation();
        }
    }

    public function _beforeUpdate(\Cake\Event\Event $event)
    {
        $continuedEvents = $this->__constructContinuedEventsForUpdate($event->getSubject()->entity->id);
        $completeSave = true;
        foreach ($continuedEvents as $continuedEvent) {
            if ($continuedEvent->errors()) {
                $completeSave = false;
                $event->getSubject()->entity->errors($continuedEvent->errors());
            }
        }

        if (!$completeSave) {
            $event->stopPropagation();
        }
    }

    private function __applyQueryFilters(&$event)
    {
        if (!empty($this->request->getQuery('tool'))) {
            $event->getSubject()->query->matching(
                'Tools',
                function ($q) {
                    return $q->where(['Tools.id' => $this->request->getQuery('tool')]);
                }
            );
        }

        if (!empty($this->request->getQuery('room'))) {
            $event->getSubject()->query->matching(
                'Rooms',
                function ($q) {
                    return $q->where(['Rooms.id' => $this->request->getQuery('room')]);
                }
            );
        }

        if (!empty($this->request->getQuery('type')) || !empty($this->request->getQuery('category'))) {
            $event->getSubject()->query->matching(
                'Categories',
                function ($q) {
                    $categories = [];
                    $set = 0;

                    if (!empty($this->request->getQuery('type'))) {
                        $categories[] = $this->request->getQuery('type');
                    }

                    if (!empty($this->request->getQuery('category'))) {
                        $categories[] = $this->request->getQuery('category');
                    }

                    return $q->where(['Categories.id IN' => $categories]);
                }
            );
        }

        if (!empty($this->request->getQuery('type')) && !empty($this->request->getQuery('category'))) {
            $event->getSubject()->query
                ->group('Events.id')
                ->having(
                    [
                    $event->getSubject()->query->newExpr('COUNT(DISTINCT Categories.id) = 2')
                    ]
                );
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
        $continuedEvents = [];
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
        $continuedEvents = [];

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
                $client->account->messages->create(
                    [
                    'From' => $fromNumber,
                    'To' => '+' . $textNumber,
                    'Body' => $message,
                    ]
                );
            } catch (\Services_Twilio_RestException $e) {
                $this->log($e);
            }
        }
    }
}
