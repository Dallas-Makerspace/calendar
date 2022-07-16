<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\EventsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\EventsTable Test Case
 */
class EventsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\EventsTable
     */
    public $EventsTable;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
		'app.events',
		'app.rooms',
		'app.contacts',
		'app.prerequisites',
		'app.honorarias',
		'app.categories',
		'app.tools',
		'app.files',
		'app.registrations'
	];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('Events') ? [] : ['className' => EventsTable::class];
        $this->EventsTable = TableRegistry::getTableLocator()->get('Events', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->EventsTable);

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

    /**
     * Test hasHonorarium method
     *
     * @return void
     */
    public function testHasHonorarium()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getTotalSpaces method
     *
     * @return void
     */
    public function testGetTotalSpaces()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getFilledSpaces method
     *
     * @return void
     */
    public function testGetFilledSpaces()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test hasFreeSpaces method
     *
     * @return void
     */
    public function testHasFreeSpaces()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test hasPaidSpaces method
     *
     * @return void
     */
    public function testHasPaidSpaces()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test hasOpenSpaces method
     *
     * @return void
     */
    public function testHasOpenSpaces()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test isOwnedBy method
     *
     * @return void
     */
    public function testIsOwnedBy()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
