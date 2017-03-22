<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('New Honorarium'), ['action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Events'), ['controller' => 'Events', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Event'), ['controller' => 'Events', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Contacts'), ['controller' => 'Contacts', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Contact'), ['controller' => 'Contacts', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Committees'), ['controller' => 'Committees', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Committee'), ['controller' => 'Committees', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="honoraria index large-9 medium-8 columns content">
    <h3><?= __('Honoraria') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th><?= $this->Paginator->sort('id') ?></th>
                <th><?= $this->Paginator->sort('event_id') ?></th>
                <th><?= $this->Paginator->sort('contact_id') ?></th>
                <th><?= $this->Paginator->sort('pay_contact') ?></th>
                <th><?= $this->Paginator->sort('committee_id') ?></th>
                <th><?= $this->Paginator->sort('paid') ?></th>
                <th><?= $this->Paginator->sort('created') ?></th>
                <th><?= $this->Paginator->sort('modified') ?></th>
                <th class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($honoraria as $honorarium): ?>
            <tr>
                <td><?= $this->Number->format($honorarium->id) ?></td>
                <td><?= $honorarium->has('event') ? $this->Html->link($honorarium->event->name, ['controller' => 'Events', 'action' => 'view', $honorarium->event->id]) : '' ?></td>
                <td><?= $honorarium->has('contact') ? $this->Html->link($honorarium->contact->name, ['controller' => 'Contacts', 'action' => 'view', $honorarium->contact->id]) : '' ?></td>
                <td><?= h($honorarium->pay_contact) ?></td>
                <td><?= $honorarium->has('committee') ? $this->Html->link($honorarium->committee->name, ['controller' => 'Committees', 'action' => 'view', $honorarium->committee->id]) : '' ?></td>
                <td><?= h($honorarium->paid) ?></td>
                <td><?= h($honorarium->created) ?></td>
                <td><?= h($honorarium->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $honorarium->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $honorarium->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $honorarium->id], ['confirm' => __('Are you sure you want to delete # {0}?', $honorarium->id)]) ?>
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
