<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CommitteesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CommitteesTable Test Case
 */
class CommitteesTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\CommitteesTable
     */
    public $Committees;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.committees',
        'app.honoraria'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Committees') ? [] : ['className' => 'App\Model\Table\CommitteesTable'];
        $this->Committees = TableRegistry::get('Committees', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Committees);

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
