<?php
namespace App\Test\TestCase\Controller\Api\V1;

use App\Test\Fixture\ApiTokensFixture;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

/**
 * PrerequisitesControllerTest
 *
 * Covers the v1 prerequisites record API: auth (session + token), the JSON
 * envelopes, pagination meta, CRUD status codes, the AD-existence validation
 * (stubbed via Directory.stubGroups), the delete-while-referenced 409 guard
 * and audit logging.
 */
class PrerequisitesControllerTest extends IntegrationTestCase
{
    /**
     * @var array
     */
    public $fixtures = [
        'app.Prerequisites',
        'app.Events',
        'app.ApiTokens',
        'app.Logs',
    ];

    /**
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        Configure::write('Directory.stubGroups', [
            'Laser Basics',
            'Kiln Basics',
            'Woodshop Basics',
            'DMS-Woodshop-Basics',
            'Test Prerequisite',
            'Unreferenced Prerequisite',
        ]);
    }

    /**
     * Authenticate as an admin via the session login path.
     *
     * @return void
     */
    protected function asAdminSession()
    {
        $this->session([
            'Auth.User' => [
                'samaccountname' => 'adminuser',
                'groups' => ['Calendar Admins'],
            ],
        ]);
    }

    /**
     * Authenticate as a signed-in user who is NOT a Calendar Admin.
     *
     * @return void
     */
    protected function asNonAdminSession()
    {
        $this->session([
            'Auth.User' => [
                'samaccountname' => 'regularuser',
                'groups' => ['Some Other Group'],
            ],
        ]);
    }

