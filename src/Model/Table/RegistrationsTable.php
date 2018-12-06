<?php
namespace App\Model\Table;

use App\Model\Entity\Registration;
use Cake\Core\Configure;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Registrations Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Events
 */
class RegistrationsTable extends Table
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

        $this->setTable('registrations');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Events', ['joinType' => 'INNER']);
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
            ->requirePresence('event_id', 'create')
            ->notEmpty('event_id');

        $validator
            ->requirePresence('type', 'create')
            ->notEmpty('type')
            ->add('type', 'inList', [
                'rule' => ['inList', ['free', 'paid']]
            ]);

        $validator
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmpty('email')
            ->add('email', ['unique' => [
                'rule' => ['validateUnique', ['scope' => 'event_id']],
                'provider' => 'table',
                'message' => 'This email address is already associated with a registration for this event.'
            ]]);

        $validator
            ->allowEmpty('phone');

        $validator
            ->allowEmpty('ad_username')
            ->add('ad_username', ['unique' => [
                'rule' => ['validateUnique', ['scope' => 'event_id']], 'provider' => 'table']
            ]);

        $validator
            ->boolean('send_text')
            ->requirePresence('send_text', 'create')
            ->notEmpty('send_text');

        $validator
            ->alphaNumeric('edit_key')
            ->requirePresence('edit_key', 'create')
            ->notEmpty('edit_key');

        $validator
            ->requirePresence('status', 'create')
            ->notEmpty('status')
            ->add('status', 'inList', [
                'rule' => ['inList', ['confirmed', 'cancelled', 'pending']]
            ]);

        $validator
            ->allowEmpty('attended');

        $validator
            ->boolean('attended')
            ->allowEmpty('attended');

        $validator
            ->boolean('ad_assigned')
            ->allowEmpty('ad_assigned');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['event_id'], 'Events'));

        return $rules;
    }

    public function refund($id)
    {
        if ($this->exists(['id' => $id, 'status IN' => ['confirmed', 'pending']])) {
            $reg = $this->get($id);

            if ($reg->transaction_id) {
                \Braintree_Configuration::environment(Configure::read('Braintree.environment'));
                \Braintree_Configuration::merchantId(Configure::read('Braintree.merchantId'));
                \Braintree_Configuration::publicKey(Configure::read('Braintree.publicKey'));
                \Braintree_Configuration::privateKey(Configure::read('Braintree.privateKey'));

                $transaction = \Braintree_Transaction::find($reg->transaction_id);

                if ($transaction->status == 'submitted_for_settlement') {
                    $result = \Braintree_Transaction::void($reg->transaction_id);

                    return $result->success;
                }

                $result = \Braintree_Transaction::refund($reg->transaction_id);

                return $result->success;
            }

            return true;
        }

        return true;
    }

    /**
     * Returns a boolean indictating whether or not a given registration is owned
     * by a user with a given AD Username or edit key.
     *
     * @param int $id The id of the event to check.
     * @param array $authorizations The username and edit key to check against.
     * @return boolean
     */
    public function isOwnedBy($id, $authorizations)
    {
        if (isset($authorizations['edit_key'])) {
            return $this->exists(['id' => $id, 'edit_key' => $authorizations['edit_key']]);
        }

        if (isset($authorizations['ad_username'])) {
            return $this->exists(['id' => $id, 'ad_username' => $authorizations['ad_username']]);
        }

        return false;
    }
}
