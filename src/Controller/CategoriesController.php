<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;

/**
 * Categories Controller
 *
 * @property \App\Model\Table\CategoriesTable $Categories
 */
class CategoriesController extends AppController
{
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        $this->Crud->disable(['View']);
    }

    public function isAuthorized($user = null)
    {
        return parent::isAuthorized($user);
    }

    public function index()
    {
        $this->Crud->on('beforePaginate', function (\Cake\Event\Event $event) {
            $event->getSubject()->query
                ->where(['id >' => 2])
                ->order(['name' => 'ASC']);

            $this->paginate['limit'] = 2147483647;
        });

        return $this->Crud->execute();
    }
}
