<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('List Honoraria'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List Events'), ['controller' => 'Events', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Event'), ['controller' => 'Events', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Contacts'), ['controller' => 'Contacts', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Contact'), ['controller' => 'Contacts', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Committees'), ['controller' => 'Committees', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Committee'), ['controller' => 'Committees', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="honoraria form large-9 medium-8 columns content">
    <?= $this->Form->create($honorarium) ?>
    <fieldset>
        <legend><?= __('Add Honorarium') ?></legend>
        <?php
            echo $this->Form->input('event_id', ['options' => $events]);
            echo $this->Form->input('contact_id', ['options' => $contacts, 'empty' => true]);
            echo $this->Form->input('pay_contact');
            echo $this->Form->input('committee_id', ['options' => $committees, 'empty' => true]);
            echo $this->Form->input('paid');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
