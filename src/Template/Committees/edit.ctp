<div class="categories form">
    <h2>
        Edit Committee
        <?= $this->Html->link('List Committees',
            ['action' => 'index'],
            ['class' => 'btn btn-primary pull-right']
        ) ?>
    </h2>
    <?= $this->Form->create($committee) ?>
    <fieldset>
        <?= $this->Form->input('name') ?>
    </fieldset>
    <?= $this->Form->button(__('Save Committee'), ['class' => 'btn btn-success']) ?>
    <?= $this->Form->end() ?>
</div>
