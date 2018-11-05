<?php
namespace App\Test\TestCase\Model\Behavior;

use App\Model\Table\EventsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Behavior\RelationalTimeBehavior Test Case
 */
class RelationalTimeBehaviorTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Behavior\RelationalTimeBehavior
     */
    public $RelationalTimeBehavior;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->EventsTable = new EventsTable();
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
     * Test convertFromOffset method
     *
     * @return void
     */
    public function testConvertFromOffset()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test convertToOffset method
     *
     * @return void
     */
    public function testConvertToOffset()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test beforeMarshal method
     *
     * @return void
     */
    public function testBeforeMarshal()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
