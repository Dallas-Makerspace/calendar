<?php
namespace App\Model\Entity;

use Cake\I18n\Time;
use Cake\ORM\Entity;

/**
 * Event Entity.
 *
 * @property int $id
 * @property string $name
 * @property string $short_description
 * @property string $long_description
 * @property string $advisories
 * @property \Cake\I18n\Time $event_start
 * @property \Cake\I18n\Time $event_end
 * @property \Cake\I18n\Time $booking_start
 * @property \Cake\I18n\Time $booking_end
 * @property float $cost
 * @property int $free_spaces
 * @property int $paid_spaces
 * @property bool $members_only
 * @property int $age_restriction
 * @property bool $attendees_require_approval
 * @property \Cake\I18n\Time $attendee_cancellation
 * @property int $class_number
 * @property bool $sponsored
 * @property string $status
 * @property int $room_id
 * @property \App\Model\Entity\Room $room
 * @property int $contact_id
 * @property \App\Model\Entity\Contact $contact
 * @property int $fulfills_prerequisite_id
 * @property \App\Model\Entity\Prerequisite $fulfills_prerequisite
 * @property int $requires_prerequisite_id
 * @property \App\Model\Entity\Prerequisite $requires_prerequisite
 * @property int $part_of_id
 * @property \App\Model\Entity\Event $part_of
 * @property int $copy_of_id
 * @property \App\Model\Entity\Event $copy_of
 * @property string $rejected_by
 * @property string $rejection_reason
 * @property string $created_by
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 * @property \App\Model\Entity\Honorarium[] $honoraria
 * @property \App\Model\Entity\Category[] $categories
 * @property \App\Model\Entity\Tool[] $tools
 */
class Event extends Entity
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

    protected function _getCancellationDays()
    {
        if ($this->event_start && $this->attendee_cancellation) {
            $startTime = Time::createFromFormat(
                'Y-m-d H:i:s',
                $this->event_start,
                'UTC'
            );
            $cancellationTime = Time::createFromFormat(
                'Y-m-d H:i:s',
                $this->attendee_cancellation,
                'UTC'
            );

            return $startTime->diffInDays($cancellationTime);
        }

        return null;
    }

    protected function _getSetupTime()
    {
        if ($this->event_start && $this->booking_start) {
            $startTime = Time::createFromFormat(
                'Y-m-d H:i:s',
                $this->event_start,
                'UTC'
            );
            $setupTime = Time::createFromFormat(
                'Y-m-d H:i:s',
                $this->booking_start,
                'UTC'
            );

            return $startTime->diffInMinutes($setupTime);
        }

        return null;
    }

    protected function _getTeardownTime()
    {
        if ($this->event_start && $this->booking_end) {
            $startTime = Time::createFromFormat(
                'Y-m-d H:i:s',
                $this->event_start,
                'UTC'
            );
            $teardownTime = Time::createFromFormat(
                'Y-m-d H:i:s',
                $this->booking_end,
                'UTC'
            );

            return $startTime->diffInMinutes($teardownTime);
        }

        return null;
    }
}
