<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Contact;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\Contact Test Case
 */
class ContactTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Entity\Contact
     */
    public $Contact;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Contact = new Contact();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Contact);

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
