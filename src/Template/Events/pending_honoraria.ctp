<div class="pending index">
    <h2>Pending Events Requesting Honorarium</h2>
    <p>Events which are pending can be approved or denied below. Events are automatically approved if no action is taken on them.</p>
    <table class="table table-striped">
        <thead>
            <tr>
                <th><?php echo $this->Paginator->sort('name'); ?></th>
                <th>Short Description</th>
                <th><?php echo $this->Paginator->sort('event_start'); ?></th>
                <th>Cost</th>
                <th><?php echo $this->Paginator->sort('created'); ?></th>
                <th><?php echo $this->Paginator->sort('created_by'); ?></th>
                <th>Contact</th>
                <th>Pay Contact</th>
                <th>Committee</th>
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
                    <td><?= $this->Html->link($event->created_by, ['controller' => 'contacts', 'action' => 'view', $event->created_by]) ?></td>
                    <td><?= $event->contact->name . "(" . $this->Html->link($event->contact->email,"mailto:" . $event->contact->email) . ")"?></td>
                    <td><?= $event->honorarium->pay_contact ? "yes" : "no" ?> </td>
                    <td><?= $event->honorarium->committee->name ?></td>
                    <td>
                        <?=
                        $this->Form->postLink(__('Approve'),
                            ['action' => 'approve',
                            $event->id,
                            '?' => ['redirect_url' => '/events/honoraria/pending']],
                            ['confirm' => __('Are you sure you want to approve event {0}?', $event->name)]
                        )  ?> |
                        <?= $this->Html->link(__('Reject'), [
                            'action' => 'processRejection',
                            $event->id,
                            '?' => ['redirect_url' => '/events/honoraria/pending']
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
