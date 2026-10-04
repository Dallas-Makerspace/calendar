<?php
namespace App\Controller\Api\V1;

use App\Controller\Api\ApiAppController;
use App\Error\ApiException;
use App\Model\Entity\Prerequisite;
use App\Service\DirectoryUnavailableException;
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

        $prerequisite = $this->Prerequisites->newEntity($attributes);
        if (!$this->savePrerequisite($prerequisite)) {
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

        $prerequisite = $this->Prerequisites->patchEntity($prerequisite, $attributes);
        if (!$this->savePrerequisite($prerequisite)) {
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
            // An event reference may have appeared between the check above
            // and the delete (the addDelete model rule then blocks it).
            $references = $this->Prerequisites->eventReferences((int)$prerequisite->id);
            if ($references['count'] > 0) {
                $this->apiError(
                    409,
                    'Prerequisite in use',
                    $this->Prerequisites->referenceErrorMessage($prerequisite, $references),
                    '/data/id'
                );
            }
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

        $this->response = $this->response->withStatus(204);

        return $this->response;
    }

    /**
     * Save a prerequisite, translating infrastructure failures into API errors.
     *
     * The AD-existence model rule binds to LDAP during save() and throws
     * DirectoryUnavailableException when AD is unreachable (fail closed →
     * 503). A duplicate that slips past the isUnique rules between check and
     * INSERT surfaces as a duplicate-key PDOException (SQLSTATE 23000) → 422
     * with the same detail text the rules would have produced.
     *
     * @param \App\Model\Entity\Prerequisite $prerequisite The entity to save.
     * @return bool True when the save succeeded, false on rule failures.
     * @throws \App\Error\ApiException On 503 (AD down) or 422 (duplicate race).
     */
    protected function savePrerequisite(Prerequisite $prerequisite)
    {
        try {
            return (bool)$this->Prerequisites->save($prerequisite);
        } catch (DirectoryUnavailableException $e) {
            throw new ApiException(
                503,
                'Service Unavailable',
                'Active Directory is unreachable; ad_group could not be verified. ' .
                'Please try again later.'
            );
        } catch (\PDOException $e) {
            if ((string)$e->getCode() === '23000') {
                $this->throwDuplicateConflict($prerequisite);
            }
            throw $e;
        }
    }

    /**
     * Throw a 422 ApiException identifying which unique field lost a
     * check-then-insert race.
     *
     * @param \App\Model\Entity\Prerequisite $prerequisite The failed entity.
     * @return void
     * @throws \App\Error\ApiException Always.
     */
    protected function throwDuplicateConflict(Prerequisite $prerequisite)
    {
        $errors = [];
        $excludeSelf = $prerequisite->id ? ['id !=' => $prerequisite->id] : [];
        $fieldMessages = [
            'name' => 'This name is already in use.',
            'ad_group' => 'This AD group is already mapped to another prerequisite.',
        ];
        foreach ($fieldMessages as $field => $message) {
            $value = $prerequisite->get($field);
            if ($value === null || $value === '') {
                continue;
            }
            $conditions = array_merge($excludeSelf, [$field => $value]);
            if ($this->Prerequisites->exists($conditions)) {
                $errors[] = [
                    'status' => '422',
                    'title' => 'Validation failed',
                    'detail' => $message,
                    'source' => ['pointer' => '/data/attributes/' . $field],
                ];
            }
        }
        if (empty($errors)) {
            // An integrity violation we cannot attribute to a field: keep it
            // honest instead of inventing a duplicate detail.
            throw new ApiException(
                500,
                'Internal Server Error',
                'The record could not be saved.'
            );
        }
        throw ApiException::withErrors(422, $errors);
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
}
