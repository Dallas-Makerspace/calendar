<div class="events edit">
    <?= $this->Flash->render() ?>
    <?php if (in_array($event->status, ['cancelled', 'completed', 'rejected'])): ?>
        <div class="alert alert-danger" role="alert"><?= ucfirst($event->status) ?> events can no longer be edited.</div>
    <?php else: ?>
        <?= $this->Form->create($event, ['type' => 'file']) ?>
        <fieldset>
            <legend>General</legend>
            <div class="row">
                <div class="col-sm-6">
                    <?= $this->Form->input('name', [
                        'label' => 'Class or Event Title'
                    ]) ?>
                    <?= $this->Form->input('categories._ids.0', [
                        'default' => 1,
                        'inline' => true,
                        'label' => false,
                        'options' => $categories,
                        'type' => 'radio',
                        'value' => (isset($event->categories[0]->id) ? $event->categories[0]->id : null)
                    ]) ?>
                    <?= $this->Form->input('short_description', [
                        'help' => '250 characters or less. Give a general description of the event or class, think of it as a tagline.'
                    ]) ?>

                    <?php if ($unlockedEdits): ?>
                        <?= $this->Form->input('event_start', [
                            'append' => '<span class="glyphicon glyphicon-calendar"></span>',
                            'help' => 'Event start time for attendees. Do not include any needed setup time here. If you will be requesting honorarium then the start date must be at least ' . $config[3] . ' days from today. Events cannot be scheduled more than 60 days out.',
                            'type' => 'text'
                        ]) ?>
                    <?php else: ?>
                        <h5>Event Start</h5>
                        <p class="fixed-data"><?= str_replace(
                            ['AM', 'PM'],
                            ['am', 'pm'],
                            $this->Time->format(
                                $event->event_start,
                                'MMMM d, y - h:mma',
                                null, 'America/Chicago'
                            )
                        ) ?></p>
                    <?php endif; ?>

                    <?php if ($unlockedEdits): ?>
                        <?= $this->Form->input('event_end', [
                            'append' => '<span class="glyphicon glyphicon-calendar"></span>',
                            'help' => 'Event end time for attendees. Do not include any needed teardown time here.',
                            'type' => 'text'
                        ]) ?>
                    <?php else: ?>
                        <h5>Event End</h5>
                        <p class="fixed-data"><?= str_replace(
                            ['AM', 'PM'],
                            ['am', 'pm'],
                            $this->Time->format(
                                $event->event_end,
                                'MMMM d, y - h:mma',
                                null, 'America/Chicago'
                            )
                        ) ?></p>
                    <?php endif; ?>

                    <?php if ($unlockedEdits): ?>
                        <?= $this->Form->input('multipart_event', [
                            'help' => 'This is not to be used for repeating events, but for classes that require attendance to multiple class sessions. If this is a multipart event then the continued dates can be filled out below this section of the form.',
                            'type' => 'checkbox',
                            'value' => 1,
                            ($event->event_start_2 ? 'checked' : '')
                        ]) ?>
                    <?php endif; ?>
                </div>
                <div class="col-sm-6">
                    <?= $this->Form->input('long_description', [
                        'help' => 'Plaintext only. Line spacing is preserved when displaying this information on the event page and links are automatically turned into hyperlinks.',
                        'label' => 'Description',
                        'required' => true,
                        'rows' => 12
                    ]) ?>
                    <div class="row">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <div class="col-sm-4">
                                <?php if (isset($event->files) && isset($event->files[$i])): ?>
                                    <h5>File <?= $i + 1 ?></h5>
                                    <?= $this->Form->postLink('<i class="fa fa-times" aria-hidden="true"></i>',
                                        [
                                            'controller' => 'Files',
                                            'action' => 'delete',
                                            $event->files[$i]->id,
                                            $event->id,
                                            '?' => ['redirect_url' => '/events/edit/' . $event->id]
                                        ],
                                        [
                                            'block' => true,
                                            'confirm' => __('Are you sure you want to delete the file {0}?', $event->files[$i]->file),
                                            'escape' => false
                                        ]
                                    ) ?>
                                    <?= $event->files[$i]->file ?>
									<br/><em><?= ($event->files[$i]->private ? 'Private File' : 'Public File') ?></em>
                                <?php else: ?>
                                    <?= $this->Form->input('files.' . $i . '.file', [
                                        'label' => 'File ' . ($i + 1),
                                        'required' => false,
                                        'type' => 'file'
                                    ]); ?>
				                    <?= $this->Form->input('files.' . $i . '.private'); ?>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <div class="help-block">Files will be listed and available for download on the event page. Private files will only be visible to you and administrators.</div>
                    <?= $this->Form->input('optional_categories._ids', [
                        'label' => 'Categories',
                        'multiple' => true,
                        'options' => $optionalCategories,
                        'style' => 'height: 160px',
                        'type' => 'select'
                    ]) ?>
                </div>
            </div>
        </fieldset>

        <?php if ($unlockedEdits): ?>
            <fieldset data-depends-on-field="multipart-event" data-dependent-required="0">
                <legend>Continued Dates</legend>
                <div class="alert alert-info">
                    Continued dates must be in order and not overlap. The preparation and teardown times for these dates will use the same setup and teardown times that are set for this event. Description information will also be applied to these dates, with an added note that these are follow-up times for a multipart event. Attendees will only be able to register for the first event in the series and will be carried through to the last date.
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <?= $this->Form->input('event_start_2', [
                            'append' => '<span class="glyphicon glyphicon-calendar"></span>',
                            'help' => 'Event start time for attendees. Do not include any needed setup time here.',
                            'label' => 'Second Date Start',
                            'type' => 'text'
                        ]) ?>
                        <?= $this->Form->input('event_end_2', [
                            'append' => '<span class="glyphicon glyphicon-calendar"></span>',
                            'help' => 'Event end time for attendees. Do not include any needed teardown time here.',
                            'label' => 'Second Date End',
                            'type' => 'text'
                        ]) ?>
                        <?= $this->Form->input('event_start_3', [
                            'append' => '<span class="glyphicon glyphicon-calendar"></span>',
                            'help' => 'Event start time for attendees. Do not include any needed setup time here.',
                            'label' => 'Third Date Start',
                            'type' => 'text'
                        ]) ?>
                        <?= $this->Form->input('event_end_3', [
                            'append' => '<span class="glyphicon glyphicon-calendar"></span>',
                            'help' => 'Event end time for attendees. Do not include any needed teardown time here.',
                            'label' => 'Third Date End',
                            'type' => 'text'
                        ]) ?>
                    </div>
                    <div class="col-sm-6">
                        <?= $this->Form->input('event_start_4', [
                            'append' => '<span class="glyphicon glyphicon-calendar"></span>',
                            'help' => 'Event start time for attendees. Do not include any needed setup time here.',
                            'label' => 'Fourth Date Start',
                            'type' => 'text'
                        ]) ?>
                        <?= $this->Form->input('event_end_4', [
                            'append' => '<span class="glyphicon glyphicon-calendar"></span>',
                            'help' => 'Event end time for attendees. Do not include any needed teardown time here.',
                            'label' => 'Fourth Date End',
                            'type' => 'text'
                        ]) ?>
                        <?= $this->Form->input('event_start_5', [
                            'append' => '<span class="glyphicon glyphicon-calendar"></span>',
                            'help' => 'Event start time for attendees. Do not include any needed setup time here.',
                            'label' => 'Fifth Date Start',
                            'type' => 'text'
                        ]) ?>
                        <?= $this->Form->input('event_end_5', [
                            'append' => '<span class="glyphicon glyphicon-calendar"></span>',
                            'help' => 'Event end time for attendees. Do not include any needed teardown time here.',
                            'label' => 'Fifth Date End',
                            'type' => 'text'
                        ]) ?>
                    </div>
                </div>
            </fieldset>
        <?php endif; ?>

        <?php if ($continuedDates && !$unlockedEdits): ?>
            <fieldset>
                <legend>Continued Dates</legend>
                <div class="row">
                    <?php foreach ($continuedDates as $continuedDate): ?>
                        <div class="col-sm-6">
                            <h5>Date <?= $continuedDate['class_number'] ?> Start</h5>
                            <p class="fixed-data"><?= str_replace(
                                ['AM', 'PM'],
                                ['am', 'pm'],
                                $this->Time->format(
                                    $continuedDate['event_start'],
                                    'MMMM d, y - h:mma',
                                    null, 'America/Chicago'
                                )
                            )?></p>

                            <h5>Date <?= $continuedDate['class_number'] ?> End</h5>
                            <p class="fixed-data"><?= str_replace(
                                ['AM', 'PM'],
                                ['am', 'pm'],
                                $this->Time->format(
                                    $continuedDate['event_end'],
                                    'MMMM d, y - h:mma',
                                    null, 'America/Chicago'
                                )
                            )?></p>
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>
            </fieldset>
        <?php endif; ?>

        <?php if ($event->sponsored): ?>
            <fieldset>
                <legend>Contact Information</legend>
                <div class="row">
                    <div class="col-sm-4">
                        <h5>Contact Name</h5>
                        <p class="fixed-data"><?= $event->contact->name ?></p>
                    </div>
                    <div class="col-sm-4">
                        <h5>Contact Email</h5>
                        <p class="fixed-data"><?= $event->contact->email ?></p>
                    </div>
                    <div class="col-sm-4">
                        <h5>Contact Phone</h5>
                        <p class="fixed-data"><?= $event->contact->phone ?></p>
                    </div>
                </div>
            </fieldset>
        <?php endif; ?>

        <fieldset>
            <legend>Attendance</legend>
            <div class="row">
                <div class="col-sm-6">
                    <?php if ($unlockedEdits): ?>
						<?php if ($event->eventbrite_link): ?>
		                    <?= $this->Form->input('eventbrite_link', [
								'type' => 'url',
		                        'help' => 'Eventbrite event URL for taking payments off site. When using Eventbrite you will be responsible for processing any required refunds.',
		                        'placeholder' => 'https://www.eventbrite.com/...',
								'required' => true
		                    ]) ?>
						<?php endif; ?>
						
						<?php if (!$event->eventbrite_link): ?>
                        	<?= $this->Form->input('cost', [
                            	'append' => '.00',
                            	'default' => 0,
                            	'help' => 'Cost to attend this event. Leave this at $0.00 if the event is free. Attendees will be charged this amount to register for the event and any payments will be made by Dallas Makerspace to the required party.',
                            	'min' => 0,
                            	'placeholder' => 0,
                            	'prepend' => '$',
                            	'step' => 1
                       		]) ?>
						<?php endif; ?>
                    <?php else: ?>
                        <h5>Cost</h5>
                        <p class="fixed-data"><?= $event->cost > 0 ? '$' . $event->cost . '00' : 'Free' ?></p>
                    <?php endif; ?>

                    <?php if ($unlockedEdits): ?>
                        <?= $this->Form->input('free_spaces', [
                            'default' => 0,
                            'help' => 'Free spaces available for this event. A 0 denotes no limit unless this is a paid event. Mostly relevant for free events, but may be used if a paid class has limited space for observers.',
                            'placeholder' => 0
                        ]) ?>
                    <?php else: ?>
                        <h5>Free Spaces</h5>
                        <p class="fixed-data"><?= $event->free_spaces > 0 ? $event->free_spaces : 'No Limit' ?></p>
                    <?php endif; ?>

                    <?php if ($event->cost > 0 || $unlockedEdits): ?>
                        <?php if ($unlockedEdits): ?>
                            <div data-depends-on-field="cost" <?php if ($event->eventbrite_link) { echo 'class="hidden"'; } ?>>
                                <?= $this->Form->input('paid_spaces', [
                                    'data-depends-on-field' => 'cost',
                                    'default' => 0,
                                    'help' => 'Paid spaces available for this event. A 0 denotes no limit. Attendees will be charged the cost of the event for these spaces.',
                                    'placeholder' => 0
                                ]) ?>
                            </div>
                        <?php else: ?>
                            <h5>Paid Spaces</h5>
                            <p class="fixed-data"><?= $event->paid_spaces > 0 ? $event->paid_spaces : 'No Limit' ?></p>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($unlockedEdits): ?>
                        <?= $this->Form->input('attendee_cancellation', [
                            'append' => 'days',
                            'default' => 0,
                            'help' => "Last day leading up to the event where attendees are allowed to cancel and receive a refund, if applicable. If you don't want attendees to be able to cancel within three days of the event then you would enter 3 above. Attendees will be emailed a final reminder 24 hours before the cutoff time for cancelling is reached. Keep in mind, no new registrations are allowed after the cancellation cutoff time.",
                            'label' => 'Cancellation Window',
                            'min' => 0,
                            'placeholder' => 0,
                            'required' => true,
                            'type' => 'number',
                            'step' => 1
                        ]) ?>
                        <?= $this->Form->input('extend_registration', [
                            'help' => 'Allow attendees to sign up for a short period of time after the event has begun by extending registration. This does not extend registrations past the event start time if a cancellation window is set.',
                            'label' => 'Extend Registration',
                            'options' => [
								0 => 'No extended registration',
								15 => '15 minutes after start',
								20 => '20 minutes after start',
								25 => '25 minutes after start',
								30 => '30 minutes after start'
                            ],
                            'type' => 'select'
                        ]) ?>
                    <?php else: ?>
                        <h5>Cancellations Allowed Until</h5>
                        <p class="fixed-data"><?= str_replace(
                            ['AM', 'PM'],
                            ['am', 'pm'],
                            $this->Time->format(
                                $event->attendee_cancellation,
                                'MMMM d, y - h:mma',
                                null, 'America/Chicago'
                            )
                        )?></p>
                    <?php endif; ?>
                </div>
                <div class="col-sm-6">
                    <h5>Restrictions</h5>
                    <?php if ($unlockedEdits): ?>
                        <?= $this->Form->input('members_only', [
                            'label' => 'Only allow DMS members to register for this event'
                        ]) ?>
                        <?= $this->Form->input('attendees_require_approval', [
                            'help' => "Want to approve each attendee for this event?"
                        ]) ?>
                        <?= $this->Form->input('age_restriction', [
                            'help' => 'If your event requires attendees to be older than a minimum age, select it here. The event page will let attendees know that they must meet that age requirement to attend.',
                            'label' => 'Age Restriction',
                            'options' => [
                              0 => 'No age restriction',
                              13 => '13 and up',
                              16 => '16 and up',
                              18 => '18 and up',
                              21 => '21 and up'
                            ],
                            'type' => 'select'
                        ]) ?>
                        <?= $this->Form->input('fulfills_prerequisite_id', [
                            'empty' => 'Select Prerequisite',
                            'help' => 'If this event is a class which fulfills a prerequisite for future classes or for tool usage then select any that apply.'
                        ]) ?>
                        <?= $this->Form->input('requires_prerequisite_id', [
                            'empty' => 'Select Prerequisite',
                            'help' => 'If this event requires attendees to have a prerequisite fulfilled before taking this class or attending this event then select one whcih applies. Note that if a prerequisite is required then the event will be limited to DMS members only.'
                        ]) ?>
                    <?php else: ?>
                        <?php if ($event->members_only): ?>
                            <p class="fixed-data">DMS members only</p>
                        <?php endif; ?>
                        <?php if ($event->attendees_require_approval): ?>
                            <p class="fixed-data">Attendees require approval</p>
                        <?php endif; ?>
                        <p class="fixed-data">
                            Ages: <?= $event->age_restriction > 0 ? $event->age_restriction : 'All ages' ?>
                        </p>
                        <?php if (isset($event->fulfills_prerequisite)): ?>
                            <h5>Fulfills Prerequisite</h5>
                            <p class="fixed-data"><?= $event->fulfills_prerequisite->name ?></p>
                        <?php endif; ?>

                        <?php if (isset($event->requires_prerequisite)): ?>
                            <h5>Requires Prerequisite</h5>
                            <p class="fixed-data"><?= $event->requires_prerequisite->name ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Resources</legend>
            <?= $this->Form->input('advisories', [
                'label' => 'Special Considerations and Warnings',
                'help' => 'Most likely to be relevant to classes. Does this event have any special safety requirements such as close-toed shoes? List any considerations, safety requirements or warnings that attendees should know about here.'
            ]) ?>
        </fieldset>

        <fieldset>
            <legend>Facilities</legend>
            <div class="row">
                <div class="col-sm-6">
                    <?= $this->Form->input('room_id', [
                        'empty' => 'Select Room',
                        'help' => 'Rooms are available on a first-come, first-served basis. Setup and teardown times may overlap. Ignore this if the event takes place throughout the building, such as an open house.',
                        'options' => $rooms
                    ]) ?>
                    <?= $this->Form->input('tools._ids', [
                        'options' => $tools,
                        'help' => 'If this event or class requires certain tools to be available for use during then select any that apply.',
                        'style' => 'height: 150px'
                    ]) ?>
                </div>
                <div class="col-sm-6">
                    <?php if ($unlockedEdits): ?>
                        <?= $this->Form->input('booking_start', [
                            'help' => "If you require any setup time for your event it will be made available immediately preceding the event's start time.",
                            'label' => 'Setup Time',
                            'options' => [
                                0 => 'No setup time required',
                                15 => '15 minutes',
                                30 => '30 minutes',
                                45 => '45 minutes',
                                60 => '1 hour'
                            ],
                            'type' => 'select'
                        ]) ?>
                        <?= $this->Form->input('booking_end', [
                            'help' => "If you require any teardown time for your event it will be made available immediately following the event's end time.",
                            'label' => 'Teardown Time',
                            'options' => [
                                0 => 'No setup time required',
                                15 => '15 minutes',
                                30 => '30 minutes',
                                45 => '45 minutes',
                                60 => '1 hour'
                            ],
                            'type' => 'select'
                        ]) ?>
                    <?php else: ?>
                        <h5>Setup Begins At</h5>
                        <p class="fixed-data"><?= str_replace(
                            ['AM', 'PM'],
                            ['am', 'pm'],
                            $this->Time->format(
                                $event->booking_start,
                                'MMMM d, y - h:mma',
                                null, 'America/Chicago'
                            )
                        )?></p>

                        <h5>Teardown Ends At</h5>
                        <p class="fixed-data"><?= str_replace(
                            ['AM', 'PM'],
                            ['am', 'pm'],
                            $this->Time->format(
                                $event->booking_end,
                                'MMMM d, y - h:mma',
                                null, 'America/Chicago'
                            )
                        )?></p>
                    <?php endif; ?>
                </div>
            </div>
        </fieldset>

        <?php if (isset($event->honorarium)): ?>
            <fieldset>
                <legend>Honorarium</legend>
                <div class="row">
                    <div class="col-sm-6">
                        <h5>Committee</h5>
                        <p class="fixed-data"><?= $event->honorarium->committee->name ?></p>
                    </div>
                    <div class="col-sm-6">
                        <h5>Pay Contact</h5>
                        <p class="fixed-data"><?= $event->honorarium->pay_contact ? 'Yes' : 'No' ?></p>
                    </div>
                </div>
            </fieldset>
        <?php endif; ?>

        <?= $this->Form->input('class_number', ['type' => 'hidden']) ?>
        <?= $this->Form->input('redirect_url', ['type' => 'hidden', 'value' => '/events/edit/' . $event->id]) ?>
        <?= $this->Form->input('request_honorarium', ['type' => 'hidden']) ?>
        <?= $this->Form->button(__('Update Event'), [
            'class' => 'btn btn-success'
        ]) ?>
        <?= $this->Form->postLink(__('Cancel Event'),
            [
                'controller' => 'Events',
                'action' => 'cancel',
                $event->id
            ],
            [
                'block' => true,
                'class' => 'btn btn-danger',
                'confirm' => __('Are you sure you want to cancel this event? That action CAN NOT be undone.'),
            ]
        ) ?>
        <?= $this->Form->end() ?>
        <?= $this->fetch('postLink'); ?>
    <?php endif; ?>
</div>
