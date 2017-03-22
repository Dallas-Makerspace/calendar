<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Honorarium'), ['action' => 'edit', $honorarium->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Honorarium'), ['action' => 'delete', $honorarium->id], ['confirm' => __('Are you sure you want to delete # {0}?', $honorarium->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Honoraria'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Honorarium'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Events'), ['controller' => 'Events', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Event'), ['controller' => 'Events', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Contacts'), ['controller' => 'Contacts', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Contact'), ['controller' => 'Contacts', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Committees'), ['controller' => 'Committees', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Committee'), ['controller' => 'Committees', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="honoraria view large-9 medium-8 columns content">
    <h3><?= h($honorarium->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th><?= __('Event') ?></th>
            <td><?= $honorarium->has('event') ? $this->Html->link($honorarium->event->name, ['controller' => 'Events', 'action' => 'view', $honorarium->event->id]) : '' ?></td>
        </tr>
        <tr>
            <th><?= __('Contact') ?></th>
            <td><?= $honorarium->has('contact') ? $this->Html->link($honorarium->contact->name, ['controller' => 'Contacts', 'action' => 'view', $honorarium->contact->id]) : '' ?></td>
        </tr>
        <tr>
            <th><?= __('Committee') ?></th>
            <td><?= $honorarium->has('committee') ? $this->Html->link($honorarium->committee->name, ['controller' => 'Committees', 'action' => 'view', $honorarium->committee->id]) : '' ?></td>
        </tr>
        <tr>
            <th><?= __('Id') ?></th>
            <td><?= $this->Number->format($honorarium->id) ?></td>
        </tr>
        <tr>
            <th><?= __('Created') ?></th>
            <td><?= h($honorarium->created) ?></td>
        </tr>
        <tr>
            <th><?= __('Modified') ?></th>
            <td><?= h($honorarium->modified) ?></td>
        </tr>
        <tr>
            <th><?= __('Pay Contact') ?></th>
            <td><?= $honorarium->pay_contact ? __('Yes') : __('No'); ?></td>
        </tr>
        <tr>
            <th><?= __('Paid') ?></th>
            <td><?= $honorarium->paid ? __('Yes') : __('No'); ?></td>
        </tr>
    </table>
</div>
