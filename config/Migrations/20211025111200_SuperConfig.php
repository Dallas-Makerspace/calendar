<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

// Adding this to sync up with production DB
class SuperConfig extends AbstractMigration
{
    public function up()
    {
        $this->table('calendar_super_configurations')
            ->addColumn('description',
                'text', [
                'default' => null,
                'limit' => MysqlAdapter::TEXT_TINY,
                'null' => false,
            ])
            ->addColumn('value', 'text', [
                'default' => null,
                'null' => false,
            ])
            ->create();
    }

    public function down()
    {
        $this->dropTable('calendar_super_configurations');
    }
}
