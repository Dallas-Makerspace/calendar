<div class="contacts form">
    <h2>
        Edit Contact
        <?= $this->Html->link('List Contacts',
            ['action' => 'index'],
            ['class' => 'btn btn-primary pull-right']
        ) ?>
    </h2>
    <?= $this->Form->create($contact) ?>
    <fieldset>
        <?= $this->Form->input('name') ?>
        <?= $this->Form->input('email') ?>
        <?= $this->Form->input('phone') ?>
        <?= $this->Form->input('w9_on_file') ?>
        <?= $this->Form->input('blacklisted') ?>
    </fieldset>
    <?= $this->Form->button(__('Save Contact'), ['class' => 'btn btn-success']) ?>
    <?= $this->Form->end() ?>
</div>
