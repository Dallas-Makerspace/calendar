<?php
use Migrations\AbstractSeed;

/**
 * Configurations seed.
 */
class ConfigurationsSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     *
     * @return void
     */
	public function run()
    {
        date_default_timezone_set('UTC');
        $data = [
        	[
        		'id' => 1,
        		'name' => 'Automatic Approval Time',
        		'value' => 2,
        		'created' => date('Y-m-d H:i:s'),
        		'modified' => date('Y-m-d H:i:s')
        	], [
        		'id' => 2,
        		'name' => 'Honoraria Approval Time',
        		'value' => 3,
        		'created' => date('Y-m-d H:i:s'),
        		'modified' => date('Y-m-d H:i:s')
        	], [
        		'id' => 3,
        		'name' => 'Honoraria Booking Lead Time',
        		'value' => 10,
        		'created' => date('Y-m-d H:i:s'),
        		'modified' => date('Y-m-d H:i:s')
        	], [
        		'id' => 4,
        		'name' => 'Minimum Booking Lead Time',
        		'value' => 2,
        		'created' => date('Y-m-d H:i:s'),
        		'modified' => date('Y-m-d H:i:s')
        	], [
        		'id' => 5,
        		'name' => 'Maximum Booking Lead Time',
        		'value' => 190,
        		'created' => date('Y-m-d H:i:s'),
        		'modified' => date('Y-m-d H:i:s')
        	], [
        		'id' => 6,
        		'name' => 'Role Call Cutoff',
        		'value' => 2,
        		'created' => date('Y-m-d H:i:s'),
        		'modified' => date('Y-m-d H:i:s')
        	]
        ];
        
        $table = $this->table('configurations');
        $table->insert($data)->save();
    }
}
