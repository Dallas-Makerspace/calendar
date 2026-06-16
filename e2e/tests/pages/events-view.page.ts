import { expect, type Locator, type Page } from '@playwright/test';

import { RegistrationsEventPage } from './registrations-event.page';

export class EventsViewPage {
  constructor(private readonly page: Page) {}

  get title(): Locator {
    return this.page.getByRole('heading', { level: 1 });
  }

  get whenCell(): Locator {
    return this.page.locator('tr').filter({ hasText: 'When' }).locator('td').nth(1);
  }

  get registrationSection(): Locator {
    return this.page.locator('h3', { hasText: 'Registration' }).locator('..');
  }

  get successMessage(): Locator {
    return this.page.getByRole('alert');
  }

  get registerLink(): Locator {
    return this.page.getByRole('link', { name: 'Register for this Event' });
  }

  get viewRegistrationLink(): Locator {
    return this.page.getByRole('link', { name: 'View Your Registration' });
  }

  get editEventButton(): Locator {
    return this.page.getByRole('link', { name: 'Edit Event' });
  }

  get copyEventButton(): Locator {
    return this.page.getByRole('link', { name: 'Copy Event' });
  }

  get noSpacesAlert(): Locator {
    return this.page.getByText('There are no more spaces available for this event');
  }

  get registrationClosedAlert(): Locator {
    return this.page.getByText('Registration for the event is closed');
  }

  get pendingApprovalAlert(): Locator {
    return this.page.getByText('This event is pending approval');
  }

  get cancelledBanner(): Locator {
    return this.page.getByText('This event has been cancelled');
  }

  get cancellationNotice(): Locator {
    return this.page.getByText(/Cancellations for this event must be made before/);
  }

  get spacesAvailableText(): Locator {
    return this.page.locator('.spaces_avaliable');
  }

  async navigateViaUrl(eventId: number) {
    await this.page.goto(`/events/view/${eventId}`);
  }

  async expectTitle(name: string) {
    await expect(this.title).toContainText(name);
  }

  async expectShortDescription(text: string) {
    await expect(this.page.getByText(text)).toBeVisible();
  }

  async expectWhenSectionContains(text: string | RegExp) {
    await expect(this.whenCell).toContainText(text);
  }

  async expectWhenSectionLineCount(count: number) {
    const html = await this.whenCell.innerHTML();
    const lineBreaks = (html.match(/<br\s*\/?>/gi) ?? []).length;
    expect(lineBreaks + 1).toBe(count);
  }

  async expectCancellationDeadline(text: string) {
    await expect(this.cancellationNotice).toContainText(text);
  }

  async expectCost(text: string | RegExp) {
    await expect(this.registrationSection).toContainText(text);
  }

  async expectRegisterButtonVisible() {
    await expect(this.registerLink).toBeVisible();
  }

  async expectSpaceCountAvailable(open: number, total: number) {
    await expect(this.spacesAvailableText).toContainText(`${open} spaces of ${total} available`);
  }

  async expectNoCapacityCountDisplayed() {
    await expect(this.spacesAvailableText).toBeHidden();
  }

  async registerForEvent(): Promise<RegistrationsEventPage> {
    await this.registerLink.click();
    return new RegistrationsEventPage(this.page);
  }

  async openEdit(): Promise<void> {
    await this.editEventButton.click();
  }

  async openCopy(): Promise<void> {
    await this.copyEventButton.click();
  }

  async openRegisteredAttendeesTab() {
    await this.page.getByRole('tab', { name: /Registrations/ }).click();
  }

  async approveRegistration(name: string) {
    const row = this.page.getByRole('row').filter({ hasText: name });
    this.page.once('dialog', (dialog) => dialog.accept());
    await row.getByRole('link', { name: 'Approve' }).click();
  }

  async rejectRegistration(name: string) {
    const row = this.page.getByRole('row').filter({ hasText: name });
    this.page.once('dialog', (dialog) => dialog.accept());
    await row.getByRole('link', { name: 'Reject' }).click();
  }

  async openAttendanceTab() {
    await this.page.getByRole('tab', { name: 'Attendance' }).click();
  }

  async markAttended(name: string) {
    await this.openAttendanceTab();
    const row = this.page.getByRole('row').filter({ hasText: name });
    await row.getByLabel('Attended').check();
    await this.page.getByRole('button', { name: 'Mark Attended' }).click();
  }

  async expectAttendanceClosed() {
    await this.openAttendanceTab();
    await expect(this.page.getByText('Attendance is closed for this class')).toBeVisible();
  }

  async expectEditEventHidden() {
    await expect(this.editEventButton).toBeHidden();
  }

  async expectCancelledBanner() {
    await expect(this.cancelledBanner).toBeVisible();
  }

  async expectNoSpacesMessage() {
    await expect(this.noSpacesAlert).toBeVisible();
  }
}
