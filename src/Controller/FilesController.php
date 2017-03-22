<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Filesystem\File;
use Cake\ORM\TableRegistry;

/**
 * Files Controller
 *
 * @property \App\Model\Table\FilesTable $Files
 */
class FilesController extends AppController
{
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        $this->Crud->disable(['Index', 'Add', 'Edit', 'View']);
    }

    public function isAuthorized($user = null)
    {
        // TODO: Check if authorized for this file

        return parent::isAuthorized($user);
    }

    public function delete($id = null, $eventId = null)
    {
        // Remove file relation from related events (multi-part events)
        $this->Crud->on('beforeDelete', function (\Cake\Event\Event $event) {
            $this->Events = TableRegistry::get('Events');
            $relatedEvents = $this->Events->find('all')
                ->select(['id'])
                ->where(['part_of_id' => $this->request->params['pass'][1]]);

            $events = [];

            foreach ($relatedEvents as $relatedEvent) {
                $events[] = $relatedEvent->id;
            }

            if (!empty($events)) {
                $this->Files->deleteAll([
                    'file' => $event->subject()->entity->file,
                    'event_id IN' => $events
                ]);
            }
        });

        $this->Crud->on('afterDelete', function (\Cake\Event\Event $event) {
            // Delete the file if there are no remaining references to it
            $remainingReferences = $this->Files->find('all')
                ->where(['file' => $event->subject()->entity->file])
                ->count();

            if (!$remainingReferences) {
                $file = new File(str_replace('webroot/', '', $event->subject()->entity->dir) . $event->subject()->entity->file);
                $file->delete();
            }
        });

        return $this->Crud->execute();
    }
}
