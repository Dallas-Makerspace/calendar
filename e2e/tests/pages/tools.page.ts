import { type Locator, type Page } from '@playwright/test';

import { HeaderComponent } from '../components/header.component';
import { AdminIndexPage } from './admin-index.page';

export class ToolsPage extends AdminIndexPage {
  readonly header: HeaderComponent;

  constructor(protected readonly page: Page) {
    super(page);
    this.header = new HeaderComponent(page.getByRole('navigation'));
  }

  get heading(): Locator {
    return this.page.getByRole('heading', { name: 'Tools' });
  }

  private get nameInput(): Locator {
    return this.page.getByLabel('Name');
  }

  async navigateViaMenu() {
    await this.header.openAdminLink('Tools');
  }

  async openAddForm() {
    await this.page.getByRole('link', { name: 'Add Tool' }).click();
  }

  async addTool(name: string) {
    await this.openAddForm();
    await this.nameInput.fill(name);
    await this.page.getByRole('button', { name: 'Add New Tool' }).click();
  }

  async saveTool(name: string) {
    await this.nameInput.fill(name);
    await this.page.getByRole('button', { name: 'Save Tool' }).click();
  }
}
