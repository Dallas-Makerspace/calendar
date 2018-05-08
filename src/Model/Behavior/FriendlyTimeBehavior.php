<?php
namespace App\Model\Behavior;

use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\ORM\Behavior;

/**
 * FriendlyTimeBehavior Utility class
 */
class FriendlyTimeBehavior extends Behavior
{
    protected $_defaultConfig = [
        'fields' => ['event_start', 'event_end'],
        'format' => 'm/d/Y g:i A',
        'from_timezone' => 'America/Chicago',
        'to_timezone' => 'UTC'
    ];

    /**
    * convertFrom Format.
    *
    * @param \Cake\ArrayObject data Data to convert.
    * @return void
    */
    public function convertFromFormat(\ArrayObject $data)
    {
        $config = $this->config();
        foreach ($config['fields'] as $field) {
            if (isset($data[$field])) {
                $dateTime = Time::createFromFormat(
                    $config['format'],
                    $data[$field],
                    $config['from_timezone']
                );
                $dateTime->setTimeZone(new \DateTimeZone($config['to_timezone']));
                $data[$field] = $dateTime->format('Y-m-d H:i:s');
            }
        }
    }

    /**
    * convertToFrom Format.
    *
    * @param \DateTime date Date object to convert.
    * @return \Time
    */
    public function convertToFormat($date)
    {
        $config = $this->config();
        $dateTime = Time::createFromFormat(
            'm/d/y, g:i A',
            $date,
            $config['to_timezone']
        );
        $dateTime->setTimeZone(new \DateTimeZone($config['from_timezone']));
        $date = $dateTime->format($config['format']);
        
        return $date;
    }

    public function beforeMarshal(Event $event, \ArrayObject $data, \ArrayObject $options)
    {
        $this->convertFromFormat($data);
    }
}
