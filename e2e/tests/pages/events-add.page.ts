import { expect, type Locator, type Page } from '@playwright/test';

import { eventStartEnd, scheduleSession, type ScheduleSession } from '../helpers/dates';
import { HeaderComponent } from '../components/header.component';

export type EventFormOptions = {
  title: string;
  shortDescription?: string;
  longDescription?: string;
  startDaysFromNow?: number;
  durationHours?: number;
  schedule?: ScheduleSession;
  room?: string;
  category?: string;
  tool?: string;
  freeSpaces?: number;
  paidSpaces?: number;
  cancellationDays?: number;
  membersOnly?: boolean;
  attendeesRequireApproval?: boolean;
  ageRestriction?: string;
  requiresPrerequisite?: string;
  fulfillsPrerequisite?: string;
  advisories?: string;
  extendRegistration?: string;
  sponsored?: boolean;
  existingInstructor?: string;
  requestHonorarium?: boolean;
  committee?: string;
  notifyInstructorRegistrations?: boolean;
  eventbriteLink?: string;
  cost?: number;
  paidEventType?: 'paid' | 'eventbrite';
  primaryType?: 'Class' | 'Event';
  multipart?: boolean;
  continuedSessions?: ScheduleSession[];
  setupMinutes?: 0 | 15 | 30 | 45 | 60;
  teardownMinutes?: 0 | 15 | 30 | 45 | 60;
};

export class EventsAddPage {
  readonly header: HeaderComponent;

  constructor(private readonly page: Page) {
    this.header = new HeaderComponent(page.getByRole('navigation'));
  }

  get generalLegend(): Locator {
    return this.page.getByRole('group', { name: 'General' });
  }

  get submitEventButton(): Locator {
    return this.page.getByRole('button', { name: 'Submit Event' });
  }

  get successMessage(): Locator {
    return this.page.locator('.alert-success');
  }

  get honorariumLegend(): Locator {
    return this.page.getByRole('group', { name: 'Honorarium' });
  }

  private get titleInput(): Locator {
    return this.page.getByLabel('Class or Event Title');
  }

  private get shortDescriptionInput(): Locator {
    return this.page.getByLabel('Short Description');
  }

  private get longDescriptionInput(): Locator {
    return this.page.locator('#long-description');
  }

  private get eventStartInput(): Locator {
    return this.page.getByLabel('Event Start');
  }

  private get eventEndInput(): Locator {
    return this.page.getByLabel('Event End');
  }

  private get optionalCategoriesSelect(): Locator {
    return this.page.getByLabel('Categories');
  }

  private get committeeSelect(): Locator {
    return this.page.locator('#honorarium-committee-id');
  }

  private get fulfillsPrerequisiteSelect(): Locator {
    return this.page.locator('#fulfills-prerequisite-id');
  }

  private get requiresPrerequisiteSelect(): Locator {
    return this.page.locator('#requires-prerequisite-id');
  }

  private get roomSelect(): Locator {
    return this.page.locator('#room-id');
  }

  private get toolsSelect(): Locator {
    return this.page.locator('#tools-ids');
  }

  private get sponsoredEventCheckbox(): Locator {
    return this.page.getByLabel('Sponsored Event');
  }

  private get existingInstructorsSelect(): Locator {
    return this.page.getByLabel('Existing Instructors');
  }

  private get requestHonorariumCheckbox(): Locator {
    return this.page.getByLabel('Request Honorarium');
  }

  private get freeSpacesInput(): Locator {
    return this.page.getByLabel('Free Spaces');
  }

  private get paidSpacesInput(): Locator {
    return this.page.getByLabel('Paid Spaces');
  }

  private get cancellationWindowInput(): Locator {
    return this.page.getByLabel('Cancellation Window');
  }

  private get membersOnlyCheckbox(): Locator {
    return this.page.getByLabel('Only allow DMS members to register for this event');
  }

  private get attendeesRequireApprovalCheckbox(): Locator {
    return this.page.getByLabel('Attendees Require Approval');
  }