    /**
     * Authenticate with a Bearer API token.
     *
     * @param string $plainToken The plain-text token.
     * @return void
     */
    protected function withToken($plainToken = ApiTokensFixture::ADMIN_TOKEN)
    {
        $this->configRequest([
            'headers' => [
                'Authorization' => 'Bearer ' . $plainToken,
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * POST a JSON body, preserving any headers set earlier (e.g. auth).
     *
     * @param string $url The URL.
     * @param mixed $body Array to encode, or a raw string.
     * @return void
     */
    protected function postJson($url, $body)
    {
        $this->jsonRequestHeaders();
        $this->post($url, is_string($body) ? $body : json_encode($body));
    }

    /**
     * PATCH a JSON body, preserving any headers set earlier (e.g. auth).
     *
     * @param string $url The URL.
     * @param mixed $body Array to encode, or a raw string.
     * @return void
     */
    protected function patchJson($url, $body)
    {
        $this->jsonRequestHeaders();
        $this->patch($url, is_string($body) ? $body : json_encode($body));
    }

    /**
     * Set JSON content headers, merged with (not replacing) existing headers.
     *
     * @return void
     */
    protected function jsonRequestHeaders()
    {
        $headers = isset($this->_request['headers']) ? $this->_request['headers'] : [];
        $this->configRequest([
            'headers' => $headers + [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Decode the response body as JSON.
     *
     * @return mixed
     */
    protected function responseJson()
    {
        return json_decode((string)$this->_response->getBody(), true);
    }

    /**
     * Assert the response carries the error envelope with the given status.
     *
     * @param int $status Expected HTTP status.
     * @param string|null $title Expected error title.
     * @return array The decoded body.
     */
    protected function assertErrorEnvelope($status, $title = null)
    {
        $this->assertResponseCode($status);
        $this->assertContentType('application/json');
        $json = $this->responseJson();
        $this->assertArrayHasKey('errors', $json);
        $this->assertNotEmpty($json['errors']);
        $this->assertSame((string)$status, $json['errors'][0]['status']);
        if ($title !== null) {
            $this->assertSame($title, $json['errors'][0]['title']);
        }

        return $json;
    }

    /**
     * @return void
     */
    public function testIndexRequiresAuthentication()
    {
        $this->get('/api/v1/prerequisites');
        $this->assertErrorEnvelope(401, 'Unauthorized');
    }

    /**
     * @return void
     */
    public function testIndexPaginationMeta()
    {
        $this->asAdminSession();
        $this->get('/api/v1/prerequisites?per_page=1');

        $this->assertResponseCode(200);
        $json = $this->responseJson();
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('meta', $json);
        $this->assertCount(1, $json['data']);
        $this->assertSame(1, $json['meta']['page']);
        $this->assertSame(1, $json['meta']['per_page']);
        $this->assertSame(2, $json['meta']['total']);
        $this->assertSame(2, $json['meta']['total_pages']);
        // ordered by name
        $this->assertSame('Lorem ipsum dolor sit amet', $json['data'][0]['name']);
    }

    /**
     * @return void
     */
    public function testIndexWithTokenAuth()
    {
        $this->withToken();
        $this->get('/api/v1/prerequisites');

        $this->assertResponseCode(200);
        $json = $this->responseJson();
        $this->assertCount(2, $json['data']);
        $this->assertSame(2, $json['meta']['total']);
    }

    /**
     * @return void
     */
    public function testView()
    {
        $this->asAdminSession();
        $this->get('/api/v1/prerequisites/1');

        $this->assertResponseCode(200);
        $json = $this->responseJson();
        $this->assertArrayHasKey('data', $json);
        $this->assertSame([], $json['meta']);
        $data = $json['data'];
        $this->assertSame(1, $data['id']);
        $this->assertSame('Lorem ipsum dolor sit amet', $data['name']);
        $this->assertSame('Lorem ipsum dolor sit amet', $data['ad_group']);
        $this->assertRegExp(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+00:00$/',
            $data['created']
        );
        $this->assertRegExp(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+00:00$/',
            $data['modified']
        );
        $this->assertSame(
            ['id', 'name', 'ad_group', 'created', 'modified'],
            array_keys($data)
        );
    }

    /**
     * @return void
     */
    public function testViewNotFound()
    {
        $this->asAdminSession();
        $this->get('/api/v1/prerequisites/999');

        $this->assertErrorEnvelope(404, 'Not Found');
    }

    /**
     * @return void
     */
    public function testCreate()
    {
        $this->withToken();
        $this->postJson('/api/v1/prerequisites', [
            'data' => [
                'attributes' => [
                    'name' => 'Kiln Basics',
                    'ad_group' => 'Kiln Basics',
                ],
            ],
        ]);

        $this->assertResponseCode(201);
        $this->assertHeader('Location', '/api/v1/prerequisites/3');
        $json = $this->responseJson();
        $this->assertSame('Kiln Basics', $json['data']['name']);
        $this->assertSame('Kiln Basics', $json['data']['ad_group']);
        $this->assertSame(3, $json['data']['id']);
        $this->assertSame([], $json['meta']);
    }

    /**
     * @return void
     */
    public function testCreateAcceptsFlatBody()
    {
        $this->withToken();
        $this->postJson('/api/v1/prerequisites', [
            'name' => 'Woodshop Basics',
            'ad_group' => 'DMS-Woodshop-Basics',
        ]);

        $this->assertResponseCode(201);
        $json = $this->responseJson();
        $this->assertSame('Woodshop Basics', $json['data']['name']);
        $this->assertSame('DMS-Woodshop-Basics', $json['data']['ad_group']);
    }

    /**
     * @return void
     */
    public function testCreateRejectsUnknownAdGroup()
    {
        $this->withToken();
        $this->postJson('/api/v1/prerequisites', [
            'data' => [
                'attributes' => [
                    'name' => 'Nope Basics',
                    'ad_group' => 'No Such Group',
                ],
            ],
        ]);

        $json = $this->assertErrorEnvelope(422, 'Validation failed');
        $this->assertSame(
            "ad_group 'No Such Group' was not found in Active Directory",
            $json['errors'][0]['detail']
        );
        $this->assertSame(
            '/data/attributes/ad_group',
            $json['errors'][0]['source']['pointer']
        );
    }

    /**
     * @return void
     */
    public function testCreateRejectsDuplicateName()
    {
        $this->withToken();
        $this->postJson('/api/v1/prerequisites', [
            'name' => 'Lorem ipsum dolor sit amet',
            'ad_group' => 'Kiln Basics',
        ]);

        $json = $this->assertErrorEnvelope(422, 'Validation failed');
        $this->assertSame(
            '/data/attributes/name',
            $json['errors'][0]['source']['pointer']
        );
    }

    /**
     * @return void
     */
    public function testCreateRejectsDuplicateAdGroup()
    {
        $this->withToken();
        $this->postJson('/api/v1/prerequisites', [
            'name' => 'Laser Basics',
            'ad_group' => 'Laser Basics',
        ]);
        $this->assertResponseCode(201);

        // _buildRequest() consumes the configured headers, so re-authenticate.
        $this->withToken();
        $this->postJson('/api/v1/prerequisites', [
            'name' => 'Laser Basics Again',
            'ad_group' => 'Laser Basics',
        ]);

        $json = $this->assertErrorEnvelope(422, 'Validation failed');
        $this->assertSame(
            '/data/attributes/ad_group',
            $json['errors'][0]['source']['pointer']
        );
    }

    /**
     * @return void
     */
    public function testCreateRejectsMissingFields()
    {
        $this->withToken();
        $this->postJson('/api/v1/prerequisites', [
            'data' => ['attributes' => []],
        ]);

        $json = $this->assertErrorEnvelope(422, 'Validation failed');
        $pointers = array_column(array_column($json['errors'], 'source'), 'pointer');
        $this->assertContains('/data/attributes/name', $pointers);
        $this->assertContains('/data/attributes/ad_group', $pointers);
    }

    /**
     * @return void
     */
    public function testCreateRejectsMalformedJson()
    {
        $this->withToken();
        $this->postJson('/api/v1/prerequisites', '{not valid json');

        $this->assertErrorEnvelope(400, 'Bad Request');
    }

    /**
     * @return void
     */
    public function testCreateFailsClosedWhenLdapUnreachable()
    {
        Configure::write('Directory.stubGroups', null);
        Configure::write('ActiveDirectory.domain_controllers', ['127.0.0.1']);

        $this->withToken();
        $this->postJson('/api/v1/prerequisites', [
            'name' => 'Kiln Basics',
            'ad_group' => 'Kiln Basics',
        ]);

        $this->assertErrorEnvelope(503, 'Service Unavailable');
    }

    /**
     * @return void
     */
    public function testUpdateRenamesDisplayLabel()
    {
        $this->asAdminSession();
        $this->patchJson('/api/v1/prerequisites/2', [
            'data' => ['attributes' => ['name' => 'Renamed Label']],
        ]);

        $this->assertResponseCode(200);
        $json = $this->responseJson();
        $this->assertSame('Renamed Label', $json['data']['name']);
        $this->assertSame('Unreferenced Prerequisite', $json['data']['ad_group']);
    }

    /**
     * @return void
     */
    public function testUpdateAdGroupRevalidatesAgainstAd()
    {
        $this->asAdminSession();
        $this->patchJson('/api/v1/prerequisites/2', [
            'ad_group' => 'No Such Group',
        ]);

        $json = $this->assertErrorEnvelope(422, 'Validation failed');
        $this->assertSame(
            '/data/attributes/ad_group',
            $json['errors'][0]['source']['pointer']
        );
    }

    /**
     * @return void
     */
    public function testUpdateAdGroupToKnownGroup()
    {
        $this->asAdminSession();
        $this->patchJson('/api/v1/prerequisites/2', [
            'ad_group' => 'Kiln Basics',
        ]);

        $this->assertResponseCode(200);
        $json = $this->responseJson();
        $this->assertSame('Kiln Basics', $json['data']['ad_group']);
    }

    /**
     * @return void
     */
    public function testUpdateNotFound()
    {
        $this->asAdminSession();
        $this->patchJson('/api/v1/prerequisites/999', ['name' => 'x']);

        $this->assertErrorEnvelope(404, 'Not Found');
    }

    /**
     * @return void
     */
    public function testDelete()
    {
        $this->withToken();
        $this->delete('/api/v1/prerequisites/2');

        $this->assertResponseCode(204);
        $this->assertSame('', (string)$this->_response->getBody());

        $prerequisites = TableRegistry::getTableLocator()->get('Prerequisites');
        $this->assertSame(0, $prerequisites->find()->where(['id' => 2])->count());
    }

    /**
     * @return void
     */
    public function testDeleteWhenReferencedReturnsConflict()
    {
        $this->withToken();
        $this->delete('/api/v1/prerequisites/1');

        $json = $this->assertErrorEnvelope(409, 'Prerequisite in use');
        $this->assertContains('referenced by', $json['errors'][0]['detail']);
        $this->assertSame('/data/id', $json['errors'][0]['source']['pointer']);

        $prerequisites = TableRegistry::getTableLocator()->get('Prerequisites');
        $this->assertSame(1, $prerequisites->find()->where(['id' => 1])->count());
    }

    /**
     * @return void
     */
    public function testDeleteNotFound()
    {
        $this->withToken();
        $this->delete('/api/v1/prerequisites/999');

        $this->assertErrorEnvelope(404, 'Not Found');
    }

    /**
     * @return void
     */
    public function testForbiddenForNonAdminSession()
    {
        $this->asNonAdminSession();
        $this->get('/api/v1/prerequisites');

        $this->assertErrorEnvelope(403, 'Forbidden');
    }

    /**
     * @return void
     */
    public function testForbiddenForTokenWithoutAdminScope()
    {
        $this->withToken(ApiTokensFixture::LIMITED_TOKEN);
        $this->get('/api/v1/prerequisites');

        $this->assertErrorEnvelope(403, 'Forbidden');
    }

    /**
     * @return void
     */
    public function testUnauthorizedForBogusToken()
    {
        $this->withToken('bogus-token-value');
        $this->get('/api/v1/prerequisites');

        $this->assertErrorEnvelope(401, 'Unauthorized');
    }

    /**
     * @return void
     */
    public function testUnauthorizedForRevokedToken()
    {
        $this->withToken(ApiTokensFixture::REVOKED_TOKEN);
        $this->get('/api/v1/prerequisites');

        $this->assertErrorEnvelope(401, 'Unauthorized');
    }

    /**
     * @return void
     */
    public function testUnauthorizedForExpiredToken()
    {
        $this->withToken(ApiTokensFixture::EXPIRED_TOKEN);
        $this->get('/api/v1/prerequisites');

        $this->assertErrorEnvelope(401, 'Unauthorized');
    }

    /**
     * @return void
     */
    public function testNotAcceptableForNonJson()
    {
        $this->configRequest(['headers' => ['Accept' => 'text/html']]);
        $this->session([
            'Auth.User' => [
                'samaccountname' => 'adminuser',
                'groups' => ['Calendar Admins'],
            ],
        ]);
        $this->get('/api/v1/prerequisites');

        $this->assertErrorEnvelope(406, 'Not Acceptable');
    }

    /**
     * @return void
     */
    public function testMutationsAreAuditLogged()
    {
        $this->withToken();
        $this->postJson('/api/v1/prerequisites', [
            'name' => 'Kiln Basics',
            'ad_group' => 'Kiln Basics',
        ]);
        $this->assertResponseCode(201);

        $logs = TableRegistry::getTableLocator()->get('Logs');
        $entry = $logs->find()
            ->where(['description LIKE' => 'API: created prerequisite%'])
            ->first();
        $this->assertNotEmpty($entry);
        $this->assertSame(
            "API: created prerequisite 'Kiln Basics' (ad_group='Kiln Basics')",
            $entry->description
        );
        $this->assertSame('token:test-admin-token', $entry->user);
        $this->assertSame('Prerequisites', $entry->controller);
        $this->assertSame('add', $entry->action);
    }

    /**
     * @return void
     */
    public function testTokenLastUsedIsUpdated()
    {
        $this->withToken();
        $this->get('/api/v1/prerequisites');
        $this->assertResponseCode(200);

        $tokens = TableRegistry::getTableLocator()->get('ApiTokens');
        $token = $tokens->get(1);
        $this->assertNotEmpty($token->last_used_at);
    }
}
