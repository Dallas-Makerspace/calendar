<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RegistrationsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\RegistrationsTable Test Case
 */
class RegistrationsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\RegistrationsTable
     */
    public $RegistrationsTable;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.registrations',
        'app.events'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('Registrations') ? [] : ['className' => RegistrationsTable::class];
        $this->RegistrationsTable = TableRegistry::getTableLocator()->get('Registrations', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->RegistrationsTable);

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
     * Test refund method
     *
     * @return void
     */
    public function testRefund()
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
