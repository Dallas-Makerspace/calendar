<?php

// set link tag in head
$this->Html->meta(
    'Events',
    $this->Url->build([
    	"controller" => "Events",
    	"action" => "feed",
    	"feedtype" => "rss",
   		"category" => $this->request->getParam("category"),
   		"type" => $this->request->getParam("type"),
   		"tool" => $this->request->getParam("tool"),
	], true),
    ['type' => 'rss', 'block' => 'meta']
);

// set link tag in head
$this->Html->meta(
    'Events',
    $this->Url->build([
    	"controller" => "Events",
    	"action" => "feed",
    	"feedtype" => "atom",
    	"category" => $this->request->getParam("category"),
    	"type" => $this->request->getParam("type"),
    	"tool" => $this->request->getParam("tool"),
	], true),
    ['type' => 'atom', 'block' => 'meta']
);

?>
<div class="events index">
    <?= $this->Flash->render() ?>
    <div class="text-right">
        <?= $this->Html->link('<i class="fa fa-calendar" aria-hidden="true"></i> Calendar View', [
            'action' => 'calendar'
        ], [
            'escape' => false
        ]) ?>
        <br>
        <?= $this->Html->link('<i class="fa fa-rss" aria-hidden="true"></i> RSS', [
            'action' => 'feed', 'feedtype' => "rss",
    		"category" => $this->request->getParam("category"),
    		"type" => $this->request->getParam("type"),
    		"tool" => $this->request->getParam("tool"),
        ], [
            'escape' => false
        ]) ?>
        <?= $this->Html->link('<i class="fa fa-rss" aria-hidden="true"></i> ATOM', [
            'action' => 'feed', 'feedtype' => "atom",
    		"category" => $this->request->getParam("category"),
    		"type" => $this->request->getParam("type"),
    		"tool" => $this->request->getParam("tool"),
        ], [
            'escape' => false
        ]) ?>
    </div>
    <div class="page-header">
        <div class="row">
            <div class="col-sm-7">
                <h1 style="margin-top:0">Upcoming Classes and Events</h1>
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
                                '?' => $params
                            ], [
                                'class' => 'label label-info',
                                'escape' => false
                            ]) ?>
                        <?php elseif ($key == 'type' && $value = 2): ?>
                            <?php unset($params['type']); ?>
                            <?= $this->Html->link('<i class="fa fa-filter small" aria-hidden="true"></i> Event | x', [
                                '?' => $params
                            ], [
                                'class' => 'label label-info',
                                'escape' => false
                            ]) ?>
                        <?php elseif ($key == 'category' && array_key_exists((int) $value, $categories)): ?>
                            <?php unset($params['category']); ?>
                            <?= $this->Html->link('<i class="fa fa-tag small" aria-hidden="true"></i> ' . $categories[$value] . ' | x', [
                                '?' => $params
                            ], [
                                'class' => 'label label-success',
                                'escape' => false
                            ]) ?>
                        <?php elseif ($key == 'tool' && array_key_exists((int) $value, $tools)): ?>
                            <?php unset($params['tool']); ?>
                            <?= $this->Html->link('<i class="fa fa-wrench small" aria-hidden="true"></i> ' . $tools[$value] . ' | x', [
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
        <div class="row">
            <div class="col-md-offset-8 col-md-4">
                <div class="text-right">

                    Sort By:
                    &nbsp;
                    <?= $this->Paginator->sort(
                        'Events.event_start',
                        array(
                            'asc' => 'Soonest Events <i class="glyphicon glyphicon-chevron-down"></i>',
                            'desc' => 'Latest Events <i class="glyphicon glyphicon-chevron-up"></i>',
                        ),
                        ['escape' => false, 'model' => 'Events']
                    ); ?>
                    &nbsp;
                    <?= $this->Paginator->sort(
                        'Events.created',
                        array(
                            'asc' => 'Created Recently <i class="glyphicon glyphicon-chevron-down"></i>',
                            'desc' => 'Created Long Ago <i class="glyphicon glyphicon-chevron-up"></i>',
                        ),
                        ['escape' => false]
                    ); ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($events->count()): ?>
        <div class="event-list">
            <?php $id = 0; ?>
            <?php $currentDate = null; ?>
            <?php foreach ($events as $event): ?>
                <?php
                    $eventDate = $this->Time->format(
                        $event->event_start,
                        'EEEE, MMMM d',
                        null,
                        'America/Chicago'
                    )
                ?>
                <?php if ($currentDate != $eventDate): ?>
                    <div class="date-break">
                        <?= $eventDate ?>
                    </div>
                    <?php $currentDate = $eventDate; ?>
                <?php endif; ?>
                <div class="panel event-panel panel-default">
                    <div class="panel-heading" role="tab" id="heading-<?= ++$id ?>">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-<?= $id ?>" aria-expanded="false" aria-controls="collapse-<?= $id ?>" class="collapsed">
                            <h4 class="panel-title">
                                <span class="time">
                                    <?= str_replace(
                                        ['AM', 'PM'],
                                        ['am', 'pm'],
                                        $this->Time->format(
                                            $event->event_start,
                                            'h:mma',
                                            null, 'America/Chicago'
                                        )
                                    )?>
                                </span>
                                <?= \Cake\ORM\TableRegistry::get('Events')->hasOpenSpaces($event->id) ? '' : '<strong>FULL: </strong>' ?>
                                <?= h($event->name) ?>
                                <?php $cost = $event->cost > 0 ? '$' . $event->cost . '.00' : 'Free'; ?>
                                - <?= $cost ?>
                            </h4>
                        </a>
                    </div>
                    <div id="collapse-<?= $id ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-<?= $id ?>">
                        <div class="panel-body">
                            <table class="table table-condensed">
                                <tr>
                                    <td><strong>When</strong></td>
                                    <td>
                                        <?= str_replace(
                                            [':00', 'AM', 'PM'],
                                            ['', 'am', 'pm'],
                                            $this->Time->format(
                                                $event->event_start,
                                                'E MMM d h:mma',
                                                null,
                                                'America/Chicago'
                                            )
                                        ) ?> —
                                        <?= str_replace(
                                            [':00', 'AM', 'PM'],
                                            ['', 'am', 'pm'],
                                            $this->Time->format(
                                                $event->event_end,
                                                'h:mma',
                                                null, 'America/Chicago'
                                            )
                                        )?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Where</strong></td>
                                    <td>
                                        <?php if ($event->room): ?>
                                            <?= $event->room->name ?><br/>
                                        <?php endif; ?>
                                        <?php if ($event->address): ?>
                                            <?= $event->address ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Details</strong></td>
                                    <td><?= h($event->short_description) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Cost</strong></td>
                                    <td>$<?= number_format($event->cost, 2) ?></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>
                                        <?= str_replace('"', "'", $this->Html->link('More Info and RSVP »', [
                                            'action' => 'view',
                                            $event->id
                                        ])) ?>
                                        <?php if ($canManageEvents): ?>
                                            <br/><?= str_replace('"', "'", $this->Html->link('Edit Event »', [
                                                'action' => 'edit',
                                                $event->id
                                            ])) ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning" role="alert">No classes or events match your selected criteria.</div>
    <?php endif; ?>
</div>
