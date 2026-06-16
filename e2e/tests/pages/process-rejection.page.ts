import { expect, type Locator, type Page } from '@playwright/test';

export class ProcessRejectionPage {
  constructor(private readonly page: Page) {}

  get heading(): Locator {
    return this.page.getByRole('heading', { name: 'Process Event Rejection' });
  }

  private get reasonInput(): Locator {
    return this.page.locator('#event-rejection-reason, textarea[name="event[rejection_reason]"]').first();
  }

  async rejectWithReason(reason: string) {
    await this.reasonInput.fill(reason);
    await this.page.getByRole('button', { name: 'Reject Event' }).click();
  }

  async expectVisible() {
    await expect(this.heading).toBeVisible();
  }
}
