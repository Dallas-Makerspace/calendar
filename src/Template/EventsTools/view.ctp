<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Events Tool'), ['action' => 'edit', $eventsTool->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Events Tool'), ['action' => 'delete', $eventsTool->id], ['confirm' => __('Are you sure you want to delete # {0}?', $eventsTool->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Events Tools'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Events Tool'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Tools'), ['controller' => 'Tools', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Tool'), ['controller' => 'Tools', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Events'), ['controller' => 'Events', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Event'), ['controller' => 'Events', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="eventsTools view large-9 medium-8 columns content">
    <h3><?= h($eventsTool->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th><?= __('Tool') ?></th>
            <td><?= $eventsTool->has('tool') ? $this->Html->link($eventsTool->tool->name, ['controller' => 'Tools', 'action' => 'view', $eventsTool->tool->id]) : '' ?></td>
        </tr>
        <tr>
            <th><?= __('Event') ?></th>
            <td><?= $eventsTool->has('event') ? $this->Html->link($eventsTool->event->name, ['controller' => 'Events', 'action' => 'view', $eventsTool->event->id]) : '' ?></td>
        </tr>
        <tr>
            <th><?= __('Id') ?></th>
            <td><?= $this->Number->format($eventsTool->id) ?></td>
        </tr>
    </table>
</div>
