<?php
use Phinx\Seed\AbstractSeed;

/**
 * Prerequisites seed.
 */
class PrerequisitesSeed extends AbstractSeed
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
                'name' => '3D Printer Basics',
                'ad_group' => '3D Printer Basics',
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Laser Basics',
                'ad_group' => 'Laser Basics',
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Multicam CNC Router',
                'ad_group' => 'Multicam CNC Router',
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Woodshop Basics',
                'ad_group' => 'Woodshop Basics',
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ]
        ];

        $table = $this->table('prerequisites');
        $table->insert($data)->save();
    }
}