  private get ageRestrictionSelect(): Locator {
    return this.page.getByLabel('Age Restriction');
  }

  private get advisoriesInput(): Locator {
    return this.page.getByLabel('Special Considerations and Warnings');
  }

  private get extendRegistrationSelect(): Locator {
    return this.page.getByLabel('Extend Registration');
  }

  private get paidEventTypeSelect(): Locator {
    return this.page.locator('select.payment-type-select');
  }

  private get costInput(): Locator {
    return this.page.getByLabel('Cost');
  }

  private get eventbriteLinkInput(): Locator {
    return this.page.getByLabel('Eventbrite Link');
  }

  private get payInstructorSelect(): Locator {
    return this.page.getByLabel('Pay Instructor');
  }

  private get notifyInstructorRegistrationsCheckbox(): Locator {
    return this.page.getByLabel('Notify Instructor Registrations');
  }

  private get multipartEventCheckbox(): Locator {
    return this.page.getByLabel('Multipart Event');
  }

  private get setupTimeSelect(): Locator {
    return this.page.getByLabel('Setup Time');
  }

  private get teardownTimeSelect(): Locator {
    return this.page.getByLabel('Teardown Time');
  }

  private continuedStartInput(sessionNumber: number): Locator {
    return this.page.getByLabel(`${this.ordinal(sessionNumber)} Date Start`);
  }

  private continuedEndInput(sessionNumber: number): Locator {
    return this.page.getByLabel(`${this.ordinal(sessionNumber)} Date End`);
  }

  private ordinal(sessionNumber: number): string {
    const names = ['', 'First', 'Second', 'Third', 'Fourth', 'Fifth'];
    return names[sessionNumber] ?? `${sessionNumber}th`;
  }

  private setupLabel(minutes: number): string {
    if (minutes === 0) {
      return 'No setup time required';
    }
    if (minutes === 60) {
      return '1 hour';
    }
    return `${minutes} minutes`;
  }

  get eventStartHelp(): Locator {
    return this.page.getByText(/at least \d+ days from today/);
  }

  get minimumBookingLeadTime(): Locator {
    return this.page.locator('#config-mininum-booking-lead-time');
  }

  private get honorariumSection(): Locator {
    return this.page.getByRole('group', { name: 'Honorarium' });
  }

  async navigateViaMenu() {
    await this.header.goToSubmitEvent();
  }

  async navigateViaUrl(copyEventId?: number) {
    const url = copyEventId ? `/events/add?copy=${copyEventId}` : '/events/add';
    await this.page.goto(url);
  }

  async selectClassType() {
    await this.page.getByRole('radio', { name: 'Class' }).check();
  }

  async selectPrimaryType(type: 'Class' | 'Event') {
    await this.page.getByRole('radio', { name: type }).check();
  }

