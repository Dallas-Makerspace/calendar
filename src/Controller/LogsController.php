<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;

class LogsController extends AppController {

    	public function isAuthorized($user = null) {
    	    return $this->inAdminstrativeGroup($user, 'Calendar Super Admins');
    	}

	public function index () {
        	$this->Crud->on(
        	    'beforePaginate',
        	    function (\Cake\Event\Event $event) {
                	  if (!empty($_GET['start_date']) || !empty($_GET['end_date']) || !empty($_GET['user_name']) || !empty($_GET['search_string'])) {
			      $where = [];

			      if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
                	          $start_date = new \DateTime($_GET['start_date'] . ' 23:59:59', new \DateTimeZone('America/Chicago'));
                	          $start_date->setTimezone(new \DateTimeZone('UTC'));

                	          $end_date = new \DateTime($_GET['end_date'] . ' 23:59:59', new \DateTimeZone('America/Chicago'));
                	          $end_date->setTimezone(new \DateTimeZone('UTC'));
		
			          $where['Logs.date_time >='] = $start_date->format('Y-m-d H:i:s');
				  $where['Logs.date_time <='] = $end_date->format('Y-m-d H:i:s');
				
  			      } 

			      if ($_GET['user_name']) {
			          $where['Logs.user LIKE'] = "%" . $_GET['user_name'] . "%";
			      }

			      if ($_GET['search_string']) {
			          $where['Logs.description LIKE'] = "%" . $_GET['search_string'] . "%";
			      }


                	      $event->getSubject()->query
                	          ->where($where )
                	          ->order(['Logs.date_time' => 'DESC']);
        	              $this->paginate['limit'] = 50;
                	  } else {
        	              $event->getSubject()->query->select([
			            	'Logs.date_time',
			            	'Logs.description',
        		            	'Logs.user',
        		            	'Logs.ip_address',
        		            	'Logs.url',
        		            	'Logs.controller',
        		            	'Logs.action',
			            ])
        	                  ->order(['Logs.date_time' => 'DESC']);
        	              $this->paginate['limit'] = 50;
			  }
        	    }
        	);

        	return $this->Crud->execute();
	}
}
