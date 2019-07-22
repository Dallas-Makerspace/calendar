<div class="configurations index">
    <?= $this->Flash->render() ?>

    <h2>Calendar Super Admin</h2>
    <?= $this->Form->create($configuration); ?>
        <?= $this->Form->input('id', [
            'type' => 'hidden',
	    'value' => $configuration->id,
        ]) ?>
    <fieldset>
        <?= $this->Form->input('value', [
            'label' => $configuration->name ,
            'type' => 'checkbox',
	    'value' => 1,
	    'required' => false,
        ]) ?>
        <?= $this->Form->input('Honoraria.message', [
            'label' => 'Message to be displayed',
	    'value' => $message
        ]) ?>
    </fieldset>
    <?= $this->Form->button(__('Save Configuration Value'), ['class' => 'btn btn-success']) ?>
    <?= $this->Form->end() ?>

</div>
