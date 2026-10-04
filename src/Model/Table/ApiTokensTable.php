<?php
namespace App\Model\Table;

use App\Model\Entity\ApiToken;
use Cake\I18n\Time;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ApiTokens Model
 *
 * @method \App\Model\Entity\ApiToken get($primaryKey, $options = [])
 * @method \App\Model\Entity\ApiToken newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\ApiToken[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ApiToken|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ApiToken patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ApiToken[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\ApiToken findOrCreate($search, callable $callback = null, $options = [])
 */
class ApiTokensTable extends Table
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

        $this->setTable('api_tokens');
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
            ->notEmpty('name')
            ->maxLength('name', 100);

        $validator
            ->requirePresence('token_hash', 'create')
            ->notEmpty('token_hash')
            ->lengthBetween('token_hash', [64, 64]);

        $validator
            ->requirePresence('scopes', 'create')
            ->notEmpty('scopes')
            ->maxLength('scopes', 255);

        return $validator;
    }

    /**
     * Hash a raw API token with SHA-256. Only the hash is ever stored.
     *
     * @param string $token The raw token.
     * @return string The hex SHA-256 digest.
     */
    public static function hashToken($token)
    {
        return hash('sha256', (string)$token);
    }

    /**
     * Finder for tokens that are usable right now: not revoked and not expired.
     *
     * @param \Cake\ORM\Query $query The query.
     * @param array $options Options; requires `tokenHash`.
     * @return \Cake\ORM\Query
     */
    public function findValid(Query $query, array $options)
    {
        return $query->where([
            'token_hash' => $options['tokenHash'],
            'revoked' => false,
            'OR' => [
                'expires_at IS NULL',
                'expires_at >' => new Time(),
            ],
        ]);
    }

    /**
     * Check whether a token row carries a given scope.
     *
     * @param \App\Model\Entity\ApiToken $token The token entity.
     * @param string $scope The scope to look for.
     * @return bool
     */
    public function hasScope(ApiToken $token, $scope)
    {
        $scopes = array_map('trim', explode(',', (string)$token->scopes));

        return in_array($scope, $scopes, true);
    }

    /**
     * Record that a token was just used.
     *
     * @param int $id The token id.
     * @return void
     */
    public function touchLastUsed($id)
    {
        $this->updateAll(['last_used_at' => new Time()], ['id' => $id]);
    }
}
