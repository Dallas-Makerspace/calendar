<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Prerequisite Entity.
 *
 * @property int $id
 * @property string $name
 * @property string $ad_group
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 */
class Prerequisite extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Explicitly limited to the writable API fields: callers must not be able
     * to forge `id`, `created` or `modified` (TimestampBehavior does not
     * overwrite already-dirty fields).
     *
     * @var array
     */
    protected $_accessible = [
        'name' => true,
        'ad_group' => true,
    ];
}
