import { expect, type Locator, type Page } from '@playwright/test';

import { HeaderComponent } from '../components/header.component';

export class MemberEventsPage {
  readonly header: HeaderComponent;

  constructor(private readonly page: Page) {
    this.header = new HeaderComponent(page.getByRole('navigation'));
  }

  get hostingHeading(): Locator {
    return this.page.getByRole('heading', { name: 'Your Hosted Classes and Events' });
  }

  get attendingHeading(): Locator {
    return this.page.getByRole('heading', { name: 'Your Upcoming Classes and Events' });
  }

  async navigateToHostingViaMenu() {
    await this.header.openHostingEvents();
  }

  async navigateToAttendingViaMenu() {
    await this.header.openAttendingEvents();
  }

  async expectHostedEvent(name: string, status?: string) {
    await expect(this.page.getByText(name)).toBeVisible();
    if (status) {
      await expect(this.page.locator(`.event-${status}`).filter({ hasText: name })).toBeVisible();
    }
  }

  async expectAttendingEvent(name: string) {
    await expect(this.page.getByText(name)).toBeVisible();
  }
}
