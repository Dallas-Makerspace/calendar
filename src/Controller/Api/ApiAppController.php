<?php
namespace App\Controller\Api;

use App\Error\ApiException;
use App\Error\ApiExceptionRenderer;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

/**
 * Base controller for the versioned JSON APIs.
 *
 * Extends the framework controller directly (not AppController) so the HTML
 * side's Crud listeners, menu view vars and auto-serialization stay out.
 *
 * Provides:
 * - Identity resolution: admin session first, then `Authorization: Bearer`
 *   API token; both funnel into the same Calendar Admins authorization check.
 * - The v1 success/error JSON envelopes and the apiLog() audit pattern.
 * - `?page=` / `?per_page=` pagination helpers (default 50, max 200).
 */
class ApiAppController extends Controller
{
    /**
     * The AD group whose members may use the API.
     *
     * @var string
     */
    const ADMIN_GROUP = 'Calendar Admins';

    /**
     * Resolved identity for the current request (session user array or
     * synthetic token identity).
     *
     * @var array|null
     */
    protected $apiIdentity;

    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        Configure::write('Error.exceptionRenderer', ApiExceptionRenderer::class);
    }

    /**
     * Resolve the caller identity and enforce admin authorization.
     *
     * @param \Cake\Event\Event $event The beforeFilter event.
     * @return void
     * @throws \App\Error\ApiException On 401/403/406.
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        $this->assertAcceptsJson();

        $identity = $this->resolveIdentity();
        if ($identity === null) {
            throw new ApiException(
                401,
                'Unauthorized',
                'Authentication required. Provide an API token via the "Authorization: Bearer" ' .
                'header, or sign in as an administrator.'
            );
        }
        if (!$this->isAdmin($identity)) {
            throw new ApiException(
                403,
                'Forbidden',
                'This API requires membership in the "' . static::ADMIN_GROUP . '" group.'
            );
        }
        $this->apiIdentity = $identity;
    }

    /**
     * Force the JSON view and apply _serialize when the action did not.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return void
     */
    public function beforeRender(Event $event)
    {
        parent::beforeRender($event);

        $this->viewBuilder()->setClassName('Json');
        $this->response = $this->response->withType('json');
        if (!array_key_exists('_serialize', $this->viewVars)) {
            $this->set('_serialize', true);
        }
    }

    /**
     * Reject non-JSON callers with 406.
     *
     * @return void
     * @throws \App\Error\ApiException On 406.
     */
    protected function assertAcceptsJson()
    {
        $accept = $this->request->getHeaderLine('Accept');
        if (
            $accept === ''
            || stripos($accept, 'json') !== false
            || stripos($accept, '*/*') !== false
        ) {
            return;
        }
        throw new ApiException(
            406,
            'Not Acceptable',
            'This API only serves application/json responses.'
        );
    }

    /**
     * Resolve the caller identity: admin session first, then Bearer token.
     *
     * @return array|null Identity array with at least `groups`, or null.
     */
    protected function resolveIdentity()
    {
        $sessionUser = $this->request->getSession()->read('Auth.User');
        if (is_array($sessionUser) && !empty($sessionUser)) {
            return $sessionUser;
        }

        $header = trim($this->request->getHeaderLine('Authorization'));
        if (preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            return $this->identityFromToken(trim($matches[1]));
        }

        return null;
    }

    /**
     * Build a synthetic identity from a raw Bearer token.
     *
     * @param string $token The raw token.
     * @return array|null Identity array, or null when the token is not usable.
     */
    protected function identityFromToken($token)
    {
        if ($token === '') {
            return null;
        }
        $tokens = TableRegistry::getTableLocator()->get('ApiTokens');
        $row = $tokens->find('valid', ['tokenHash' => $tokens::hashToken($token)])->first();
        if (!$row) {
            return null;
        }

        $principal = 'token:' . $row->name;
        if (!$tokens->hasScope($row, 'admin')) {
            // Valid token, but without the admin scope: resolve an identity
            // with no groups so the authorization check answers 403.
            return [
                'principal' => $principal,
                'samaccountname' => $principal,
                'groups' => [],
            ];
        }

        $tokens->touchLastUsed($row->id);

        return [
            'principal' => $principal,
            'samaccountname' => $principal,
            'groups' => [static::ADMIN_GROUP],
        ];
    }

    /**
     * Check the Calendar Admins membership, mirroring the HTML side's rule.
     *
     * @param array $identity The resolved identity.
     * @return bool
     */
    protected function isAdmin(array $identity)
    {
        return !empty($identity['groups'])
            && is_array($identity['groups'])
            && in_array(static::ADMIN_GROUP, $identity['groups'], true);
    }

    /**
     * Send a success envelope: `{ "data": ..., "meta": ... }`.
     *
     * @param mixed $data The payload.
     * @param int $status HTTP status code.
     * @param array $meta Meta object ({} when empty).
     * @return void
     */
    protected function apiSuccess($data, $status = 200, array $meta = [])
    {
        $this->set([
            'data' => $data,
            // An empty meta must serialize as {} (not []) per the v1 contract.
            'meta' => $meta ? $meta : (object)[],
            '_serialize' => ['data', 'meta'],
        ]);
        $this->response = $this->response->withStatus($status);
    }

    /**
     * Throw an error envelope response.
     *
     * @param int $status HTTP status code.
     * @param string $title Short error title.
     * @param string $detail Human-readable detail.
     * @param string|null $pointer JSON pointer to the offending field, if any.
     * @return void
     * @throws \App\Error\ApiException Always.
     */
    protected function apiError($status, $title, $detail, $pointer = null)
    {
        throw new ApiException($status, $title, $detail, $pointer);
    }

    /**
     * Label identifying the caller in the audit log: the session
     * samaccountname, or `token:<name>` for machine clients.
     *
     * @return string|null
     */
    protected function apiUserLabel()
    {
        if (!is_array($this->apiIdentity)) {
            return null;
        }
        if (!empty($this->apiIdentity['samaccountname'])) {
            return $this->apiIdentity['samaccountname'];
        }
        if (!empty($this->apiIdentity['principal'])) {
            return $this->apiIdentity['principal'];
        }

        return null;
    }

    /**
     * Write an audit entry to the Logs table, mirroring AppController::customLog().
     *
     * Unlike customLog(), API mutations are always logged (no index/view skip).
     *
     * @param string $description What happened.
     * @return void
     */
    protected function apiLog($description)
    {
        $logs = TableRegistry::getTableLocator()->get('Logs');
        $log = $logs->newEntity();
        $log->description = $description;
        $log->user = $this->apiUserLabel();
        $log->date_time = date('Y-m-d H:i:s');
        $log->ip_address = $this->request->clientIp();
        $log->url = $this->request->getRequestTarget();
        $log->controller = $this->request->getParam('controller');
        $log->action = $this->request->getParam('action');
        $logs->save($log);
    }

    /**
     * Read and clamp the ?page= / ?per_page= pagination parameters.
     *
     * @param int $defaultPerPage Default page size.
     * @param int $maxPerPage Maximum page size.
     * @return array [page, perPage]
     */
    protected function paginationParams($defaultPerPage = 50, $maxPerPage = 200)
    {
        $page = (int)$this->request->getQuery('page', 1);
        $perPage = (int)$this->request->getQuery('per_page', $defaultPerPage);
        $page = max($page, 1);
        $perPage = min(max($perPage, 1), $maxPerPage);

        return [$page, $perPage];
    }
}
