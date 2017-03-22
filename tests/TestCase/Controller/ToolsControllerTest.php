<?php
namespace App\Test\TestCase\Controller;

use App\Controller\ToolsController;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\ToolsController Test Case
 */
class ToolsControllerTest extends IntegrationTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.tools',
        'app.events',
        'app.rooms',
        'app.contacts',
        'app.honoraria',
        'app.committees',
        'app.fulfills_prerequisites',
        'app.requires_prerequisites',
        'app.part_ofs',
        'app.copy_ofs',
        'app.categories',
        'app.events_categories',
        'app.events_tools'
    ];

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
