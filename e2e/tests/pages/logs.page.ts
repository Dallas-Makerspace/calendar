import { expect, type Locator, type Page } from '@playwright/test';

import { HeaderComponent } from '../components/header.component';

export class LogsPage {
  readonly header: HeaderComponent;

  constructor(private readonly page: Page) {
    this.header = new HeaderComponent(page.getByRole('navigation'));
  }

  get heading(): Locator {
    return this.page.getByRole('heading', { name: 'Logs' });
  }

  get rows(): Locator {
    return this.page.locator('table tbody tr');
  }

  async rowCount(): Promise<number> {
    return this.rows.count();
  }

  async newestRowDateTime(): Promise<string> {
    return this.rows.first().locator('td').first().innerText();
  }

  async expectFilteredLogEntryAdded(options: {
    userName?: string;
    searchString?: string;
  }) {
    await this.navigateViaUrl();
    await this.filter(options);
    const baselineCount = await this.rowCount();
    const baselineDateTime =
      baselineCount > 0 ? await this.newestRowDateTime() : null;

    return {
      assertNewEntry: async () => {
        await expect
          .poll(async () => {
            await this.navigateViaUrl();
            await this.filter(options);
            const count = await this.rowCount();
            if (baselineDateTime === null) {
              return count > 0;
            }
            const newestDateTime = await this.newestRowDateTime();
            return count > baselineCount || newestDateTime !== baselineDateTime;
          })
          .toBe(true);
      },
      countFiltered: async () => this.countFiltered(options),
    };
  }

  async countFiltered(options: {
    startDate?: string;
    endDate?: string;
    userName?: string;
    searchString?: string;
  }): Promise<number> {
    await this.navigateViaUrl();
    await this.filter(options);
    return this.rowCount();
  }

  async navigateViaUrl() {
    await this.page.goto('/logs');
  }

  async expectTableColumnsVisible() {
    await expect(this.page.getByRole('columnheader', { name: 'Date/Time' })).toBeVisible();
    await expect(this.page.getByRole('columnheader', { name: 'User' })).toBeVisible();
    await expect(this.page.getByRole('columnheader', { name: 'Description' })).toBeVisible();
    await expect(this.page.getByRole('columnheader', { name: 'URL' })).toBeVisible();
    await expect(this.page.getByRole('columnheader', { name: 'IP', exact: true })).toBeVisible();
  }

  async expectHasEntries() {
    await expect(this.rows.first()).toBeVisible();
  }

  rowContaining(text: string): Locator {
    return this.rows.filter({ hasText: text });
  }

  async expectRowCount(count: number) {
    await expect(this.rows).toHaveCount(count);
  }

  async filter(options: {
    startDate?: string;
    endDate?: string;
    userName?: string;
    searchString?: string;
  }) {
    if (options.startDate !== undefined) {
      await this.page.locator('#start-date').fill(options.startDate);
    }
    if (options.endDate !== undefined) {
      await this.page.locator('#end-date').fill(options.endDate);
    }
    if (options.userName !== undefined) {
      await this.page.locator('#user-name').fill(options.userName);
    }
    if (options.searchString !== undefined) {
      await this.page.locator('#search-string').fill(options.searchString);
    }
    await this.page.getByRole('button', { name: 'Narrow Results' }).click();
  }

  async expectAccessDenied() {
    await expect(this.heading).toBeHidden();
  }
}
