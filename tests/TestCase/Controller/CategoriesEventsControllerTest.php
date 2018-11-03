<?php
namespace App\Test\TestCase\Controller;

use App\Controller\CategoriesEventsController;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\CategoriesEventsController Test Case
 */
class CategoriesEventsControllerTest extends IntegrationTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.categories_events',
        'app.categories',
        'app.events',
        'app.rooms',
        'app.contacts',
        'app.honoraria',
        //'app.fulfills_prerequisites',
        //'app.requires_prerequisites',
        //'app.part_ofs',
        //'app.copy_ofs',
        'app.tools',
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
