import { expect, type Locator, type Page } from '@playwright/test';

export abstract class AdminIndexPage {
  constructor(protected readonly page: Page) {}

  row(name: string): Locator {
    return this.page.getByRole('row').filter({ hasText: name });
  }

  async expectRowVisible(name: string) {
    await expect(this.row(name)).toBeVisible();
  }

  async expectRowHidden(name: string) {
    await expect(this.row(name)).toBeHidden();
  }

  async openEdit(name: string) {
    await this.row(name).getByRole('link', { name: 'Edit' }).click();
  }

  async delete(name: string) {
    this.page.once('dialog', (dialog) => dialog.accept());
    await this.row(name).getByRole('link', { name: 'Delete' }).click();
  }
}
