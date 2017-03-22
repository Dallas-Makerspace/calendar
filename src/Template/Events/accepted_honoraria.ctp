<div class="pending index">
    <h2>Events with Approved Honorarium</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th><?php echo $this->Paginator->sort('name'); ?></th>
                <th>Short Description</th>
                <th><?php echo $this->Paginator->sort('event_start'); ?></th>
                <th>Cost</th>
                <th><?php echo $this->Paginator->sort('created'); ?></th>
                <th><?php echo $this->Paginator->sort('created_by'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($events as $event): ?>
                <tr>
                    <td><?= $this->Html->link(h($event->name), ['action' => 'view', $event->id]) ?></td>
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
