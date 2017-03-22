<div class="configurations form">
    <h2>
        Edit Configuration
        <?= $this->Html->link('Full Configuration',
            ['action' => 'index'],
            ['class' => 'btn btn-primary pull-right']
        ) ?>
    </h2>
    <?= $this->Form->create($configuration) ?>
    <fieldset>
        <?= $this->Form->input('value', [
            'label' => $configuration->name . ' (Days)'
        ]) ?>
    </fieldset>
    <?= $this->Form->button(__('Save Configuration Value'), ['class' => 'btn btn-success']) ?>
    <?= $this->Form->end() ?>
</div>
