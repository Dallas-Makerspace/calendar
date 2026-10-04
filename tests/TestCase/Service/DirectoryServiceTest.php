<?php
namespace App\Test\TestCase\Service;

use App\Service\DirectoryService;
use App\Service\DirectoryUnavailableException;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;

/**
 * DirectoryServiceTest
 *
 * The LDAP network round-trip is stubbed via Directory.stubGroups, so these
 * tests never touch the network — except testGroupExistsAgainstRealLdap(),
 * which exercises the real path against the docker OpenLDAP and skips itself
 * when it is unreachable.
 */
class DirectoryServiceTest extends TestCase
{
    /**
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        Configure::write('Directory.stubGroups', ['Laser Basics', 'Kiln Basics']);
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        Configure::write('Directory.stubGroups', null);
        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testGroupExistsWithStubHit()
    {
        $this->assertTrue(DirectoryService::groupExists('Laser Basics'));
    }

    /**
     * @return void
     */
    public function testGroupExistsWithStubMiss()
    {
        $this->assertFalse(DirectoryService::groupExists('No Such Group'));
    }

    /**
     * AD cn matching is case-insensitive; the stub mirrors that.
     *
     * @return void
     */
    public function testGroupExistsStubIsCaseInsensitive()
    {
        $this->assertTrue(DirectoryService::groupExists('laser basics'));
        $this->assertTrue(DirectoryService::groupExists('LASER BASICS'));
    }

    /**
     * @return void
     */
    public function testGroupExistsThrowsWhenLdapUnreachable()
    {
        Configure::write('Directory.stubGroups', null);
        Configure::write('ActiveDirectory.domain_controllers', ['127.0.0.1']);

        $this->expectException(DirectoryUnavailableException::class);
        DirectoryService::groupExists('Laser Basics');
    }

    /**
     * Exercises the real LDAP path against the docker OpenLDAP
     * (cn=admin,dc=dms,dc=local). Skips when LDAP is unreachable, e.g. outside
     * the docker dev environment.
     *
     * @return void
     */
    public function testGroupExistsAgainstRealLdap()
    {
        Configure::write('Directory.stubGroups', null);

        try {
            $result = DirectoryService::groupExists('Calendar Admins');
        } catch (DirectoryUnavailableException $e) {
            $this->markTestSkipped('Real LDAP not reachable: ' . $e->getMessage());
        }

        $this->assertInternalType('bool', $result);
    }
}
