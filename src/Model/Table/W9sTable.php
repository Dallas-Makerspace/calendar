<?php
namespace App\Model\Table;

use App\Model\Entity\W9;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * W9s Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Contacts
 */
class W9sTable extends Table
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

        $this->table('w9s');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Josegonzalez/Upload.Upload', [
            'file' => [
                'path' => 'protected{DS}files{DS}{model}{DS}{field}{DS}'
            ]
        ]);
        $this->addBehavior('Timestamp');

        $this->belongsTo('Contacts', ['joinType' => 'INNER']);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator->provider('upload', \Josegonzalez\Upload\Validation\UploadValidation::class);

        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator->add('file', 'fileUnderFormSizeLimit', [
            'rule' => 'isUnderFormSizeLimit',
            'message' => 'This file is too large.',
            'provider' => 'upload'
        ]);

        $validator->add('file', 'fileFileUpload', [
            'rule' => 'isFileUpload',
            'message' => 'There was no file found to upload',
            'provider' => 'upload'
        ]);

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
        $rules->add($rules->existsIn(['contact_id'], 'Contacts'));

        return $rules;
    }
}
