<div class="rooms form">
    <h2>
        Edit Room
        <?= $this->Html->link('List Rooms',
            ['action' => 'index'],
            ['class' => 'btn btn-primary pull-right']
        ) ?>
    </h2>
    <?= $this->Form->create($room) ?>
    <fieldset>
        <?= $this->Form->input('name') ?>
        <?= $this->Form->input('exclusive', ['label' => 'Exclusive Use - Only one event at a time in this room.']) ?>
    </fieldset>
    <?= $this->Form->button(__('Save Room'), ['class' => 'btn btn-success']) ?>
    <?= $this->Form->end() ?>
</div>
