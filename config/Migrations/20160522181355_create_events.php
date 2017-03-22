<?php

use Phinx\Migration\AbstractMigration;

class CreateEvents extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('events');
        $table->addColumn('name', 'string', [
            'default' => null,
            'limit' => 80,
            'null' => false,
        ]);
        $table->addColumn('short_description', 'string', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('long_description', 'text', [
            'default' => null,
            'limit' => MysqlAdapter::TEXT_REGULAR,
            'null' => true,
        ]);
        $table->addColumn('advisories', 'text', [
            'default' => null,
            'limit' => MysqlAdapter::TEXT_REGULAR,
            'null' => true,
        ]);
        $table->addColumn('event_start', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('event_end', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('booking_start', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('booking_end', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('cost', 'decimal', [
            'precision' => 15,
            'scale' => 2,
            'null' => false,
        ]);
        $table->addColumn('free_spaces', 'integer', [
            'limit' => 11,
            'null' => false,
        ]);
        $table->addColumn('paid_spaces', 'integer', [
            'limit' => 11,
            'null' => false,
        ]);
        $table->addColumn('members_only', 'boolean', [
            'default' => 0,
            'null' => false,
        ]);
        $table->addColumn('age_restriction', 'integer', [
            'limit' => 3,
            'default' => 0,
            'null' => false,
        ]);
        $table->addColumn('attendees_require_approval', 'boolean', [
            'default' => 0,
            'null' => false,
        ]);
        $table->addColumn('attendee_cancellation', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('class_number', 'integer', [
            'limit' => 1,
            'default' => 0,
            'null' => false,
        ]);
        $table->addColumn('sponsored', 'boolean', [
            'default' => 0,
            'null' => false,
        ]);
        $table->addColumn('status', 'string', [
            'default' => 'pending',
            'limit' => 20,
            'null' => false,
        ]);
        $table->addColumn('room_id', 'integer', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('contact_id', 'integer', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('fulfills_prerequisite_id', 'integer', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('requires_prerequisite_id', 'integer', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('part_of_id', 'integer', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('copy_of_id', 'integer', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('rejected_by', 'string', [
            'default' => null,
            'limit' => 100,
            'null' => true,
        ]);
        $table->addColumn('rejection_reason', 'string', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('created_by', 'string', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('cancel_notification', 'boolean', [
            'default' => 0,
            'null' => false,
        ]);
        $table->addColumn('reminder_notification', 'boolean', [
            'default' => 0,
            'null' => false,
        ]);
        $table->addColumn('created', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('modified', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->create();
    }
}
