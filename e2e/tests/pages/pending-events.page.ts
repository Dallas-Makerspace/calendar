import { expect, type Locator, type Page } from '@playwright/test';

import { HeaderComponent } from '../components/header.component';
import { ProcessRejectionPage } from './process-rejection.page';

export class PendingEventsPage {
  readonly header: HeaderComponent;

  constructor(private readonly page: Page) {
    this.header = new HeaderComponent(page.getByRole('navigation'));
  }

  get heading(): Locator {
    return this.page.getByRole('heading', { name: 'Pending Events' });
  }

  async navigateViaMenu() {
    await this.header.openAdminLink('Pending Events');
  }

  async navigateViaUrl() {
    await this.page.goto('/events/pending');
  }

  row(name: string): Locator {
    return this.page.getByRole('row').filter({ hasText: name });
  }

  async expectEventVisible(name: string) {
    await expect(this.row(name)).toBeVisible();
  }

  async getEventId(name: string): Promise<number> {
    const href = await this.row(name).getByRole('link', { name, exact: true }).getAttribute('href');
    const match = href?.match(/\/events\/view\/(\d+)/);
    if (!match) {
      throw new Error(`Could not find event id for ${name}`);
    }
    return Number.parseInt(match[1], 10);
  }

  async approveEvent(name: string) {
    this.page.once('dialog', (dialog) => dialog.accept());
    await this.row(name).getByRole('link', { name: 'Approve', exact: true }).click();
  }

  async openRejectForm(name: string): Promise<ProcessRejectionPage> {
    await this.row(name).getByRole('link', { name: 'Reject', exact: true }).click();
    return new ProcessRejectionPage(this.page);
  }

  async expectAccessDenied() {
    await expect(this.heading).toBeHidden();
  }
}
