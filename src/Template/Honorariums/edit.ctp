<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $honorarium->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $honorarium->id)]
            )
        ?></li>
        <li><?= $this->Html->link(__('List Honoraria'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List Contacts'), ['controller' => 'Contacts', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Contact'), ['controller' => 'Contacts', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Committees'), ['controller' => 'Committees', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Committee'), ['controller' => 'Committees', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Events'), ['controller' => 'Events', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Event'), ['controller' => 'Events', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="honoraria form large-9 medium-8 columns content">
    <?= $this->Form->create($honorarium) ?>
    <fieldset>
        <legend><?= __('Edit Honorarium') ?></legend>
        <?php
            echo $this->Form->input('event_id');
            echo $this->Form->input('contact_id', ['options' => $contacts, 'empty' => true]);
            echo $this->Form->input('committee_id', ['options' => $committees, 'empty' => true]);
            echo $this->Form->input('paid');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
