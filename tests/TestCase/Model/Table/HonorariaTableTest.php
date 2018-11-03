<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\HonorariaTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\HonorariaTable Test Case
 */
class HonorariaTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\HonorariaTable
     */
    public $Honoraria;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.honoraria',
        'app.events',
        'app.rooms',
        'app.contacts',
        // 'app.fulfills_prerequisites',
        // 'app.requires_prerequisites',
        // 'app.part_ofs',
        // 'app.copy_ofs',
        'app.categories',
        'app.categories_events',
        'app.tools',
        'app.events_tools',
        'app.committees'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Honoraria') ? [] : ['className' => 'App\Model\Table\HonorariaTable'];
        $this->Honoraria = TableRegistry::get('Honoraria', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Honoraria);

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
