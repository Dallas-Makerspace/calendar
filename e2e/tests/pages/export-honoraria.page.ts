import type { Locator, Page } from '@playwright/test';

import { HeaderComponent } from '../components/header.component';

export class ExportHonorariaPage {
  readonly header: HeaderComponent;

  constructor(private readonly page: Page) {
    this.header = new HeaderComponent(page.getByRole('navigation'));
  }

  get heading(): Locator {
    return this.page.getByRole('heading', { name: 'Export Honoraria' });
  }

  async navigateViaMenu() {
    await this.header.openFinancialsLink('Export Honoraria');
  }

  async navigateViaUrl() {
    await this.page.goto('/events/export-honoraria');
  }
}
