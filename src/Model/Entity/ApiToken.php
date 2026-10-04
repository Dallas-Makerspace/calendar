<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ApiToken Entity.
 *
 * @property int $id
 * @property string $name
 * @property string $token_hash
 * @property string $scopes
 * @property string|null $created_by
 * @property \Cake\I18n\Time|null $last_used_at
 * @property \Cake\I18n\Time|null $expires_at
 * @property bool $revoked
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 */
class ApiToken extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];

    /**
     * Fields that are never exposed when the entity is serialized.
     *
     * @var array
     */
    protected $_hidden = [
        'token_hash',
    ];
}
