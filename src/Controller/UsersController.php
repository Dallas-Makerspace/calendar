<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

/**
 * Users Controller
 *
 * @property \App\Model\Table\ToolsTable $Tools
 */
class UsersController extends AppController
{
    // Users are sourced from Active Directory. No internal model or database association.
    public $uses = [];

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        // Allow users to logout
        $this->Auth->allow(['logout']);
    }

    /**
     * implementedEvents
     *
     * @return array of implemented event listeners for this controller
     */
    public function implementedEvents()
    {
        $events = parent::implementedEvents();
        $events['Auth.afterIdentify'] = 'afterIdentify';

        return $events;
    }

    /**
     * afterIdentify check if user exists in application contacts and adds them if missing.
     *
     * @param Event $event Event.
     * @param array $user User.
     * @return array Updated user data.
     */
    public function afterIdentify(Event $event, $user)
    {
        $contactsTable = TableRegistry::get('Contacts');
        $contact = $contactsTable->find('all')
            ->where(['ad_username' => $user['samaccountname']])
            ->first();

        $user['contact_error'] = true;

        if (!$contact) {
            $fields = ['displayname', 'samaccountname', 'mail', 'telephonenumber'];

            foreach ($fields as $field) {
                if (!isset($user[$field]) || empty($user[$field])) {
                    return $user;
                }
            }

            $data = [
                'name' => $user['displayname'],
                'ad_username' => $user['samaccountname'],
                'email' => $user['mail'],
                'phone' => $user['telephonenumber'],
                'w9_on_file' => false
            ];

            $contact = $contactsTable->newEntity($data);
            $contactsTable->save($contact);
        }

        $user['contact_id'] = $contact->id;

        if (!empty($user['contact_id'])) {
            $user['contact_error'] = false;
        }
        $user['blacklisted'] = $contact->blacklisted;

        return $user;
    }

    /**
     * Login method
     *
     * @return \Cake\Network\Response|void Redirects on successful login, renders view otherwise.
     */
    public function login()
    {
        if ($this->request->is('post')) {
            $user = $this->Auth->identify();

            if ($user) {
                $this->Auth->setUser($user);

                if (isset($this->request->query['redirect'])) {
                    return $this->redirect($this->request->query['redirect']);
                }

                return $this->redirect($this->Auth->redirectUrl());
            }

            $this->Flash->error('Invalid username or password, try again.');
        }
    }

    /**
     * Logout method
     *
     * @return \Cake\Network\Response|void Redirects after logout.
     */
    public function logout()
    {
        return $this->redirect($this->Auth->logout());
    }
}
