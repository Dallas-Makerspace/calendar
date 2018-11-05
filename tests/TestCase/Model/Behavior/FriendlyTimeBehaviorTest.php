<?php
namespace App\Test\TestCase\Model\Behavior;

use App\Model\Table\EventsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Behavior\FriendlyTimeBehavior Test Case
 */
class FriendlyTimeBehaviorTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Behavior\FriendlyTimeBehavior
     */
    public $FriendlyTimeBehavior;

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
     * Test convertFromFormat method
     *
     * @return void
     */
    public function testConvertFromFormat()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test convertToFormat method
     *
     * @return void
     */
    public function testConvertToFormat()
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
