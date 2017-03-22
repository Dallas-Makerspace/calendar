<div class="prerequisites form">
    <h2>
        Add Prerequisite
        <?= $this->Html->link('List Prerequisites',
            ['action' => 'index'],
            ['class' => 'btn btn-primary pull-right']
        ) ?>
    </h2>
    <?= $this->Form->create($prerequisite) ?>
    <fieldset>
        <?= $this->Form->input('name') ?>
        <?= $this->Form->input('ad_group') ?>
    </fieldset>
    <?= $this->Form->button(__('Add New Prerequisite'), ['class' => 'btn btn-success']) ?>
    <?= $this->Form->end() ?>
</div>
