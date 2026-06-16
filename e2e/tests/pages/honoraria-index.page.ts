import { expect, type Locator, type Page } from '@playwright/test';

import { HeaderComponent } from '../components/header.component';

export class HonorariaIndexPage {
  readonly header: HeaderComponent;

  constructor(private readonly page: Page) {
    this.header = new HeaderComponent(page.getByRole('navigation'));
  }

  get heading(): Locator {
    return this.page.getByRole('heading', { name: 'Honoraria' });
  }

  async navigateViaUrl() {
    await this.page.goto('/honoraria');
  }

  async expectAccessDenied() {
    await expect(this.heading).toBeHidden();
  }
}
