<?php
use Phinx\Seed\AbstractSeed;

/**
 * Rooms seed.
 */
class RoomsSeed extends AbstractSeed
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
                'name' => '3D Fabrication Room',
                'exclusive' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Back Parking Lot',
                'exclusive' => false,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Common Area',
                'exclusive' => false,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Conference Room',
                'exclusive' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Creative Arts Studio',
                'exclusive' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Digital Media Room',
                'exclusive' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Electronics Room',
                'exclusive' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Interactive Classroom',
                'exclusive' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Lecture Hall',
                'exclusive' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Metalworking Workshop',
                'exclusive' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Purple Classroom',
                'exclusive' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Woodworking Workshop',
                'exclusive' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Workshop',
                'exclusive' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Workshop - Automotive',
                'exclusive' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Workshop - Foundry',
                'exclusive' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Workshop - Laser',
                'exclusive' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Workshop - Machine Shop',
                'exclusive' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Workshop - Science Area',
                'exclusive' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Workshop - Vector',
                'exclusive' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Workshop - Open Table Area North (Woodworking Only)',
                'exclusive' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Workshop - Open Table Area Central',
                'exclusive' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Workshop - Open Table Area South',
                'exclusive' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Offsite (See Event Description)',
                'exclusive' => false,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Galley',
                'exclusive' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Workshop - Fired Arts',
                'exclusive' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ], [
                'name' => 'Workshop - Jewelry / Small Metals',
                'exclusive' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ]
        ];

        $table = $this->table('rooms');
        $table->insert($data)->save();
    }
}
