<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Honorarium'), ['action' => 'edit', $honorarium->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Honorarium'), ['action' => 'delete', $honorarium->id], ['confirm' => __('Are you sure you want to delete # {0}?', $honorarium->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Honoraria'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Honorarium'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Contacts'), ['controller' => 'Contacts', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Contact'), ['controller' => 'Contacts', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Committees'), ['controller' => 'Committees', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Committee'), ['controller' => 'Committees', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Events'), ['controller' => 'Events', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Event'), ['controller' => 'Events', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="honoraria view large-9 medium-8 columns content">
    <h3><?= h($honorarium->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th><?= __('Contact') ?></th>
            <td><?= $honorarium->has('contact') ? $this->Html->link($honorarium->contact->name, ['controller' => 'Contacts', 'action' => 'view', $honorarium->contact->id]) : '' ?></td>
        </tr>
        <tr>
            <th><?= __('Committee') ?></th>
            <td><?= $honorarium->has('committee') ? $this->Html->link($honorarium->committee->name, ['controller' => 'Committees', 'action' => 'view', $honorarium->committee->id]) : '' ?></td>
        </tr>
        <tr>
            <th><?= __('Id') ?></th>
            <td><?= $this->Number->format($honorarium->id) ?></td>
        </tr>
        <tr>
            <th><?= __('Event Id') ?></th>
            <td><?= $this->Number->format($honorarium->event_id) ?></td>
        </tr>
        <tr>
            <th><?= __('Created') ?></th>
            <td><?= h($honorarium->created) ?></td>
        </tr>
        <tr>
            <th><?= __('Modified') ?></th>
            <td><?= h($honorarium->modified) ?></td>
        </tr>
        <tr>
            <th><?= __('Paid') ?></th>
            <td><?= $honorarium->paid ? __('Yes') : __('No'); ?></td>
        </tr>
    </table>
    <div class="related">
        <h4><?= __('Related Events') ?></h4>
        <?php if (!empty($honorarium->events)): ?>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <th><?= __('Id') ?></th>
                <th><?= __('Name') ?></th>
                <th><?= __('Short Description') ?></th>
                <th><?= __('Long Description') ?></th>
                <th><?= __('Advisories') ?></th>
                <th><?= __('Event Start') ?></th>
                <th><?= __('Event End') ?></th>
                <th><?= __('Booking Start') ?></th>
                <th><?= __('Booking End') ?></th>
                <th><?= __('Cost') ?></th>
                <th><?= __('Free Spaces') ?></th>
                <th><?= __('Paid Spaces') ?></th>
                <th><?= __('Members Only') ?></th>
                <th><?= __('Age Restriction') ?></th>
                <th><?= __('Attendees Require Approval') ?></th>
                <th><?= __('Attendee Cancellation') ?></th>
                <th><?= __('Class Number') ?></th>
                <th><?= __('Sponsored') ?></th>
                <th><?= __('Status') ?></th>
                <th><?= __('Room Id') ?></th>
                <th><?= __('Contact Id') ?></th>
                <th><?= __('Honorarium Id') ?></th>
                <th><?= __('Prerequisite Id') ?></th>
                <th><?= __('Part Of Id') ?></th>
                <th><?= __('Copy Of Id') ?></th>
                <th><?= __('Rejected By') ?></th>
                <th><?= __('Rejection Reason') ?></th>
                <th><?= __('Created') ?></th>
                <th><?= __('Modified') ?></th>
                <th class="actions"><?= __('Actions') ?></th>
            </tr>
            <?php foreach ($honorarium->events as $events): ?>
            <tr>
                <td><?= h($events->id) ?></td>
                <td><?= h($events->name) ?></td>
                <td><?= h($events->short_description) ?></td>
                <td><?= h($events->long_description) ?></td>
                <td><?= h($events->advisories) ?></td>
                <td><?= h($events->event_start) ?></td>
                <td><?= h($events->event_end) ?></td>
                <td><?= h($events->booking_start) ?></td>
                <td><?= h($events->booking_end) ?></td>
                <td><?= h($events->cost) ?></td>
                <td><?= h($events->free_spaces) ?></td>
                <td><?= h($events->paid_spaces) ?></td>
                <td><?= h($events->members_only) ?></td>
                <td><?= h($events->age_restriction) ?></td>
                <td><?= h($events->attendees_require_approval) ?></td>
                <td><?= h($events->attendee_cancellation) ?></td>
                <td><?= h($events->class_number) ?></td>
                <td><?= h($events->sponsored) ?></td>
                <td><?= h($events->status) ?></td>
                <td><?= h($events->room_id) ?></td>
                <td><?= h($events->contact_id) ?></td>
                <td><?= h($events->honorarium_id) ?></td>
                <td><?= h($events->prerequisite_id) ?></td>
                <td><?= h($events->part_of_id) ?></td>
                <td><?= h($events->copy_of_id) ?></td>
                <td><?= h($events->rejected_by) ?></td>
                <td><?= h($events->rejection_reason) ?></td>
                <td><?= h($events->created) ?></td>
                <td><?= h($events->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['controller' => 'Events', 'action' => 'view', $events->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['controller' => 'Events', 'action' => 'edit', $events->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'Events', 'action' => 'delete', $events->id], ['confirm' => __('Are you sure you want to delete # {0}?', $events->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>
