<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('New Events Tool'), ['action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Tools'), ['controller' => 'Tools', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Tool'), ['controller' => 'Tools', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Events'), ['controller' => 'Events', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Event'), ['controller' => 'Events', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="eventsTools index large-9 medium-8 columns content">
    <h3><?= __('Events Tools') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th><?= $this->Paginator->sort('id') ?></th>
                <th><?= $this->Paginator->sort('tool_id') ?></th>
                <th><?= $this->Paginator->sort('event_id') ?></th>
                <th class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($eventsTools as $eventsTool): ?>
            <tr>
                <td><?= $this->Number->format($eventsTool->id) ?></td>
                <td><?= $eventsTool->has('tool') ? $this->Html->link($eventsTool->tool->name, ['controller' => 'Tools', 'action' => 'view', $eventsTool->tool->id]) : '' ?></td>
                <td><?= $eventsTool->has('event') ? $this->Html->link($eventsTool->event->name, ['controller' => 'Events', 'action' => 'view', $eventsTool->event->id]) : '' ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $eventsTool->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $eventsTool->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $eventsTool->id], ['confirm' => __('Are you sure you want to delete # {0}?', $eventsTool->id)]) ?>
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
        <p><?= $this->Paginator->counter() ?></p>
    </div>
</div>
