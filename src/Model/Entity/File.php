<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * File Entity.
 *
 * @property int $id
 * @property string $file
 * @property string $dir
 * @property string $type
 * @property int $event_id
 * @property \App\Model\Entity\Event $event
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 */
class File extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];
}
