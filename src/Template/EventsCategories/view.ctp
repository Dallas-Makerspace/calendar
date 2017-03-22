<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Events Category'), ['action' => 'edit', $eventsCategory->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Events Category'), ['action' => 'delete', $eventsCategory->id], ['confirm' => __('Are you sure you want to delete # {0}?', $eventsCategory->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Events Categories'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Events Category'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Categories'), ['controller' => 'Categories', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Category'), ['controller' => 'Categories', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Events'), ['controller' => 'Events', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Event'), ['controller' => 'Events', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="eventsCategories view large-9 medium-8 columns content">
    <h3><?= h($eventsCategory->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th><?= __('Category') ?></th>
            <td><?= $eventsCategory->has('category') ? $this->Html->link($eventsCategory->category->name, ['controller' => 'Categories', 'action' => 'view', $eventsCategory->category->id]) : '' ?></td>
        </tr>
        <tr>
            <th><?= __('Event') ?></th>
            <td><?= $eventsCategory->has('event') ? $this->Html->link($eventsCategory->event->name, ['controller' => 'Events', 'action' => 'view', $eventsCategory->event->id]) : '' ?></td>
        </tr>
        <tr>
            <th><?= __('Id') ?></th>
            <td><?= $this->Number->format($eventsCategory->id) ?></td>
        </tr>
    </table>
</div>
