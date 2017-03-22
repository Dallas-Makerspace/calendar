<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\EventsToolsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\EventsToolsTable Test Case
 */
class EventsToolsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\EventsToolsTable
     */
    public $EventsTools;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.events_tools',
        'app.tools',
        'app.events',
        'app.rooms',
        'app.contacts',
        'app.honoraria',
        'app.fulfills_prerequisites',
        'app.requires_prerequisites',
        'app.part_ofs',
        'app.copy_ofs',
        'app.categories',
        'app.events_categories'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('EventsTools') ? [] : ['className' => 'App\Model\Table\EventsToolsTable'];
        $this->EventsTools = TableRegistry::get('EventsTools', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->EventsTools);

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

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
