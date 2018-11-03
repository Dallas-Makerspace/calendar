<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CategoriesEventsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CategoriesEventsTable Test Case
 */
class CategoriesEventsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\CategoriesEventsTable
     */
    public $CategoriesEvents;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.categories_events',
        'app.categories',
        'app.events',
        'app.rooms',
        'app.contacts',
        'app.honoraria',
        // 'app.fulfills_prerequisites',
        // 'app.requires_prerequisites',
        // 'app.part_ofs',
        // 'app.copy_ofs',
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
        $config = TableRegistry::exists('CategoriesEvents') ? [] : ['className' => 'App\Model\Table\CategoriesEventsTable'];
        $this->CategoriesEvents = TableRegistry::get('CategoriesEvents', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->CategoriesEvents);

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
