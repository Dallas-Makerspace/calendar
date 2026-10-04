<?php
use Migrations\AbstractMigration;

class AddUniqueIndexesToPrerequisites extends AbstractMigration
{
    public function up()
    {
        $this->table('prerequisites')
            ->addIndex(['name'], [
                'unique' => true,
                'name' => 'uq_prerequisites_name',
            ])
            ->addIndex(['ad_group'], [
                'unique' => true,
                'name' => 'uq_prerequisites_ad_group',
            ])
            ->update();
    }

    public function down()
    {
        $this->table('prerequisites')
            ->removeIndexByName('uq_prerequisites_name')
            ->removeIndexByName('uq_prerequisites_ad_group')
            ->update();
    }
}
