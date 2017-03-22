<div class="events index large-9 medium-8 columns content">
    <h3><?= __('Events') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th><?= $this->Paginator->sort('id') ?></th>
                <th><?= $this->Paginator->sort('name') ?></th>
                <th><?= $this->Paginator->sort('short_description') ?></th>
                <th><?= $this->Paginator->sort('event_start') ?></th>
                <th><?= $this->Paginator->sort('event_end') ?></th>
                <th><?= $this->Paginator->sort('booking_start') ?></th>
                <th><?= $this->Paginator->sort('booking_end') ?></th>
                <th><?= $this->Paginator->sort('cost') ?></th>
                <th><?= $this->Paginator->sort('free_spaces') ?></th>
                <th><?= $this->Paginator->sort('paid_spaces') ?></th>
                <th><?= $this->Paginator->sort('members_only') ?></th>
                <th><?= $this->Paginator->sort('age_restriction') ?></th>
                <th><?= $this->Paginator->sort('attendees_require_approval') ?></th>
                <th><?= $this->Paginator->sort('attendee_cancellation') ?></th>
                <th><?= $this->Paginator->sort('class_number') ?></th>
                <th><?= $this->Paginator->sort('sponsored') ?></th>
                <th><?= $this->Paginator->sort('status') ?></th>
                <th><?= $this->Paginator->sort('room_id') ?></th>
                <th><?= $this->Paginator->sort('contact_id') ?></th>
                <th><?= $this->Paginator->sort('fulfills_prerequisite_id') ?></th>
                <th><?= $this->Paginator->sort('requires_prerequisite_id') ?></th>
                <th><?= $this->Paginator->sort('part_of_id') ?></th>
                <th><?= $this->Paginator->sort('copy_of_id') ?></th>
                <th><?= $this->Paginator->sort('rejected_by') ?></th>
                <th><?= $this->Paginator->sort('rejection_reason') ?></th>
                <th><?= $this->Paginator->sort('created_by') ?></th>
                <th><?= $this->Paginator->sort('created') ?></th>
                <th><?= $this->Paginator->sort('modified') ?></th>
                <th class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php print_r($events); ?>
            <?php foreach ($events as $event): ?>
            <tr>
                <td><?= $this->Number->format($event->id) ?></td>
                <td><?= h($event->name) ?></td>
                <td><?= h($event->short_description) ?></td>
                <td><?= h($event->event_start) ?></td>
                <td><?= h($event->event_end) ?></td>
                <td><?= h($event->booking_start) ?></td>
                <td><?= h($event->booking_end) ?></td>
                <td><?= $this->Number->format($event->cost) ?></td>
                <td><?= $this->Number->format($event->free_spaces) ?></td>
                <td><?= $this->Number->format($event->paid_spaces) ?></td>
                <td><?= h($event->members_only) ?></td>
                <td><?= $this->Number->format($event->age_restriction) ?></td>
                <td><?= h($event->attendees_require_approval) ?></td>
                <td><?= h($event->attendee_cancellation) ?></td>
                <td><?= $this->Number->format($event->class_number) ?></td>
                <td><?= h($event->sponsored) ?></td>
                <td><?= h($event->status) ?></td>
                <td><?= $event->has('room') ? $this->Html->link($event->room->name, ['controller' => 'Rooms', 'action' => 'view', $event->room->id]) : '' ?></td>
                <td><?= $event->has('contact') ? $this->Html->link($event->contact->name, ['controller' => 'Contacts', 'action' => 'view', $event->contact->id]) : '' ?></td>
                <td><?= $this->Number->format($event->fulfills_prerequisite_id) ?></td>
                <td><?= $this->Number->format($event->requires_prerequisite_id) ?></td>
                <td><?= $this->Number->format($event->part_of_id) ?></td>
                <td><?= $this->Number->format($event->copy_of_id) ?></td>
                <td><?= h($event->rejected_by) ?></td>
                <td><?= h($event->rejection_reason) ?></td>
                <td><?= h($event->created_by) ?></td>
                <td><?= h($event->created) ?></td>
                <td><?= h($event->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $event->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $event->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $event->id], ['confirm' => __('Are you sure you want to delete # {0}?', $event->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        </ul>
        <p><?= $this->Paginator->counter() ?></p>
    </div>
</div>
