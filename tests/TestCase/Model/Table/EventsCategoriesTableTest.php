<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\EventsCategoriesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\EventsCategoriesTable Test Case
 */
class EventsCategoriesTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\EventsCategoriesTable
     */
    public $EventsCategories;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.events_categories',
        'app.categories',
        'app.events',
        'app.rooms',
        'app.contacts',
        'app.honoraria',
        'app.fulfills_prerequisites',
        'app.requires_prerequisites',
        'app.part_ofs',
        'app.copy_ofs',
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
        $config = TableRegistry::exists('EventsCategories') ? [] : ['className' => 'App\Model\Table\EventsCategoriesTable'];
        $this->EventsCategories = TableRegistry::get('EventsCategories', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->EventsCategories);

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
