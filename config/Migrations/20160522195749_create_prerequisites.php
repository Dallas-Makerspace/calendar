<?php

use Phinx\Migration\AbstractMigration;

class CreatePrerequisites extends AbstractMigration
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
         $table = $this->table('prerequisites');
         $table->addColumn('name', 'string', [
             'default' => null,
             'limit' => 80,
             'null' => false,
         ]);
         $table->addColumn('ad_group', 'string', [
             'default' => null,
             'limit' => 100,
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
