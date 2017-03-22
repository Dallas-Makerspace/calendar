<div class="rejection index">
    <?= $this->Flash->render() ?>
    <h2>Process Event Rejection</h2>
    <p>You may leave a reason for the rejection before processing it. Your name will not be attached to the rejection description, but the event creator will be notified of the reason for the rejection.</p>

    <?= $this->Form->create($event, ['url' => ['action' => 'reject', $event->id, '?' => ['redirect_url' => $_GET['redirect_url']]]]) ?>
        <?= $this->Form->input('event.rejection_reason', ['maxlength' => 250]) ?>
        <?= $this->Form->button(__('Reject Event'), [
            'class' => 'btn btn-danger'
        ]) ?>
    <?= $this->Form->end() ?>
</div>
