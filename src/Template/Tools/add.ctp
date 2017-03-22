<div class="tools form">
    <h2>
        Add Tool
        <?= $this->Html->link('List Tools',
            ['action' => 'index'],
            ['class' => 'btn btn-primary pull-right']
        ) ?>
    </h2>
    <?= $this->Form->create($tool) ?>
    <fieldset>
        <?= $this->Form->input('name') ?>
    </fieldset>
    <?= $this->Form->button(__('Add New Tool'), ['class' => 'btn btn-success']) ?>
    <?= $this->Form->end() ?>
</div>
