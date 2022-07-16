<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Event;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\Event Test Case
 */
class EventTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Entity\Event
     */
    public $Event;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Event = new Event();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Event);

        parent::tearDown();
    }

    /**
     * Test initial setup
     *
     * @return void
     */
    public function testInitialization()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
