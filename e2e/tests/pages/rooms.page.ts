import { expect, type Locator, type Page } from '@playwright/test';

import { HeaderComponent } from '../components/header.component';
import { AdminIndexPage } from './admin-index.page';

export class RoomsPage extends AdminIndexPage {
  readonly header: HeaderComponent;

  constructor(protected readonly page: Page) {
    super(page);
    this.header = new HeaderComponent(page.getByRole('navigation'));
  }

  get heading(): Locator {
    return this.page.getByRole('heading', { name: 'Rooms' });
  }

  private get nameInput(): Locator {
    return this.page.getByLabel('Name');
  }

  private get exclusiveCheckbox(): Locator {
    return this.page.getByLabel('Exclusive Use - Only one event at a time in this room.');
  }

  async navigateViaMenu() {
    await this.header.openAdminLink('Rooms');
  }

  async openAddForm() {
    await this.page.getByRole('link', { name: 'Add Room' }).click();
  }

  async addRoom(name: string, exclusive = false) {
    await this.openAddForm();
    await this.nameInput.fill(name);
    if (exclusive) {
      await this.exclusiveCheckbox.check();
    } else {
      await this.exclusiveCheckbox.uncheck();
    }
    await this.page.getByRole('button', { name: 'Add New Room' }).click();
  }

  async saveRoom(name: string, exclusive: boolean) {
    await this.nameInput.fill(name);
    if (exclusive) {
      await this.exclusiveCheckbox.check();
    } else {
      await this.exclusiveCheckbox.uncheck();
    }
    await this.page.getByRole('button', { name: 'Save Room' }).click();
  }

  async expectExclusiveUse(name: string, exclusive: boolean) {
    const row = this.row(name);
    await expect(row.getByRole('cell', { name: exclusive ? 'Yes' : 'No' })).toBeVisible();
  }
}
