<div class="events add">
    <?php if ($contactError): ?>
        <div class="alert alert-danger" role="alert">
            <p><strong>Your account is unable to submit events.</strong></p>
            <p>There was an issue synchronizing your AD account data with the calendar system. In order for your account data to be successfully synced your AD account information needs to include your name, email address and phone number.</p>
            <p>If your account has all of that on file you can try logging out and logging in again to see if this error disappears, otherwise contact an admin for further assistance.</p>
        </div>
    <?php elseif ($blacklisted): ?>
        <div class="alert alert-danger" role="alert">
            <p><strong>Your event submission privileges have been revoked.</strong></p>
            <p>Reach out to an administrator for more information.</p>
        </div>
    <?php else: ?>
        <?= $this->Flash->render() ?>
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
                    <?= $this->Form->input('event_start', [
                        'append' => '<span class="glyphicon glyphicon-calendar"></span>',
                        'help' => 'Event start time for attendees. Do not include any needed setup time here. If you will be requesting honorarium then the start date must be at least ' . $config[3] . ' days from today. Events cannot be scheduled more than 60 days out.',
                        'type' => 'text',
                        'autocomplete' => 'off'
                    ]) ?>
                    <?= $this->Form->input('event_end', [
                        'append' => '<span class="glyphicon glyphicon-calendar"></span>',
                        'help' => 'Event end time for attendees. Do not include any needed teardown time here.',
                        'type' => 'text',
                        'autocomplete' => 'off'
                    ]) ?>
                    <?= $this->Form->input('multipart_event', [
                        'help' => 'This is not to be used for repeating events, but for classes that require attendance to multiple class sessions. If this is a multipart event then the continued dates can be filled out below this section of the form.',
                        'type' => 'checkbox',
                        'value' => 1
                    ]) ?>
                    <?= $this->Form->input('sponsored', [
                        'help' => "Are you sponsoring this event on behalf of someone else or will somone else be the class instructor? If so, their information will be required during the submission process.",
                        'label' => 'Sponsored Event'
                    ]) ?>
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
                                <?php if ((isset($event->files) && isset($event->files[$i])) || (isset($event->files_to_copy) && isset($event->files_to_copy[$i]))): ?>
                                    <h5>File <?= $i + 1 ?></h5>
                                    <?= $event->files[$i]->file ?>
                                    <?= $this->Form->hidden('files_to_copy.' . $i . '.id', ['value' => $event->files[$i]->id]) ?>
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
                        'type' => 'text',
                        'autocomplete' => 'off'
                    ]) ?>
                    <?= $this->Form->input('event_end_2', [
                        'append' => '<span class="glyphicon glyphicon-calendar"></span>',
                        'help' => 'Event end time for attendees. Do not include any needed teardown time here.',
                        'label' => 'Second Date End',
                        'type' => 'text',
                        'autocomplete' => 'off'
                    ]) ?>
                    <?= $this->Form->input('event_start_3', [
                        'append' => '<span class="glyphicon glyphicon-calendar"></span>',
                        'help' => 'Event start time for attendees. Do not include any needed setup time here.',
                        'label' => 'Third Date Start',
                        'type' => 'text',
                        'autocomplete' => 'off'
                    ]) ?>
                    <?= $this->Form->input('event_end_3', [
                        'append' => '<span class="glyphicon glyphicon-calendar"></span>',
                        'help' => 'Event end time for attendees. Do not include any needed teardown time here.',
                        'label' => 'Third Date End',
                        'type' => 'text',
                        'autocomplete' => 'off'
                    ]) ?>
                </div>
                <div class="col-sm-6">
                    <?= $this->Form->input('event_start_4', [
                        'append' => '<span class="glyphicon glyphicon-calendar"></span>',
                        'help' => 'Event start time for attendees. Do not include any needed setup time here.',
                        'label' => 'Fourth Date Start',
                        'type' => 'text',
                        'autocomplete' => 'off'
                    ]) ?>
                    <?= $this->Form->input('event_end_4', [
                        'append' => '<span class="glyphicon glyphicon-calendar"></span>',
                        'help' => 'Event end time for attendees. Do not include any needed teardown time here.',
                        'label' => 'Fourth Date End',
                        'type' => 'text',
                        'autocomplete' => 'off'
                    ]) ?>
                    <?= $this->Form->input('event_start_5', [
                        'append' => '<span class="glyphicon glyphicon-calendar"></span>',
                        'help' => 'Event start time for attendees. Do not include any needed setup time here.',
                        'label' => 'Fifth Date Start',
                        'type' => 'text',
                        'autocomplete' => 'off'
                    ]) ?>
                    <?= $this->Form->input('event_end_5', [
                        'append' => '<span class="glyphicon glyphicon-calendar"></span>',
                        'help' => 'Event end time for attendees. Do not include any needed teardown time here.',
                        'label' => 'Fifth Date End',
                        'type' => 'text',
                        'autocomplete' => 'off'
                    ]) ?>
                </div>
            </div>
        </fieldset>

        <fieldset data-depends-on-field="sponsored" data-dependent-required="0">
            <legend>Intructor Information</legend>
            <div class="alert alert-info">
                When sponsoring an event for someone else or hosting an outside intructor for a class we require additional information about the person you're sponsoring this event for.
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <?= $this->Form->input('contact.name', [
                        'data-depends-on-field' => 'sponsored',
                        'data-dependent-required' => false,
                        'label' => 'New Instructor Name',
                        'required' => false
                    ]) ?>
                    <?= $this->Form->input('contact.email', [
                        'data-depends-on-field' => 'sponsored',
                        'data-dependent-required' => false,
                        'label' => 'New Instructor Email',
                        'required' => false
                    ]) ?>
                    <?= $this->Form->input('contact.phone', [
                        'data-depends-on-field' => 'sponsored',
                        'data-dependent-required' => false,
                        'label' => 'New Instructor Phone Number',
                        'required' => false
                    ]) ?>
                    <?= $this->Form->hidden('contact.w9_on_file', ['value' => 0]) ?>
                </div>
                <div class="col-sm-6">
                    <?= $this->Form->input('contact_id', [
                        'empty' => 'Select Existing Instructor',
                        'label' => 'Existing Instructors',
                        'options' => $contacts
                    ]) ?>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Attendance</legend>
            <div class="row">
                <div class="col-sm-6">
					<div class="form-group select">
						<label class="control-label" for="type">Paid Event?</label>
						<?= $this->Form->select('type', [
							'free' => 'Free',
							'paid' => 'Paid (DMS)',
							'eventbrite' => 'Paid (Eventbrite)'
						], [
							'class' => 'payment-type-select',
							'label' => 'Paid Event?'
						]) ?>
					</div>
					<div class="event-eventbrite hidden">
	                    <?= $this->Form->input('eventbrite_link', [
							'type' => 'url',
	                        'help' => 'Eventbrite event URL for taking payments off site. When using Eventbrite you will be responsible for processing any required refunds.',
	                        'placeholder' => 'https://www.eventbrite.com/...',
							'required' => false
	                    ]) ?>
					</div>
					<div class="event-cost hidden">
                    	<?= $this->Form->input('cost', [
                        	'append' => '.00',
                        	'default' => 0,
                        	'help' => 'Cost to attend this event. Leave this at $0.00 if the event is free. Attendees will be charged this amount to register for the event and any payments will be made by Dallas Makerspace to the required party.',
                        	'min' => 0,
                        	'placeholder' => 0,
                        	'prepend' => '$',
                        	'step' => 1
                    	]) ?>
					</div>
                    <?= $this->Form->input('free_spaces', [
                        'default' => 0,
                        'help' => 'Free spaces available for this event. A 0 denotes no limit unless this is a paid event. Mostly relevant for free events, but may be used if a paid class has limited space for observers. If using payment through Eventbrite use this to set the number of available spaces for this event.',
                        'placeholder' => 0
                    ]) ?>
                    <div data-depends-on-field="cost">
                        <?= $this->Form->input('paid_spaces', [
                            'data-depends-on-field' => 'type',
                            'default' => 0,
                            'help' => 'Paid spaces available for this event. A 0 denotes no limit. Attendees will be charged the cost of the event for these spaces.',
                            'placeholder' => 0
                        ]) ?>
                    </div>
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
                </div>
                <div class="col-sm-6">
                    <h5>Restrictions</h5>
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
                        'error' => ['escape' => false],
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
                </div>
            </div>
        </fieldset>

        <fieldset data-depends-on-field="categories-ids-1" data-hidden-on-field="categories-ids-2">
            <legend>Honorarium</legend>
            <div class="alert alert-info">
                <ul>
                    <li>When requesting honorarium remember that your event's start date must be at least <?= $config[3] ?> days away, otherwise your submission won't be accepted.</li>
                    <li>If a person (you or the event Instructor, if set) will be receiving honorarium then a W-9 is required to be on file. If a W-9 is not on file and one is not attached to this submission then the submission won't be accepted. W-9s are transmitted securely over an encrypted connection with the server.</li>
                    <li>If honorarium will only be paid out to a committee then a W-9 is not required.</li>
                    <li>Don't want to upload a W-9? You can mail it in instead, but won't be able to submit an event for honorarium until it has been received and processed.<br/>Mail W-9s to:<br/>Dallas Makerspace (W-9)<br/>P.O. Box 810663<br/>Dallas, TX 75381-0663</li>
                    <li>Need a copy of the W-9 form? <?= $this->Html->link('Download a W-9 from IRS.gov', 'https://www.irs.gov/pub/irs-pdf/fw9.pdf', ['target' => '_blank']) ?>.
                    <li><strong>Don't Forget!</strong> Class attendance must be marked within <?= $config[6] * 24 ?> hours of completion of the event.</li>
                </ul>
            </div>
	        <?php if (!empty($honorariaMessage)): ?>
		        <span style="color: red;"><?= $honorariaMessage ?></span>
	        <?php endif; ?>
            <?= $this->Form->input('request_honorarium', [
                'type' => 'checkbox',
                'value' => 1,
		        'disabled' => ($config[7] == 0) ? true : false,
            ]) ?>
            <div class="row" data-depends-on-field="request-honorarium" data-dependent-required="0">
                <div class="col-sm-4">
                    <?= $this->Form->input('honorarium.committee_id', [
                        'data-depends-on-field' => 'request-honorarium',
                        'data-dependent-required' => true,
                        'empty' => 'Select Committee'
                    ]) ?>
                </div>
                <div class="col-sm-4">
                    <?= $this->Form->input('honorarium.pay_contact', [
                        'data-depends-on-field' => 'request-honorarium',
                        'data-dependent-required' => false,
                        'label' => 'Pay Instructor',
                        'options' => [
                            1 => 'Yes',
                            0 => 'No'
                        ],
                        'type' => 'select'
                    ]) ?>
                </div>
                <div class="col-sm-4">
                    <?= $this->Form->input('contact.w9.file', [
                        'label' => 'W-9',
                        'required' => false,
                        'type' => 'file'
                    ]); ?>
                </div>
                <?= $this->Form->hidden('honorarium.paid', ['value' => 0]) ?>
            </div>
        </fieldset>

        <?php if (isset($_GET['copy'])): ?>
            <?= $this->Form->hidden('copy_of_id', ['default' => $_GET['copy']]) ?>
        <?php endif; ?>
        <?= $this->Form->hidden('class_number', ['default' => 1]) ?>
        <?= $this->Form->hidden('status', ['default' => 'pending']) ?>
        <?= $this->Form->button(__('Submit Event')) ?>
        <?= $this->Form->end() ?>

        <div id="config-mininum-booking-lead-time" class="hidden"><?= $config[4] ?></div>
        <div id="config-maximum-booking-lead-time" class="hidden"><?= $config[5] ?></div>
    <?php endif; ?>
</div>
