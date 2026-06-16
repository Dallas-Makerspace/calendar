import { type Locator, type Page } from '@playwright/test';

import { HeaderComponent } from '../components/header.component';
import { AdminIndexPage } from './admin-index.page';

export class CommitteesPage extends AdminIndexPage {
  readonly header: HeaderComponent;

  constructor(protected readonly page: Page) {
    super(page);
    this.header = new HeaderComponent(page.getByRole('navigation'));
  }

  get heading(): Locator {
    return this.page.getByRole('heading', { name: 'Committees' });
  }

  private get nameInput(): Locator {
    return this.page.getByLabel('Name');
  }

  async navigateViaMenu() {
    await this.header.openAdminLink('Committees');
  }

  async openAddForm() {
    await this.page.getByRole('link', { name: 'Add Committee' }).click();
  }

  async addCommittee(name: string) {
    await this.openAddForm();
    await this.nameInput.fill(name);
    await this.page.getByRole('button', { name: 'Add New Committee' }).click();
  }

  async saveCommittee(name: string) {
    await this.nameInput.fill(name);
    await this.page.getByRole('button', { name: 'Save Committee' }).click();
  }
}
