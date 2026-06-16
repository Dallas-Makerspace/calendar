import { expect, type Locator, type Page } from '@playwright/test';

import { HeaderComponent } from '../components/header.component';
import { AdminIndexPage } from './admin-index.page';

export class ContactsPage extends AdminIndexPage {
  readonly header: HeaderComponent;

  constructor(protected readonly page: Page) {
    super(page);
    this.header = new HeaderComponent(page.getByRole('navigation'));
  }

  get heading(): Locator {
    return this.page.getByRole('heading', { name: 'Contacts' });
  }

  private get nameInput(): Locator {
    return this.page.getByLabel('Name');
  }

  private get emailInput(): Locator {
    return this.page.getByLabel('Email');
  }

  private get phoneInput(): Locator {
    return this.page.getByLabel('Phone');
  }

  private get w9OnFileCheckbox(): Locator {
    return this.page.getByLabel('W9 On File');
  }

  private get blacklistedCheckbox(): Locator {
    return this.page.getByLabel('Blacklisted');
  }

  get emailValidationError(): Locator {
    return this.page.locator('.form-group.has-error').filter({ has: this.emailInput });
  }

  async navigateViaMenu() {
    await this.header.openAdminLink('Contacts');
  }

  async navigateViaUrl() {
    await this.page.goto('/contacts');
  }

  async openEditForAdUsername(adUsername: string) {
    await this.navigateViaMenu();
    await this.row(adUsername).getByRole('link', { name: 'Edit' }).click();
  }

  async openContactFromIndex(adUsername: string) {
    await this.navigateViaMenu();
    await this.page
      .getByRole('row')
      .filter({ hasText: adUsername })
      .getByRole('link')
      .first()
      .click();
  }

  async openContactViewDirect(adUsername: string) {
    await this.page.goto(`/contacts/view/${adUsername}`);
  }

  async openAddForm() {
    await this.page.getByRole('link', { name: 'Add Contact' }).click();
  }

  async addContact(name: string, email: string, phone: string) {
    await this.openAddForm();
    await this.fillContactForm(name, email, phone);
    await this.submitAddForm();
  }

  async submitAddForm() {
    await this.page.getByRole('button', { name: 'Add New Contact' }).click();
  }

  async fillContactForm(name: string, email: string, phone: string) {
    await this.nameInput.fill(name);
    await this.emailInput.fill(email);
    await this.phoneInput.fill(phone);
  }

  async saveContact() {
    await this.page.getByRole('button', { name: 'Save Contact' }).click();
  }

  async setW9OnFile(checked: boolean) {
    if (checked) {
      await this.w9OnFileCheckbox.check();
    } else {
      await this.w9OnFileCheckbox.uncheck();
    }
    await this.saveContact();
  }

  async setBlacklisted(checked: boolean) {
    if (checked) {
      await this.blacklistedCheckbox.check();
    } else {
      await this.blacklistedCheckbox.uncheck();
    }
    await this.saveContact();
  }

  get contactViewHeading(): Locator {
    return this.page.getByRole('heading', { level: 1 });
  }

  get attendedEventsHeading(): Locator {
    return this.page.getByRole('heading', { name: 'Attended Events' });
  }

  get hostedEventsHeading(): Locator {
    return this.page.getByRole('heading', { name: 'Hosted Events' });
  }

  async expectW9OnFile(name: string, onFile: boolean) {
    await expect(this.row(name)).toContainText(onFile ? 'Yes' : 'No');
  }

  async expectIndexAccessDenied() {
    await expect(this.heading).toBeHidden();
  }
}
