<?php
namespace App\Controller\Api\V1;

use App\Controller\Api\ApiAppController;
use App\Error\ApiException;
use App\Service\DirectoryService;
use App\Service\DirectoryUnavailableException;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;

/**
 * Prerequisites v1 API.
 *
 * Record API over the prerequisites table (display name <-> AD group mapping).
 * Admin-only; see ApiAppController for auth, envelopes and audit logging.
 */
class PrerequisitesController extends ApiAppController
{
    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->loadModel('Prerequisites');
    }

    /**
     * GET /api/v1/prerequisites — paginated list ordered by name.
     *
     * @return void
     */
    public function index()
    {
        list($page, $perPage) = $this->paginationParams();

        $query = $this->Prerequisites->find()->order(['name' => 'ASC']);
        $total = $query->count();
        $records = $query->limit($perPage)->page($page)->all();

        $data = [];
        foreach ($records as $record) {
            $data[] = $this->serializePrerequisite($record);
        }

        $this->apiSuccess($data, 200, [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => (int)ceil($total / $perPage),
        ]);
    }

    /**
     * GET /api/v1/prerequisites/:id — single record.
     *
     * @param string|null $id The prerequisite id.
     * @return void
     */
    public function view($id = null)
    {
        $prerequisite = $this->getPrerequisite($id);
        $this->apiSuccess($this->serializePrerequisite($prerequisite));
    }

    /**
     * POST /api/v1/prerequisites — create a record.
     *
     * @return void
     */
    public function add()
    {
        $attributes = $this->requestAttributes();
        if (array_key_exists('ad_group', $attributes)) {
            $this->assertAdGroupExists((string)$attributes['ad_group']);
        }

        $prerequisite = $this->Prerequisites->newEntity($attributes);
        if (!$this->Prerequisites->save($prerequisite)) {
            $this->throwValidationErrors($prerequisite);
        }

        $this->apiLog(sprintf(
            "API: created prerequisite '%s' (ad_group='%s')",
            $prerequisite->name,
            $prerequisite->ad_group
        ));

        $this->response = $this->response->withHeader(
            'Location',
            '/api/v1/prerequisites/' . $prerequisite->id
        );
        $this->apiSuccess($this->serializePrerequisite($prerequisite), 201);
    }

    /**
     * PUT/PATCH /api/v1/prerequisites/:id — update a record.
     *
     * @param string|null $id The prerequisite id.
     * @return void
     */
    public function edit($id = null)
    {
        $prerequisite = $this->getPrerequisite($id);
        $attributes = $this->requestAttributes();

        if (
            array_key_exists('ad_group', $attributes)
            && (string)$attributes['ad_group'] !== (string)$prerequisite->ad_group
        ) {
            $this->assertAdGroupExists((string)$attributes['ad_group']);
        }

        $prerequisite = $this->Prerequisites->patchEntity($prerequisite, $attributes);
        if (!$this->Prerequisites->save($prerequisite)) {
            $this->throwValidationErrors($prerequisite);
        }

        $this->apiLog(sprintf(
            "API: updated prerequisite '%s' (ad_group='%s')",
            $prerequisite->name,
            $prerequisite->ad_group
        ));
        $this->apiSuccess($this->serializePrerequisite($prerequisite), 200);
    }

    /**
     * DELETE /api/v1/prerequisites/:id — delete a record.
     *
     * @param string|null $id The prerequisite id.
     * @return \Cake\Http\Response
     */
    public function delete($id = null)
    {
        $prerequisite = $this->getPrerequisite($id);

        $references = $this->Prerequisites->eventReferences((int)$prerequisite->id);
        if ($references['count'] > 0) {
            $this->apiError(
                409,
                'Prerequisite in use',
                $this->Prerequisites->referenceErrorMessage($prerequisite, $references),
                '/data/id'
            );
        }

        if (!$this->Prerequisites->delete($prerequisite)) {
            $this->apiError(
                500,
                'Internal Server Error',
                'The prerequisite could not be deleted.'
            );
        }

        $this->apiLog(sprintf(
            "API: deleted prerequisite '%s' (ad_group='%s')",
            $prerequisite->name,
            $prerequisite->ad_group
        ));

        $this->autoRender = false;
        $this->response = $this->response->withStatus(204);

        return $this->response;
    }

    /**
     * Load a prerequisite or throw a 404 ApiException.
     *
     * @param string|null $id The prerequisite id.
     * @return \App\Model\Entity\Prerequisite
     * @throws \App\Error\ApiException
     */
    protected function getPrerequisite($id)
    {
        try {
            return $this->Prerequisites->get($id);
        } catch (RecordNotFoundException $e) {
            throw new ApiException(
                404,
                'Not Found',
                sprintf('Prerequisite with id %s was not found.', $id)
            );
        }
    }

    /**
     * Read the request attributes.
     *
     * Canonical shape is JSON:API-lite (`data.attributes`); a flat object is
     * also accepted for curl-friendliness. There is no BodyParserMiddleware in
     * the app stack, so the JSON body is decoded here.
     *
     * @return array
     * @throws \App\Error\ApiException On malformed JSON (400).
     */
    protected function requestAttributes()
    {
        $data = $this->request->getData();
        if (empty($data)) {
            $raw = trim((string)$this->request->getBody());
            if ($raw !== '') {
                $decoded = json_decode($raw, true);
                if (!is_array($decoded)) {
                    throw new ApiException(
                        400,
                        'Bad Request',
                        'Malformed JSON in request body.'
                    );
                }
                $data = $decoded;
            }
        }
        if (isset($data['data']['attributes']) && is_array($data['data']['attributes'])) {
            return $data['data']['attributes'];
        }

        return is_array($data) ? $data : [];
    }

    /**
     * Verify an ad_group exists in AD: 422 when unknown, 503 when AD is down.
     *
     * @param string $cn The group cn.
     * @return void
     * @throws \App\Error\ApiException
     */
    protected function assertAdGroupExists($cn)
    {
        try {
            $exists = DirectoryService::groupExists($cn);
        } catch (DirectoryUnavailableException $e) {
            throw new ApiException(
                503,
                'Service Unavailable',
                'Active Directory is unreachable; ad_group could not be verified. ' .
                'Please try again later.'
            );
        }
        if (!$exists) {
            throw new ApiException(
                422,
                'Validation failed',
                sprintf("ad_group '%s' was not found in Active Directory", $cn),
                '/data/attributes/ad_group'
            );
        }
    }

    /**
     * Throw a 422 ApiException from an entity's validation/rules errors.
     *
     * @param \Cake\Datasource\EntityInterface $entity The failed entity.
     * @return void
     * @throws \App\Error\ApiException Always.
     */
    protected function throwValidationErrors(EntityInterface $entity)
    {
        $errors = [];
        foreach ($entity->getErrors() as $field => $fieldErrors) {
            foreach ((array)$fieldErrors as $message) {
                if (is_array($message)) {
                    $message = implode(' ', $message);
                }
                $errors[] = [
                    'status' => '422',
                    'title' => 'Validation failed',
                    'detail' => (string)$message,
                    'source' => ['pointer' => '/data/attributes/' . $field],
                ];
            }
        }
        if (empty($errors)) {
            $errors[] = [
                'status' => '422',
                'title' => 'Validation failed',
                'detail' => 'The record could not be saved.',
            ];
        }
        throw ApiException::withErrors(422, $errors);
    }

    /**
     * Serialize a prerequisite to the v1 JSON shape (snake_case, ISO-8601 UTC).
     *
     * @param \App\Model\Entity\Prerequisite $prerequisite The entity.
     * @return array
     */
    protected function serializePrerequisite($prerequisite)
    {
        return [
            'id' => $prerequisite->id,
            'name' => $prerequisite->name,
            'ad_group' => $prerequisite->ad_group,
            'created' => $this->iso8601($prerequisite->created),
            'modified' => $this->iso8601($prerequisite->modified),
        ];
    }

    /**
     * Format a datetime as ISO-8601 UTC.
     *
     * @param \Cake\I18n\Time|null $time The datetime.
     * @return string|null
     */
    protected function iso8601($time)
    {
        if (!$time) {
            return null;
        }
        $utc = clone $time;
        $utc->setTimezone(new \DateTimeZone('UTC'));

        return $utc->format('c');
    }
}
