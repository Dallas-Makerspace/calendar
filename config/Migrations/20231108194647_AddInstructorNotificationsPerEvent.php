<?php

use Phinx\Migration\AbstractMigration;

class AddInstructorNotificationsPerEvent extends AbstractMigration
{
    public function up()
    {
        $this->table('events')
            ->addColumn('notifyInstructorRegistrations', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('notifyInstructorCancellations', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->update();
    }

    public function down()
    {
        $this->table('events')
            ->removeColumn('notifyInstructorRegistrations')
            ->removeColumn('notifyInstructorCancellations')
            ->update();
    }
}
