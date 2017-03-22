<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

/**
 * Contacts Controller
 *
 * @property \App\Model\Table\ContactsTable $Contacts
 */
class ContactsController extends AppController
{
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
    }

    public function isAuthorized($user = null)
    {
        if ($this->request->action == 'view') {
            if (parent::inAdminstrativeGroup($user, 'Honorarium Admins') || parent::inAdminstrativeGroup($user, 'Financial Reporting')) {
                return true;
            }
        }

        return parent::isAuthorized($user);
    }

    public function index()
    {
        $this->Crud->on('beforePaginate', function (\Cake\Event\Event $event) {
            $event->subject()->query->order(['name' => 'ASC']);

            $this->paginate['limit'] = 2147483647;
        });

        return $this->Crud->execute();
    }

    /**
     * View method
     *
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id)
    {
        $this->Crud->on('beforeFind', function (\Cake\Event\Event $event) {
            $event->subject()->query = TableRegistry::get('Contacts')->find()
                ->where(['Contacts.ad_username' => $event->subject()->id]);
        });

        $attended = TableRegistry::get('Registrations')->find()
            ->where(['Registrations.ad_username' => $id])
            ->contain(['Events'])
            ->all();
        $this->set('attended', $attended);

        $hosted = TableRegistry::get('Events')->find()
            ->where(['Events.created_by' => $id])
            ->all();
        $this->set('hosted', $hosted);

        return $this->Crud->execute();
    }
}
