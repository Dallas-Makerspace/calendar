<?php use Cake\I18n\Time; ?>
<div class="registrations view">
    <div class="row">
        <div class="col-lg-6 col-md-8 col-sm-10 col-lg-offset-3 col-md-offset-2 col-sm-offset-1">
            <div class="page-header">
                <h1>Registration Status</h1>
            </div>

            <?= $this->Flash->render() ?>

            <h2><?= $this->Html->link($registration->event->name, [
                'controller' => 'Events',
                'action' => 'view',
                $registration->event->id
            ]) ?></h2>

            <?php if ($registration->status == 'pending'): ?>
                <div class="alert alert-info">
                    <p><strong>Your registration is still pending.</strong> The event host has been notified of your registration and you will receive an update when your request is accepted.</p>
                </div>
            <?php elseif(in_array($registration->status, ['cancelled', 'rejected'])): ?>
                <div class="alert alert-danger">
                    <p><strong>Your registration has been cancelled.</strong> A refund has been issued, if applicable.</p>
                </div>
            <?php else: ?>
                <div class="alert alert-success">
                    <p><strong>You're all set!</strong> You're confirmed for this event. For full event details, including event time and location, <?= $this->Html->link('visit the event\'s page', [
                        'controller' => 'Events',
                        'action' => 'view',
                        $registration->event->id
                    ]) ?>.</p>
                </div>
            <?php endif; ?>

            <table class="table table-striped">
                <tr>
                    <td><strong>Name</strong></td>
                    <td><?= h($registration->name) ?></td>
                </tr>
                <tr>
                    <td><strong>Email</strong></td>
                    <td><?= h($registration->email) ?></td>
                </tr>
                <tr>
                    <td><strong>Phone</strong></td>
                    <td><?= h($registration->phone) ?></td>
                </tr>
                <?php if ($registration->type == 'paid'): ?>
                    <tr>
                        <td><strong>Paid</strong></td>
                        <td>$<?= h($registration->event->cost) ?>.00</td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td><strong>Registered</strong></td>
                    <td><?= str_replace(
                        [':00', 'AM', 'PM'],
                        ['', 'am', 'pm'],
                        $this->Time->format(
                            $registration->created,
                            'E MMM d h:mma',
                            null,
                            'America/Chicago'
                        )
                    ) ?></td>
                </tr>
                <?php if ($registration->status == 'cancelled'): ?>
                    <td><strong>Cancelled</strong></td>
                    <td><?= str_replace(
                        [':00', 'AM', 'PM'],
                        ['', 'am', 'pm'],
                        $this->Time->format(
                            $registration->modified,
                            'E MMM d h:mma',
                            null,
                            'America/Chicago'
                        )
                    ) ?></td>
                <?php endif; ?>
            </table>

            <?php $now = new Time(); ?>
            <?php if (!in_array($registration->status, ['cancelled', 'rejected']) && ($now < $registration->event->attendee_cancellation || $isAdmin)): ?>
                <p>You may cancel your RSVP with the button below. If you paid to attend this event, cancelling is final and your payment will be processed for a refund.</p>
                <?php 
                    if ($registration->type == 'paid') {
                        print $this->Form->postLink('Cancel RSVP',
                            [
                                'action' => 'cancel',
                                $registration->id,
                                '?' => ['edit_key' => (isset($this->request->query['edit_key']) ? $this->request->query['edit_key'] : null)]
                            ],
                            [
                                'class' => 'btn btn-danger',
                                'confirm' => __('Are you sure you want to cancel your RSVP to this event? This CAN NOT be undone!')
                            ]
                        );
                    }
                    else {
                        print $this->Form->postLink('Cancel RSVP',
                            [
                                'action' => 'cancel',
                                $registration->id,
                                '?' => ['edit_key' => (isset($this->request->query['edit_key']) ? $this->request->query['edit_key'] : null)]
                            ],
                            [
                                'class' => 'btn btn-danger',
                                'confirm' => __('Are you sure you want to cancel your RSVP to this event?')
                            ]
                        );
                    }
                ?>
            <?php endif; ?>
        </div>
    </div>
</div>
