<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Contact Entity.
 *
 * @property int $id
 * @property string $name
 * @property string $ad_username
 * @property string $email
 * @property string $phone
 * @property bool $w9
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 * @property \App\Model\Entity\Event[] $events
 * @property \App\Model\Entity\Honorarium[] $honoraria
 */
class Contact extends Entity
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
        'id' => true,
    ];

    /**
     * @return array
     */
    protected function _getContactListLabel()
    {
        return $this->_properties['name'] . ' (' . $this->_properties['email'] . ')';
    }
}
