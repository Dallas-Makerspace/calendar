<?php
namespace App\Model\Behavior;

use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\ORM\Behavior;

class RelationalTimeBehavior extends Behavior
{
    protected $_defaultConfig = [
        'fields' => [
            'attendee_cancellation' => [
                'operation' => 'subtract',
                'measure' => 'days',
                'source' => 'event_start'
            ],
            'booking_start' => [
                'operation' => 'subtract',
                'measure' => 'minutes',
                'source' => 'event_start'
            ],
            'booking_end' => [
                'operation' => 'add',
                'measure' => 'minutes',
                'source' => 'event_end'
            ]
        ],
        'format' => 'Y-m-d H:i:s',
        'timezone' => 'UTC'
    ];

    public function convertFromOffset(\ArrayObject $data)
    {
        $config = $this->config();
        $operations = ['add' => 'add', 'subtract' => 'sub'];
        $measures = ['Days', 'Minutes'];
        foreach ($config['fields'] as $field => $options) {
            if (isset($data[$options['source']])) {
                $source = Time::createFromFormat(
                    $config['format'],
                    $data[$options['source']],
                    $config['timezone']
                );

                $measure = ucfirst($options['measure']);
                if (array_key_exists($options['operation'], $operations) && in_array($measure, $measures)) {
                    $action = $operations[$options['operation']] . $measure;
                    if (is_callable([$source, $action])) {
                        $source->$action($data[$field]);
                    }
                }

                $data[$field] = $source->format('Y-m-d H:i:s');
            }
        }
    }

    public function convertToOffset($date, $relation, $field)
    {
        $config = $this->config();
        $operation = ($field == 'booking_end' ? 'add' : 'sub');
        $measure = ($field == 'attendee_cancellation' ? 'Days' : 'Minutes');

        if (isset($date) && isset($relation)) {
            $date = Time::createFromFormat(
                'm/d/y, g:i A',
                $date,
                $config['timezone']
            );

            $relation = Time::createFromFormat(
                'm/d/y, g:i A',
                $relation,
                $config['timezone']
            );

            $interval = $relation->diff($date);

            if ($measure == 'Minutes') {
                return $interval->h * 60 + $interval->i;
            } else {
                return $interval->days;
            }
        }

        return false;
    }

    public function beforeMarshal(Event $event, \ArrayObject $data, \ArrayObject $options)
    {
        $this->convertFromOffset($data);
    }
}
