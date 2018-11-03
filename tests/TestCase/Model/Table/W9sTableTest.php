<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\W9sTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\W9sTable Test Case
 */
class W9sTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\W9sTable
     */
    public $W9s;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.w9s',
        'app.contacts',
        'app.events',
        'app.rooms',
        // 'app.fulfills_prerequisites',
        // 'app.requires_prerequisites',
        'app.honoraria',
        'app.committees',
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
        $config = TableRegistry::exists('W9s') ? [] : ['className' => 'App\Model\Table\W9sTable'];
        $this->W9s = TableRegistry::get('W9s', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->W9s);

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
