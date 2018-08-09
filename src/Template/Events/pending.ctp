<div class="pending index">
    <h2>Pending Events</h2>
    <p>Events which are pending can be approved or denied below. Events are automatically approved if no action is taken on them.</p>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Short Description</th>
                <th>Event Start</th>
                <th>Cost</th>
                <th>Created</th>
                <th>Created By</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($events as $event): ?>
                <tr>
                    <td><?= $this->Html->link($event->name, ['action' => 'view', $event->id]) ?></td>
                    <td><?= h($event->short_description) ?></td>
                    <td>
                        <?= $this->Time->format(
                            $event->event_start,
                            'MM/dd/yy h:mm a',
                            null,
                            'America/Chicago'
                        ) ?>
                    </td>
                    <td><?= $this->Number->currency($event->cost) ?></td>
                    <td>
                        <?= $this->Time->format(
                            $event->created,
                            'MM/dd/yy h:mm a',
                            null,
                            'America/Chicago'
                        ) ?>
                    </td>
                    <td><?= h($event->created_by) ?></td>
                    <td>
                        <?= $this->Form->postLink(__('Approve'),
                            ['action' => 'approve', $event->id, '?' => ['redirect_url' => '/events/pending']],
                            ['confirm' => __('Are you sure you want to approve event {0}?', $event->name)]
                        ) ?> -
                        <?= $this->Html->link(__('Reject'), [
                            'action' => 'processRejection',
                            $event->id,
                            '?' => ['redirect_url' => '/events/pending']
                        ]) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        </ul>
        <p>Page <?= $this->Paginator->counter() ?></p>
    </div>
</div>
