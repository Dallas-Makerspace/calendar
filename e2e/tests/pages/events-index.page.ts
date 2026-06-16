import type { Locator, Page } from '@playwright/test';

import { HeaderComponent } from '../components/header.component';

export class EventsIndexPage {
  readonly header: HeaderComponent;

  constructor(private readonly page: Page) {
    this.header = new HeaderComponent(page.getByRole('navigation'));
  }

  get heading(): Locator {
    return this.page.getByRole('heading', { name: 'Upcoming Classes and Events' });
  }

  private get calendarViewLink(): Locator {
    return this.page.getByRole('link', { name: 'Calendar View' });
  }

  async navigateViaUrl() {
    await this.page.goto('/');
  }

  async openCalendarView() {
    await this.calendarViewLink.click();
  }
}
