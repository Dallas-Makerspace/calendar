<?php use Cake\I18n\Time; ?>
<div class="registrations event">
    <div class="row">
        <div class="col-lg-6 col-md-8 col-sm-10 col-lg-offset-3 col-md-offset-2 col-sm-offset-1">
            <div class="page-header">
                <h1>Event Registration</h1>
            </div>

            <?= $this->Flash->render() ?>

            <h2><?= $this->Html->link($event->name, [
                'controller' => 'Events',
                'action' => 'view',
                $event->id
            ]) ?></h2>

            <?php if ($hasFreeSpaces || $hasPaidSpaces): ?>
                <?php if ($event->members_only && !$authUser): ?>
                    <div class="alert alert-info">
                        <p><strong>Notice!</strong> This event is for DMS Members only. If you are a DMS member please <?= $this->Html->link('log in', ['controller' => 'Users', 'action' => 'login', '?' => ['redirect' => $this->request->getAttribute("here")]]) ?> before registering for this event.</p>
                        <p>For more information on DMS memberships <?= $this->Html->link('visit the DMS website', 'https://dallasmakerspace.org/') ?>.</p>
                    </div>
                <?php elseif (!empty($event->requires_prerequisite) && !$meetsPreq): ?>
                    <div class="alert alert-warning">
                        <p><strong>Notice!</strong> This event requires completion of the <?= $event->requires_prerequisite->name ?> prerequisite. Check the calendar for classes which fulfill this prerequisite.</p>
                        <p>Have you met the <?= $event->requires_prerequisite->name ?> prerequisite? Let us know and we'll update our records accordingly.</p>
                    </div>
                <?php else: ?>
                    <?php if (!$authUser): ?>
                        <div class="alert alert-info">
                            <strong>DMS Member?</strong> Don't forget to <?= $this->Html->link('log in', ['controller' => 'Users', 'action' => 'login', '?' => ['redirect' => $this->request->getAttribute("here")]]) ?> before signing up for this event.
                        </div>
                    <?php endif; ?>

                    <?= $this->Form->create($registration, ['id' => 'registration']) ?>
                    <?= $this->Form->hidden('event_id', ['default' => $event->id]) ?>
                    <?= $this->Form->input('name', [
                        'default' => ($authUser ? $authUser['displayname'] : '')
                    ]) ?>
                    <?= $this->Form->input('email', [
                        'default' => ($authUser ? $authUser['mail'] : '')
                    ]) ?>
                    <?= $this->Form->input('phone', [
                        'default' => ($authUser ? $authUser['telephonenumber'] : ''),
                        'help' => 'DMS does not send messages to this number, but the teacher will see it and may reach out in the case of a class delay/etc.'
                    ]) ?>
                    <div style="display: none">
                        <?= $this->Form->input('send_text', ['label' => 'Receive text message alerts and updates regarding this event.']) ?>
                    </div>
                    <?php if ($event->advisories): ?>
                        <div class="alert alert-warning">
                            <h4 style="margin-top: 10px"><strong>Special Considerations and Warnings</strong></h4>
                            <p style="margin-bottom: 30px"><?= nl2br(h($event->advisories)) ?></p>
                            <?= $this->Form->input('safety_confirmation', [
                                'label' => 'I acknowledge the above considerations and warnings.',
                                'type' => 'checkbox',
                                'required' => true
                            ]) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($event->age_restriction): ?>
                        <?= $this->Form->input('age_confirmation', [
                            'label' => 'I acknowledge that I am ' . $event->age_restriction . ' years old or older.',
                            'type' => 'checkbox',
                            'required' => true
                        ]) ?>
                    <?php endif; ?>

                    <?php if (!$event->cost): ?>
                        <?= $this->Form->hidden('type', ['default' => 'free']) ?>
                    <?php else: ?>
                        <hr/>
                        <h3>Select Registration Type</h3>
                        <?php
                            $available = [];
                            if ($hasPaidSpaces) {
                                $available['paid'] = 'Paid Registration ($' . $event->cost . '.00)';
                            }
                            if ($hasFreeSpaces) {
                                $available['free'] = 'Free Registration';
                            }
                        ?>
                        <?= $this->Form->select('type',
                            $available,
                            ['id' => 'type', 'label' => 'Registration Type']
                        ) ?>
                        <p style="margin-top: 10px"><strong>Note:</strong> If an event has both paid and free options then the free option is usually for attendees who wish to observe and not take part in the event directly. Check the event details for specific informaiton.</p>

                        <?php $this->Form->unlockField('payment_method_nonce'); ?>
                        <?= $this->Form->hidden('payment_method_nonce', ['default' => null]) ?>
                        <?= $this->Form->hidden('cost', ['value' => $event->cost]) ?>
                        <div id="payment-form" data-depends-on-field="type"></div>
                    <?php endif; ?>

                    <hr/>
                    <?= $this->Form->hidden('ad_username', ['default' => ($authUser ? $authUser['samaccountname'] : null)]) ?>
                    <?= $this->Form->hidden('edit_key', ['default' => $editKey]) ?>
                    <?= $this->Form->hidden('status', ['default' => ($event->attendees_require_approval ? 'pending' : 'confirmed')]) ?>
                    <?= $this->Form->button(($event->attendees_require_approval ? 'Submit Registration for Approval' : 'Confirm Registration'), ['class' => 'btn btn-lg btn-block btn-success']) ?>
                    <?= $this->Form->end() ?>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-warning">
                    <strong>No available spaces!</strong> It looks like this event has already filled up. You can always check back later to see if any spaces become available.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (isset($clientToken)): ?>
    <script src="https://js.braintreegateway.com/js/braintree-2.27.0.min.js"></script>
    <script>
        var clientToken = '<?= $clientToken ?>';
        braintree.setup(clientToken, 'dropin', {
            container: 'payment-form',
            onError: function() {
                if ($('#type').val() === 'free') {
                    $('#registration').submit();
                }
            }
        });
    </script>
<?php endif; ?>
