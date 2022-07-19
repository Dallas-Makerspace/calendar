<?php use Cake\I18n\Time; ?>
<div class="events view">
    <?= $this->Flash->render() ?>
    <div class="page-header">
        <h1>
            <?= h($event->name) ?>

            <?php if ($canManageEvents || $event->created_by == $authUsername): ?>
                <div class="pull-right">
                    <?php if ($event->status != 'rejected'): ?>
                        <?= $this->Html->link('Edit Event', [
                            'action' => 'edit',
                            $event->id
                        ], [
                            'class' => 'btn btn-success'
                        ]) ?>
                    <?php endif; ?>
                    <?= $this->Html->link('Copy Event', [
                        'action' => 'add',
                        '?' => ['copy' => $event->id]
                    ], [
                        'class' => 'btn btn-primary'
                    ]) ?>
                </div>
            <?php endif; ?>
        </h1>
    </div>
    <?php if (in_array($event->status, ['cancelled', 'rejected'])): ?>
        <div class="alert alert-danger">
            <strong>Notice!</strong> This event has been cancelled. Registrations have been automatically cancelled and payments refunded, if applicable.
        </div>
    <?php endif; ?>
    <div class="row">
        <div class="col-sm-5">
            <table class="table table-condensed table-striped borderless">
                <tr>
                    <td><strong>When</strong></td>
                    <td>
                    <?php
                            $startdate = $this->Time->fromString($event->event_start, 'America/Chicago')->format('Ymd');
                            $enddate = $this->Time->fromString($event->event_end, 'America/Chicago')->format('Ymd');
                            if ($startdate == $enddate) {
                                $secondFormat = "h:mma";
                            } else {
                                $secondFormat = "E MMM d h:mma";
                            }
                        ?><?= str_replace(
                            [':00', 'AM', 'PM'],
                            ['', 'am', 'pm'],
                            $this->Time->format(
                                $event->event_start,
                                'E MMM d h:mma',
                                null,
                                'America/Chicago'
                            )
                        ) ?> —
                        <?= str_replace(
                            [':00', 'AM', 'PM'],
                            ['', 'am', 'pm'],
                            $this->Time->format(
                                $event->event_end,
                                $secondFormat,
                                null, 'America/Chicago'
                            )
                        )?>
                        <?php foreach ($continuedDates as $continuedDate): ?>
                            <br/><?php
                                $startdate = $this->Time->fromString($event->event_start, 'America/Chicago')->format('Ymd');
                                $enddate = $this->Time->fromString($event->event_end, 'America/Chicago')->format('Ymd');
                                if ($startdate == $enddate) {
                                    $secondFormat = "h:mma";
                                } else {
                                    $secondFormat = "E MMM d h:mma";
                                }
                            ?><?= str_replace(
                                [':00', 'AM', 'PM'],
                                ['', 'am', 'pm'],
                                $this->Time->format(
                                    $continuedDate['event_start'],
                                    'E MMM d h:mma',
                                    null,
                                    'America/Chicago'
                                )
                            ) ?> —
                            <?= str_replace(
                                [':00', 'AM', 'PM'],
                                ['', 'am', 'pm'],
                                $this->Time->format(
                                    $continuedDate['event_end'],
                                    $secondFormat,
                                    null, 'America/Chicago'
                                )
                            )?>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Where</strong></td>
                    <td>
                        <?php if ($event->room): ?>
                            <?= $event->room->name ?><br/>
                        <?php endif; ?>
                        <?php if ($event->address): ?>
                            <?= $event->address ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>What</strong></td>
                    <td><?= h($event->short_description) ?></td>
                </tr>
                <tr>
                    <td><strong>Host</strong></td>
                    <?php if ($canManageEvents): ?>
                        <td><?= $this->Html->link($event->contact->name, ['controller' => 'contacts', 'action' => 'view', $event->created_by]) ?></td>
                    <?php else: ?>
                        <td><?= $event->contact->name ?></td>
                    <?php endif; ?>
                </tr>
                <tr>
                    <td><strong>Categories</strong></td>
                    <td>
                        <ul class="list-inline">
                            <?php foreach ($event->categories as $category): ?>
                                <?php if ($category->id < 3): ?>
                                    <li><?= $this->Html->link($category->name, [
                                        'action' => 'index',
                                        '?' => ['type' => $category->id]
                                    ]) ?></li>
                                <?php else: ?>
                                    <li><?= $this->Html->link($category->name, [
                                        'action' => 'index',
                                        '?' => ['category' => $category->id]
                                    ]) ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                </tr>
                <?php if ($event->tools): ?>
                    <tr>
                        <td><strong>Tools</strong></td>
                        <td>
                            <ul class="list-inline">
                                <?php foreach ($event->tools as $tool): ?>
                                    <li><?= $this->Html->link($tool->name, [
                                        'action' => 'index',
                                        '?' => ['tool' => $tool->id]
                                    ]) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>

            <h3>Registration</h3>
            <ul class="list-unstyled">
				<?php
					$cost = $event->cost > 0 ? '$' . $event->cost . '.00' : 'Free';
					if ($event->eventbrite_link) {
						$cost = 'Paid through Eventbrite';
					}
				?>
                <li><strong>Cost:</strong> <?= $cost ?></li>
				<?php if ($event->eventbrite_link): ?>
					<li><strong>Eventbrite Registration:</strong> <a href="<?= $event->eventbrite_link ?>" target="_blank">Complete required third party registration</a></li>
					<li><strong>Considerations:</strong> This event's required fee is not processed through the DMS payment system and is completed through the host's Eventbrite account. Due to this, the host is responsible for any refunds that may be needed for this event.</li>
				<?php endif; ?>
                <?php if ($event->members_only || $event->attendees_require_approval || $event->age_restriction > 0): ?>
                    <li>
                        <strong>Restrictions:</strong>
                        <ul>
                            <?php if ($event->members_only): ?>
                                <li>DMS members only</li>
                            <?php endif; ?>
                            <?php if ($event->attendees_require_approval): ?>
                                <li>Attendees require approval from the event host</li>
                            <?php endif; ?>
                            <?php if ($event->age_restriction > 0): ?>
                                <li>Ages <?= $event->age_restriction ?>+</li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
            <p>
                <strong>Cancellations for this event must be made before <?= str_replace(
                    ['AM', 'PM'],
                    ['am', 'pm'],
                    $this->Time->format(
                        $event->attendee_cancellation,
                        'MMMM d, y — h:mma',
                        null, 'America/Chicago'
                    )
                )?>.</strong>
            </p>

            <?php
				$now = new Time();
				$cutoff = new Time($event->attendee_cancellation);
				$cutoff->addMinutes($event->extend_registration);
			?>
            <?php if ($cutoff > $now && $event->status == 'approved'): ?>
                <?php if ($hasOpenSpaces): ?>
                    <?= $this->Html->link(($hasRegistration ? 'View Your Registration' : 'Register for this Event'), [
                        'controller' => 'Registrations',
                        'action' => 'event',
                        $event->id
                    ], [
                        'class' => 'btn btn-lg btn-success',
                        'style' => 'margin-top: 30px'
                    ]) ?><?php
                    if (is_int($openSpaces)):
                        ?><p class="spaces_avaliable"><?= $openSpaces ?> spaces of <?= $totalSpaces ?> available</p><?php
                    endif;
                    ?>
                <?php else: ?>
                    <?php if ($hasRegistration): ?>
                        <?= $this->Html->link('View Your Registration', [
                            'controller' => 'Registrations',
                            'action' => 'event',
                            $event->id
                        ], [
                            'class' => 'btn btn-lg btn-success',
                            'style' => 'margin-top: 30px'
                        ]) ?>
                    <?php else: ?>
                        <div class="alert alert-info">There are no more spaces available for this event.</div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info">Registration for the event is closed.</div>
            <?php endif; ?>
        </div>
        <div class="col-sm-7">
            <h3 class="column-heading">About this Event</h3>
            <?= nl2br(preg_replace(
                "~[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]~",
                "<a href=\"\\0\" target=\"_blank\">\\0</a>",
                h($event->long_description)
            )) ?>

            <?php if ($event->advisories): ?>
                <h3>Special Considerations and Warnings</h3>
                <div class="alert alert-warning">
                    <?= nl2br(h($event->advisories)) ?>
                </div>
            <?php endif; ?>

            <?php if ($event->files): ?>
                <h3>Attachments</h3>
                <ul>
                    <?php foreach ($event->files as $file): ?>
						<?php if (!$file->private || $canManageEvents || $event->created_by == $authUsername): ?>
                        	<li><a href="/<?= str_replace("webroot/", "", $file->dir) . $file->file ?>" target="_new"><?= h($file->file) ?></a></li>
						<?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($canManageEvents || $event->created_by == $authUsername): ?>
        <div class="page-header">
            <h3>Registered Attendees</h3>
        </div>

		<div>
			<ul class="nav nav-tabs" role="tablist">
			    <li role="presentation" class="active"><a href="#registrations" aria-controls="registrations" role="tab" data-toggle="tab">Registrations <span class="badge"><?= (isset($event->registrations) && !empty($event->registration)) ? count($event->registrations) : "0"; ?></span></a></li>
			    <li role="presentation"><a href="#attendance" aria-controls="attendance" role="tab" data-toggle="tab">Attendance</a></li>
			    <?php if ($event->fulfills_prerequisite_id): ?>
					<li role="presentation"><a href="#assignments" aria-controls="assignments" role="tab" data-toggle="tab">AD Assignment</a></li>
				<?php endif; ?>
			</ul>

			<div class="tab-content">
			    <div role="tabpanel" class="tab-pane active" id="registrations">
			        <table class="table table-striped">
			            <thead>
			                <tr>
			                    <th>Name</th>
			                    <th>Email</th>
			                    <th>Phone</th>
			                    <th>Member</th>
			                    <th>Status</th>
			                    <th>Actions</th>
			                </tr>
			            </thead>
			            <tbody>
			                <?php foreach ($event->registrations as $registration): ?>
			                    <tr>
                                    <?php if ($canManageEvents && $registration->ad_username): ?>
                                        <td><?= $this->Html->link($registration->name, ['controller' => 'contacts', 'action' => 'view', $registration->ad_username]) ?></td>
                                    <?php else: ?>
                                        <td><?= h($registration->name) ?></td>
                                    <?php endif; ?>
			                        <td><?= h($registration->email) ?></td>
			                        <td><?= $registration->phone ? h($registration->phone) : 'N/A' ?></td>
			                        <td><?= $registration->ad_username ? 'Yes' : 'No' ?></td>
			                        <td><?= ucwords($registration->status) ?>
			                        <td>
			                            <?= $this->Html->link('View', [
			                                'controller' => 'Registrations',
			                                'action' => 'view',
			                                $registration->id
			                            ]) ?>
			                            <?php if ($registration->status == 'pending'): ?>
                                            -
			                                <?= $this->Form->postLink('Approve', [
			                                    'controller' => 'Registrations',
			                                    'action' => 'accept',
			                                    $registration->id
			                                ], [
			                                    'confirm' => __('Are you sure you want to approve this attendee?')
			                                ]) ?> -
			                                <?= $this->Form->postLink('Reject', [
			                                    'controller' => 'Registrations',
			                                    'action' => 'reject',
			                                    $registration->id
			                                ], [
			                                    'confirm' => __('Are you sure you want to reject this attendee?')
			                                ]) ?>
			                            <?php endif; ?>
			                        </td>
			                    </tr>
			                <?php endforeach; ?>
			            </tbody>
			        </table>
			    </div>
			    <div role="tabpanel" class="tab-pane" id="attendance">
                    <?php
                        $now = Time::now();
                        $time = new Time($event->event_start);
                    ?>

                    <?php if ($time->wasWithinLast($config[6] * 24 . ' hours')): ?>
    					<?php echo $this->Form->create($event, [
    					    'url' => ['controller' => 'Events', 'action' => 'attendance']
    					]); ?>
                    <?php endif; ?>

			        <table class="table table-striped">
			            <thead>
			                <tr>
			                    <th>Name</th>
			                    <th>Attended</th>
			                </tr>
			            </thead>
			            <tbody>
			                <?php $i = 0; foreach ($event->registrations as $registration): ?>
								<?php if ($registration->status == 'confirmed'): ?>
				                    <tr>
                                        <?php if ($canManageEvents && $registration->ad_username): ?>
                                            <td><?= $this->Html->link($registration->name, ['controller' => 'contacts', 'action' => 'view', $registration->ad_username]) ?></td>
                                        <?php else: ?>
                                            <td><?= h($registration->name) ?></td>
                                        <?php endif; ?>
				                        <td>
                                            <?php if ($time->wasWithinLast($config[6] * 24 . ' hours')): ?>
				                        	    <?php echo $this->Form->hidden('registrations.' . $i . '.id'); ?>
											    <?php echo $this->Form->checkbox('registrations.' . $i . '.attended'); ?>
                                            <?php elseif ($time > $now): ?>
                                                —
                                            <?php else: ?>
                                                <?php echo ($registration->attended ? 'Present' : 'Absent'); ?>
                                            <?php endif; ?>
				                        </td>
				                    </tr>
								<?php endif; ?>
								<?php $i+=1; endforeach; ?>
			            </tbody>
			        </table>

                    <?php if ($time->wasWithinLast($config[6] * 24 . ' hours')): ?>
					    <?php echo $this->Form->button('Mark Attended', ['class' => 'btn-success pull-right', 'type' => 'submit']); ?>

					    <?php echo $this->Form->end(); ?>
                    <?php else: ?>
                        <?php if ($time > $now): ?>
                            <p class="text-right">Attendance can be taken at the start of the class through the following 24 hours.</p>
                        <?php else: ?>
                            <p class="text-right">Attendance is closed for this class.</p>
                        <?php endif; ?>
                    <?php endif; ?>
			    </div>
				<?php if ($event->fulfills_prerequisite_id): ?>
			    	<div role="tabpanel" class="tab-pane" id="assignments">
						<div class="alert alert-warning" role="alert">
							<strong>Caution!</strong> AD Permissions can not be removed once given. Double check your assignments before submitting. If you do make a mistake the group will need to be remove manually in Active Directory.
						</div>

						<?php echo $this->Form->create($event, [
						    'url' => ['controller' => 'Events', 'action' => 'assignments']
						]); ?>

				        <table class="table table-striped">
				            <thead>
				                <tr>
				                    <th>Name</th>
				                    <th>Assign AD</th>
				                </tr>
				            </thead>
				            <tbody>
				                <?php $i = 0; foreach ($event->registrations as $registration): ?>
									<?php if ($registration->status == 'confirmed'): ?>
					                    <tr>
					                        <td><?= h($registration->name) ?></td>
											<?php if ($registration->ad_assigned): ?>
												<td><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></td>
											<?php else: ?>
					                        	<td>
													<?php echo $this->Form->hidden('registrations.' . $i . '.id'); ?>
													<?php echo $this->Form->checkbox('registrations.' . $i . '.ad_assigned'); ?>
												</td>
											<?php endif; ?>
					                    </tr>
									<?php endif; ?>
									<?php $i+=1; endforeach; ?>
				            </tbody>
				        </table>

						<?php echo $this->Form->button('Assign AD Group', ['class' => 'btn-success pull-right', 'type' => 'submit']); ?>

						<?php echo $this->Form->end(); ?>
			    	</div>
				<?php endif; ?>
			</div>
		</div>
    <?php endif; ?>
</div>
