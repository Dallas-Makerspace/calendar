<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

/**
 * Calendar Admin Controller
 *
 * @property \App\Model\Table\ConfigurationsTable $Configurations
 */
class CalendarAdminController extends AppController
{
    public function isAuthorized($user = null)
    {
        return $this->inAdminstrativeGroup($user, 'Calendar Super Admins');
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        $this->Security->setConfig('unlockedActions', ['edit']);
    }

    public function edit() {
        $configurations = TableRegistry::getTableLocator()->get('Configurations');
        $calendarConfigurations = TableRegistry::getTableLocator()->get('CalendarSuperConfigurations');

        if ($this->request->getData('id')) {
	        $configuration = $configurations->get(7); // Return article with id 12

	    $configuration->value = $this->request->getData('value');
	    $configurations->save($configuration);

	    $message = $calendarConfigurations->get(1);

	    $message->value = $this->request->getData('Honoraria.message');
	    $calendarConfigurations->save($message);

	    $this->Flash->success('Updated successfully');
    }

    // get the message
    $message = $calendarConfigurations->find()->where(['id' => 1])->first();
    
    $this->set('message', $message->value);

    $this->set('configuration', $configurations->find()->where(['id' => 7])->first());
  }
}
