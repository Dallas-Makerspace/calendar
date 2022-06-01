<?php
use Migrations\AbstractSeed;

/**
 * Configurations seed.
 */
class SuperConfigSeed extends AbstractSeed
{

    public function run()
    {
        date_default_timezone_set('UTC');
        $data = [
            [
                'id' => 1,
                'description' => 'Honoraria Message',
                'value' => ''
            ]
        ];

        $table = $this->table('calendar_super_configurations');
        $table->insert($data)->save();
    }
}
