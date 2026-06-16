import { expect, type Locator, type Page } from '@playwright/test';

import { HeaderComponent } from '../components/header.component';

export class EventsArchivePage {
  readonly header: HeaderComponent;

  constructor(private readonly page: Page) {
    this.header = new HeaderComponent(page.getByRole('navigation'));
  }

  get heading(): Locator {
    return this.page.getByRole('heading', { name: 'Events Archive' });
  }

  async navigateViaMenu() {
    await this.header.openAdminLink('Events Archive');
  }

  async navigateViaUrl() {
    await this.page.goto('/events/all');
  }

  async expectVisible() {
    await expect(this.heading).toBeVisible();
  }
}
