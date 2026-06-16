import { expect, type Locator, type Page } from '@playwright/test';

import { HeaderComponent } from '../components/header.component';
import { AdminIndexPage } from './admin-index.page';

export class CategoriesPage extends AdminIndexPage {
  readonly header: HeaderComponent;

  constructor(protected readonly page: Page) {
    super(page);
    this.header = new HeaderComponent(page.getByRole('navigation'));
  }

  get heading(): Locator {
    return this.page.getByRole('heading', { name: 'Categories' });
  }

  private get nameInput(): Locator {
    return this.page.getByLabel('Name');
  }

  async navigateViaMenu() {
    await this.header.openAdminLink('Categories');
  }

  async navigateViaUrl() {
    await this.page.goto('/categories');
  }

  async openAddForm() {
    await this.page.getByRole('link', { name: 'Add Category' }).click();
  }

  async addCategory(name: string) {
    await this.openAddForm();
    await this.nameInput.fill(name);
    await this.page.getByRole('button', { name: 'Add New Category' }).click();
  }

  async saveCategory(name: string) {
    await this.nameInput.fill(name);
    await this.page.getByRole('button', { name: 'Save Category' }).click();
  }

  async expectTypeCategoriesHidden() {
    await expect(this.row('Class')).toBeHidden();
    await expect(this.row('Event')).toBeHidden();
  }

  async expectAccessDenied() {
    await expect(this.heading).toBeHidden();
  }
}
