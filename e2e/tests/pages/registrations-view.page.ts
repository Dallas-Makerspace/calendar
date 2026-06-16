import { expect, type Locator, type Page } from '@playwright/test';

export class RegistrationsViewPage {
  constructor(private readonly page: Page) {}

  get heading(): Locator {
    return this.page.getByRole('heading', { name: 'Registration Status' });
  }

  get confirmedAlert(): Locator {
    return this.page.getByText("You're all set!");
  }

  get pendingAlert(): Locator {
    return this.page.getByText('Your registration is still pending');
  }

  get cancelledAlert(): Locator {
    return this.page.getByText('Your registration has been cancelled');
  }

  get cancelRsvpButton(): Locator {
    return this.page.getByRole('link', { name: 'Cancel RSVP' });
  }

  get successMessage(): Locator {
    return this.page.getByRole('alert');
  }

  async navigateViaUrl(registrationId: number, editKey?: string) {
    const query = editKey ? `?edit_key=${editKey}` : '';
    await this.page.goto(`/registrations/view/${registrationId}${query}`);
  }

  async getRegistrationIdFromUrl(): Promise<number> {
    const match = this.page.url().match(/\/registrations\/view\/(\d+)/);
    if (!match) {
      throw new Error('Not on a registration view page');
    }
    return Number.parseInt(match[1], 10);
  }

  async getEditKeyFromUrl(): Promise<string | null> {
    const url = new URL(this.page.url());
    return url.searchParams.get('edit_key');
  }

  async cancelRsvp() {
    this.page.once('dialog', (dialog) => dialog.accept());
    await this.cancelRsvpButton.click();
  }

  async expectConfirmed() {
    await expect(this.confirmedAlert).toBeVisible();
  }

  async expectPending() {
    await expect(this.pendingAlert).toBeVisible();
  }

  async expectCancelRsvpHidden() {
    await expect(this.cancelRsvpButton).toBeHidden();
  }
}