  async fillEventForm(options: EventFormOptions) {
    const {
      title,
      shortDescription = 'Short description for E2E test',
      longDescription = 'Long description body for E2E test',
      startDaysFromNow = 3,
      durationHours = 2,
      room = 'Common Area',
      category,
      tool,
      freeSpaces = 10,
      paidSpaces = 0,
      cancellationDays = 0,
      membersOnly,
      attendeesRequireApproval,
      ageRestriction,
      requiresPrerequisite,
      fulfillsPrerequisite,
      advisories,
      extendRegistration,
      sponsored,
      existingInstructor,
      requestHonorarium,
      committee,
      notifyInstructorRegistrations,
      eventbriteLink,
      cost,
      paidEventType,
      primaryType,
      multipart,
      continuedSessions,
      setupMinutes,
      teardownMinutes,
      schedule,
    } = options;

    await this.selectPrimaryType(primaryType ?? 'Class');
    await this.titleInput.fill(title);
    await this.shortDescriptionInput.fill(shortDescription);
    await this.longDescriptionInput.fill(longDescription);

    const primarySchedule =
      schedule ??
      ({
        daysFromNow: startDaysFromNow,
        startHour: 10,
        durationHours,
      } satisfies ScheduleSession);
    const primaryDates = scheduleSession(primarySchedule);
    await this.eventStartInput.fill(primaryDates.start);
    await this.eventEndInput.fill(primaryDates.end);
    await this.fillDates(primaryDates.start, primaryDates.end);

    const rooms = ['Common Area', 'Back Parking Lot', 'Offsite (See Event Description)'];
    const selectedRoom = rooms[new Date().getMilliseconds() % rooms.length];
    await this.roomSelect.selectOption({ label: room ?? selectedRoom });

    if (category) {
      await this.optionalCategoriesSelect.selectOption({ label: category });
    }
    if (tool) {
      await this.toolsSelect.selectOption({ label: tool });
    }

    if (paidEventType === 'eventbrite') {
      await this.paidEventTypeSelect.selectOption({ label: 'Paid (Eventbrite)' });
      if (eventbriteLink) {
        await this.eventbriteLinkInput.fill(eventbriteLink);
      }
      await this.paidSpacesInput.fill(String(paidSpaces));
    } else if (paidEventType === 'paid' || cost !== undefined) {
      await this.paidEventTypeSelect.selectOption({ label: 'Paid (DMS)' });
      await this.paidEventTypeSelect.dispatchEvent('change');
      if (cost !== undefined) {
        await expect(this.costInput).toBeVisible();
        await this.costInput.fill(String(cost));
        await this.costInput.dispatchEvent('change');
      }
      if (paidSpaces > 0) {
        await expect(this.paidSpacesInput).toBeVisible();
        await this.paidSpacesInput.fill(String(paidSpaces));
      }
    }

    await this.freeSpacesInput.fill(String(freeSpaces));
    await this.cancellationWindowInput.fill(String(cancellationDays));

    if (extendRegistration) {
      await this.extendRegistrationSelect.selectOption({ label: extendRegistration });
    }
    if (membersOnly) {
      await this.membersOnlyCheckbox.check();
    }
    if (attendeesRequireApproval) {
      await this.attendeesRequireApprovalCheckbox.check();
    }
    if (ageRestriction) {
      await this.ageRestrictionSelect.selectOption({ label: ageRestriction });
    }
    if (requiresPrerequisite) {
      await this.requiresPrerequisiteSelect.selectOption({ label: requiresPrerequisite });
    }
    if (fulfillsPrerequisite) {
      await this.fulfillsPrerequisiteSelect.selectOption({ label: fulfillsPrerequisite });
    }
    if (advisories) {
      await this.advisoriesInput.fill(advisories);
    }
    if (sponsored) {
      await this.sponsoredEventCheckbox.check();
      if (existingInstructor) {
        await this.existingInstructorsSelect.selectOption({ label: existingInstructor });
      }
    }
    if (requestHonorarium) {
      await this.requestHonorariumCheckbox.check();
      if (committee) {
        await this.committeeSelect.selectOption({ label: committee });
      }
      await this.payInstructorSelect.selectOption({ label: 'No' });
    }
    if (notifyInstructorRegistrations) {
      await this.notifyInstructorRegistrationsCheckbox.check();
    }

    if (setupMinutes !== undefined) {
      await this.setupTimeSelect.selectOption({ label: this.setupLabel(setupMinutes) });
    }
    if (teardownMinutes !== undefined) {
      await this.teardownTimeSelect.selectOption({ label: this.setupLabel(teardownMinutes) });
    }

    if (multipart) {
      await this.multipartEventCheckbox.check();
    }
    if (continuedSessions?.length) {
      for (let index = 0; index < continuedSessions.length; index += 1) {
        const sessionNumber = index + 2;
        const sessionDates = scheduleSession(continuedSessions[index]);
        await this.continuedStartInput(sessionNumber).fill(sessionDates.start);
        await this.continuedEndInput(sessionNumber).fill(sessionDates.end);
        await this.fillContinuedDates(sessionNumber, sessionDates.start, sessionDates.end);
      }
    }
  }

