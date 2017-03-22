<div class="events calendar">
    <?= $this->Flash->render() ?>
    <div class="text-right">
        <?= $this->Html->link('<i class="fa fa-list-alt" aria-hidden="true"></i> List View', [
            'action' => 'index'
        ], [
            'escape' => false
        ]) ?>
    </div>
    <div class="page-header">
        <div class="row">
            <div class="col-sm-7">
                <h1 style="margin-top:0">Classes and Events Calendar</h1>
            </div>
            <div class="col-sm-5 text-right">
                <?php parse_str($_SERVER['QUERY_STRING'], $urlparams); ?>
                <div class="btn-group">
                    <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        By Type <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <li><?= $this->Html->link('Class', [
                            '?' => array_merge($urlparams, ['type' => 1])
                        ]) ?></li>
                        <li><?= $this->Html->link('Event', [
                            '?' => array_merge($urlparams, ['type' => 2])
                        ]) ?></li>
                    </ul>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        By Category <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right scrollable-menu">
                        <?php foreach ($categories as $key => $value): ?>
                            <li><?= $this->Html->link(h($value), [
                                '?' => array_merge($urlparams, ['category' => $key])
                            ]) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        By Tool <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right scrollable-menu">
                        <?php foreach ($tools as $key => $value): ?>
                            <li><?= $this->Html->link(h($value), [
                                '?' => array_merge($urlparams, ['tool' => $key])
                            ]) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php if ($urlparams): ?>
            <ul class="list-inline active-filters">
                <?php foreach ($urlparams as $key => $value): ?>
                    <li>
                        <?php $params = $urlparams; ?>
                        <?php if ($key == 'type' && $value == 1): ?>
                            <?php unset($params['type']); ?>
                            <?= $this->Html->link('<i class="fa fa-filter small" aria-hidden="true"></i> Class | x', [
                                $currentDate->year,
                                $currentDate->month,
                                '?' => $params
                            ], [
                                'class' => 'label label-info',
                                'escape' => false
                            ]) ?>
                        <?php elseif ($key == 'type' && $value = 2): ?>
                            <?php unset($params['type']); ?>
                            <?= $this->Html->link('<i class="fa fa-filter small" aria-hidden="true"></i> Event | x', [
                                $currentDate->year,
                                $currentDate->month,
                                '?' => $params
                            ], [
                                'class' => 'label label-info',
                                'escape' => false
                            ]) ?>
                        <?php elseif ($key == 'category' && array_key_exists((int) $value, $categories)): ?>
                            <?php unset($params['category']); ?>
                            <?= $this->Html->link('<i class="fa fa-tag small" aria-hidden="true"></i> ' . $categories[$value] . ' | x', [
                                $currentDate->year,
                                $currentDate->month,
                                '?' => $params
                            ], [
                                'class' => 'label label-success',
                                'escape' => false
                            ]) ?>
                        <?php elseif ($key == 'tool' && array_key_exists((int) $value, $tools)): ?>
                            <?php unset($params['tool']); ?>
                            <?= $this->Html->link('<i class="fa fa-wrench small" aria-hidden="true"></i> ' . $tools[$value] . ' | x', [
                                $currentDate->year,
                                $currentDate->month,
                                '?' => $params
                            ], [
                                'class' => 'label label-danger',
                                'escape' => false
                            ]) ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <h3 class="text-center">
        <span class="prev">
            <?php
                $lastMonth = clone $currentDate;
                $lastMonth->modify( 'first day of last month' );
            ?>
            <?= $this->Html->link('<i class="fa fa-caret-left" aria-hidden="true"></i>',
                [
                    $lastMonth->year,
                    $lastMonth->month,
                    '?' => $urlparams
                ],
                ['escape' => false]
            ) ?>
        </span>
        <?= $this->Time->format($currentDate,'MMMM y') ?>
        <span class="next">
            <?php
                $nextMonth = clone $currentDate;
                $nextMonth->modify( 'first day of next month' );
            ?>
            <?= $this->Html->link('<i class="fa fa-caret-right" aria-hidden="true"></i>',
                [
                    $nextMonth->year,
                    $nextMonth->month,
                    '?' => $urlparams
                ],
                ['escape' => false]
            ) ?>
        </span>
    </h3>
    <table class="table table-bordered table-calendar">
        <thead>
            <tr>
                <th>Sun</th>
                <th>Mon</th>
                <th>Tue</th>
                <th>Wed</th>
                <th>Thu</th>
                <th>Fri</th>
                <th>Sat</th>
            </tr>
        </thead>
        <tbody class="small">
            <?php
                $skip = 0;
                $currentDay = 1;
                $currentEvent = $events->first();
            ?>
            <?php while($currentDay <= $currentDate->daysInMonth): ?>
                <tr>
                    <?php for ($i = 0; $i < 7; $i++): ?>
                        <?php if (($currentDay == 1 && $i < $startOfMonth) || $currentDay > $currentDate->daysInMonth): ?>
                            <td class="not-in-month"></td>
                        <?php else: ?>
                            <?php
                                $class = '';
                                if ($highlight && $currentDay == $currentDate->day) {
                                    $class = ' class="highlighted"';
                                }
                            ?>
                            <td<?= $class ?>>
                                <h6 class="text-right"><?= $currentDay ?></h6>
                                <ul class="days-events list-unstyled">
                                    <?php while ($currentEvent && $this->Time->format($currentEvent->event_start, 'd', null, 'America/Chicago') == $currentDay): ?>
                                        <li>
                                            <a class="calendar-popover"
                                            data-placement="top"
                                            data-toggle="popover"
                                            title="<?= $currentEvent->name ?>"
                                            data-content="<table class='popover-description'>
                                                <tr>
                                                    <td><strong>When</strong></td>
                                                    <td>
                                                        <?= str_replace(
                                                            [':00', 'AM', 'PM'],
                                                            ['', 'am', 'pm'],
                                                            $this->Time->format(
                                                                $currentEvent->event_start,
                                                                'E MMM d h:mma',
                                                                null,
                                                                'America/Chicago'
                                                            )
                                                        ) ?> —
                                                        <?= str_replace(
                                                            [':00', 'AM', 'PM'],
                                                            ['', 'am', 'pm'],
                                                            $this->Time->format(
                                                                $currentEvent->event_end,
                                                                'h:mma',
                                                                null, 'America/Chicago'
                                                            )
                                                        )?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Where</strong></td>
                                                    <td>
                                                        <?php if ($currentEvent->room): ?>
                                                            <?= $currentEvent->room->name ?><br/>
                                                        <?php endif; ?>
                                                        <?php if ($currentEvent->address): ?>
                                                            <?= $currentEvent->address ?>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Details</strong></td>
                                                    <td><?= str_replace('"', "'", $currentEvent->short_description) ?></td>
                                                </tr>
                                                <tr>
                                                    <td></td>
                                                    <td>
                                                        <?= str_replace('"', "'", $this->Html->link('More Info and RSVP »', [
                                                            'action' => 'view',
                                                            $currentEvent->id
                                                        ])) ?>
                                                    </td>
                                                </tr>
                                            </table>">
                                                <strong>
                                                    <?= str_replace(
                                                        [':00', 'AM', 'PM'],
                                                        ['', 'am', 'pm'],
                                                        $this->Time->format(
                                                            $currentEvent->event_start,
                                                            'h:mma',
                                                            null,
                                                            'America/Chicago'
                                                        )
                                                    ) ?>
                                                </strong>
                                                <?= $currentEvent->name ?>
                                            </a>
                                        </li>
                                        <?php
                                            ++$skip;
                                            $currentEvent = $events->skip($skip)->first();
                                        ?>
                                    <?php endwhile; ?>
                                </ul>
                            </td>
                            <?php ++$currentDay; ?>
                        <?php endif; ?>
                    <?php endfor; ?>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
