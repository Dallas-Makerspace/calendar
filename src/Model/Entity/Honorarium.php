<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Honorarium Entity.
 *
 * @property int $id
 * @property int $event_id
 * @property \App\Model\Entity\Event $event
 * @property int $contact_id
 * @property \App\Model\Entity\Contact $contact
 * @property bool $pay_contact
 * @property int $committee_id
 * @property \App\Model\Entity\Committee $committee
 * @property bool $paid
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 */
class Honorarium extends Entity
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
