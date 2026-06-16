import { type Locator, type Page } from '@playwright/test';

import { HeaderComponent } from '../components/header.component';
import { AdminIndexPage } from './admin-index.page';

export class PrerequisitesPage extends AdminIndexPage {
  readonly header: HeaderComponent;

  constructor(protected readonly page: Page) {
    super(page);
    this.header = new HeaderComponent(page.getByRole('navigation'));
  }

  get heading(): Locator {
    return this.page.getByRole('heading', { name: 'Prerequisites' });
  }

  private get nameInput(): Locator {
    return this.page.getByLabel('Name');
  }

  private get adGroupInput(): Locator {
    return this.page.getByLabel('Ad Group');
  }

  async navigateViaMenu() {
    await this.header.openAdminLink('Prerequisites');
  }

  async openAddForm() {
    await this.page.getByRole('link', { name: 'Add Prerequisite' }).click();
  }

  async addPrerequisite(name: string, adGroup: string) {
    await this.openAddForm();
    await this.nameInput.fill(name);
    await this.adGroupInput.fill(adGroup);
    await this.page.getByRole('button', { name: 'Add New Prerequisite' }).click();
  }

  async savePrerequisite(name: string, adGroup: string) {
    await this.nameInput.fill(name);
    await this.adGroupInput.fill(adGroup);
    await this.page.getByRole('button', { name: 'Save Prerequisite' }).click();
  }
}
