import { expect, type Locator, type Page } from '@playwright/test';

import { HeaderComponent } from '../components/header.component';
import { AdminIndexPage } from './admin-index.page';

export class ConfigurationsPage extends AdminIndexPage {
  readonly header: HeaderComponent;

  constructor(protected readonly page: Page) {
    super(page);
    this.header = new HeaderComponent(page.getByRole('navigation'));
  }

  get heading(): Locator {
    return this.page.getByRole('heading', { name: 'Configuration' });
  }

  private get valueInput(): Locator {
    return this.page.getByLabel(/\(Days\)$/);
  }

  async navigateViaMenu() {
    await this.header.openAdminLink('Configuration');
  }

  async expectConfigurationRows(names: readonly string[]) {
    for (const name of names) {
      await expect(this.row(name)).toBeVisible();
    }
    await expect(this.page.getByRole('row')).toHaveCount(names.length + 1);
  }

  async expectAllowHonorariaHidden() {
    await expect(this.row('Allow Honoraria')).toBeHidden();
  }

  async openEdit(name: string) {
    await this.row(name).getByRole('link', { name: 'Edit' }).click();
  }

  async saveValue(days: string) {
    await this.valueInput.fill(days);
    await this.page.getByRole('button', { name: 'Save Configuration Value' }).click();
  }

  async expectValueInIndex(name: string, days: string) {
    await expect(this.row(name)).toContainText(`${days} Days`);
  }
}
