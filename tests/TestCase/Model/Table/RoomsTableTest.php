<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RoomsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\RoomsTable Test Case
 */
class RoomsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\RoomsTable
     */
    public $Rooms;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.rooms',
        'app.events',
        'app.contacts',
        'app.honoraria',
        'app.committees',
        // 'app.fulfills_prerequisites',
        // 'app.requires_prerequisites',
        // 'app.part_ofs',
        // 'app.copy_ofs',
        'app.categories',
        'app.categories_events',
        'app.tools',
        'app.events_tools'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Rooms') ? [] : ['className' => 'App\Model\Table\RoomsTable'];
        $this->Rooms = TableRegistry::get('Rooms', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Rooms);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