  async submit() {
    await this.submitEventButton.click();
  }

  async fillAndSubmit(options: EventFormOptions) {
    await this.fillEventForm(options);
    await this.submit();
  }

  async expectSuccessFlash() {
    await expect(this.successMessage).toContainText('The event has been created');
  }

  async expectErrorFlash() {
    await expect(this.page.locator('.alert-danger, .alert-error').first()).toContainText(
      'The event could not be created',
    );
  }

  async expectFieldError(text: string | RegExp) {
    await expect(this.page.getByText(text)).toBeVisible();
  }

  async expectBlacklistedAlert() {
    await expect(this.page.getByText('Your event submission privileges have been revoked')).toBeVisible();
    await expect(this.generalLegend).toBeHidden();
  }

  async expectContactErrorAlert() {
    await expect(this.page.getByText('Your account is unable to submit events')).toBeVisible();
    await expect(this.generalLegend).toBeHidden();
  }

  async expectOptionalCategoryOption(name: string) {
    await expect(this.optionalCategoriesSelect.locator('option', { hasText: name })).toHaveCount(1);
  }

  async expectSelectOption(select: Locator, name: string) {
    await expect(select.locator('option', { hasText: name })).toHaveCount(1);
  }

  async expectCommitteeOption(name: string) {
    await this.expectSelectOption(this.committeeSelect, name);
  }

  async expectPrerequisiteInDropdowns(name: string) {
    await this.expectSelectOption(this.fulfillsPrerequisiteSelect, name);
    await this.expectSelectOption(this.requiresPrerequisiteSelect, name);
  }

  async expectRoomOption(name: string) {
    await this.expectSelectOption(this.roomSelect, name);
  }

  async expectToolOption(name: string) {
    await this.expectSelectOption(this.toolsSelect, name);
  }

  async enableSponsoredEvent() {
    await this.sponsoredEventCheckbox.check();
  }

  async expectExistingInstructorOption(name: string) {
    await this.expectSelectOption(this.existingInstructorsSelect, name);
  }

  async expectRequestHonorariumEnabled(enabled: boolean) {
    if (enabled) {
      await expect(this.requestHonorariumCheckbox).toBeEnabled();
    } else {
      await expect(this.requestHonorariumCheckbox).toBeDisabled();
    }
  }

  async expectHonorariumMessage(text: string) {
    await expect(this.honorariumSection).toContainText(text);
  }

  async expectHonorariumMessageHidden(text: string) {
    await expect(this.honorariumSection).not.toContainText(text);
  }

  async expectPrefilledTitle(title: string) {
    await expect(this.titleInput).toHaveValue(title);
  }

  async expectEmptyDates() {
    await expect(this.eventStartInput).toHaveValue('');
    await expect(this.eventEndInput).toHaveValue('');
  }

  async fillDates(start: string, end: string) {
    await this.page.evaluate(
      ({ start, end }) => {
        const startEl = document.querySelector('#event-start') as HTMLInputElement | null;
        const endEl = document.querySelector('#event-end') as HTMLInputElement | null;
        if (startEl) {
          startEl.value = start;
        }
        if (endEl) {
          endEl.value = end;
        }
      },
      { start, end },
    );
  }

  async fillContinuedDates(sessionNumber: number, start: string, end: string) {
    await this.page.evaluate(
      ({ sessionNumber, start, end }) => {
        const startEl = document.querySelector(`#event-start-${sessionNumber}`) as HTMLInputElement | null;
        const endEl = document.querySelector(`#event-end-${sessionNumber}`) as HTMLInputElement | null;
        if (startEl) {
          startEl.value = start;
        }
        if (endEl) {
          endEl.value = end;
        }
      },
      { sessionNumber, start, end },
    );
  }

  async expectMembersOnlyChecked(checked: boolean) {
    if (checked) {
      await expect(this.membersOnlyCheckbox).toBeChecked();
    } else {
      await expect(this.membersOnlyCheckbox).not.toBeChecked();
    }
  }
}
