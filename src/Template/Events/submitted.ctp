<div class="events submitted">
    <?= $this->Flash->render() ?>
    <div class="page-header">
        <h1>
            Your Hosted Classes and Events
        </h1>
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
                    <div class="panel-heading event-<?= $event->status ?>" role="tab" id="heading-<?= ++$id ?>">
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
                                <?= $event->name ?>
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
                                    <td><?= $event->short_description ?></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>
                                        <?= str_replace('"', "'", $this->Html->link('More Info and RSVP »', [
                                            'action' => 'view',
                                            $event->id
                                        ])) ?>
                                        <?php if (!in_array($event->status, ['completed', 'rejected'])): ?>
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
        <div class="alert alert-warning" role="alert">No submitted classes or events coming up.</div>
    <?php endif; ?>
</div>
