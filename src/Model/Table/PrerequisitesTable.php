<?php
namespace App\Model\Table;

use App\Model\Entity\Prerequisite;
use App\Service\DirectoryService;
use App\Service\DirectoryUnavailableException;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Prerequisites Model
 *
 */
class PrerequisitesTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('prerequisites');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->requirePresence('ad_group', 'create')
            ->notEmpty('ad_group');

        return $validator;
    }

    /**
     * Application integrity rules.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules checker.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add(
            $rules->isUnique(['name'], 'This name is already in use.'),
            'uniqueName'
        );
        $rules->add(
            $rules->isUnique(['ad_group'], 'This AD group is already mapped to another prerequisite.'),
            'uniqueAdGroup'
        );

        $rules->add(
            function ($entity, $options) {
                if (!$entity->isNew() && !$entity->isDirty('ad_group')) {
                    return true;
                }
                try {
                    $exists = DirectoryService::groupExists((string)$entity->ad_group);
                } catch (DirectoryUnavailableException $e) {
                    return 'Active Directory is unreachable; ad_group could not be verified.';
                }
                if (!$exists) {
                    return sprintf(
                        "ad_group '%s' was not found in Active Directory",
                        $entity->ad_group
                    );
                }

                return true;
            },
            'adGroupExistsInAd',
            ['errorField' => 'ad_group']
        );

        $rules->addDelete(
            function ($entity, $options) {
                $references = $this->eventReferences((int)$entity->id);
                if ($references['count'] === 0) {
                    return true;
                }

                return $this->referenceErrorMessage($entity, $references);
            },
            'notReferencedByEvents',
            ['errorField' => 'id']
        );

        return $rules;
    }

    /**
     * Count the events referencing a prerequisite and collect example names.
     *
     * @param int $prerequisiteId The prerequisite id.
     * @return array Array with `count` and `examples` (up to 3 event names).
     */
    public function eventReferences($prerequisiteId)
    {
        $events = TableRegistry::getTableLocator()->get('Events');
        $query = $events->find()
            ->select(['id', 'name'])
            ->where([
                'OR' => [
                    'fulfills_prerequisite_id' => $prerequisiteId,
                    'requires_prerequisite_id' => $prerequisiteId,
                ],
            ])
            ->order(['name' => 'ASC']);

        $count = $query->count();
        $examples = [];
        if ($count > 0) {
            $examples = $query->limit(3)->extract('name')->toList();
        }

        return ['count' => $count, 'examples' => $examples];
    }

    /**
     * Build the "cannot delete, referenced by events" message.
     *
     * @param \Cake\Datasource\EntityInterface $entity The prerequisite entity.
     * @param array $references The result of eventReferences().
     * @return string
     */
    public function referenceErrorMessage(EntityInterface $entity, array $references)
    {
        $quoted = array_map(function ($name) {
            return sprintf("'%s'", $name);
        }, $references['examples']);
        $examples = $quoted ? sprintf(', e.g. %s', implode(', ', $quoted)) : '';

        return sprintf(
            "Cannot delete prerequisite '%s': it is referenced by %d event(s)%s. Remove the references first.",
            $entity->name,
            $references['count'],
            $examples
        );
    }
}
