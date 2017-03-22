<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PrerequisitesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\PrerequisitesTable Test Case
 */
class PrerequisitesTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\PrerequisitesTable
     */
    public $Prerequisites;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.prerequisites'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Prerequisites') ? [] : ['className' => 'App\Model\Table\PrerequisitesTable'];
        $this->Prerequisites = TableRegistry::get('Prerequisites', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Prerequisites);

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
