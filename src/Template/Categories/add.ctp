<div class="categories form">
    <h2>
        Add Category
        <?= $this->Html->link('List Categories',
            ['action' => 'index'],
            ['class' => 'btn btn-primary pull-right']
        ) ?>
    </h2>
    <?= $this->Form->create($category) ?>
    <fieldset>
        <?= $this->Form->input('name') ?>
    </fieldset>
    <?= $this->Form->button(__('Add New Category'), ['class' => 'btn btn-success']) ?>
    <?= $this->Form->end() ?>
</div>
