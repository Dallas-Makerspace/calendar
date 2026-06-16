import { expect, type Locator, type Page } from '@playwright/test';

import { HeaderComponent } from '../components/header.component';
import { EventsAddPage } from './events-add.page';
import { EventsIndexPage } from './events-index.page';

export class LoginPage {
  readonly header: HeaderComponent;

  constructor(private readonly page: Page) {
    this.header = new HeaderComponent(page.getByRole('navigation'));
  }

  get heading(): Locator {
    return this.page.getByRole('heading', { name: 'DMS Member Log In' });
  }

  get errorMessage(): Locator {
    return this.page.getByRole('alert');
  }

  private get usernameInput(): Locator {
    return this.page.getByLabel('Username');
  }

  private get passwordInput(): Locator {
    return this.page.getByLabel('Password');
  }

  private get loginButton(): Locator {
    return this.page.getByRole('button', { name: 'Login' });
  }

  async navigateViaUrl() {
    await this.page.goto('/users/login');
  }

  async navigateViaMenu() {
    await this.header.dmsLoginLink.click();
  }

  async submitCredentials(username: string, password: string) {
    await this.usernameInput.fill(username);
    await this.passwordInput.fill(password);
    await this.passwordInput.press('Enter');
  }

  async loginAsMember(username: string, password: string): Promise<EventsIndexPage> {
    await this.submitCredentials(username, password);
    return new EventsIndexPage(this.page);
  }

  async loginAndReturnToEventsAdd(
    username: string,
    password: string,
  ): Promise<EventsAddPage> {
    await this.submitCredentials(username, password);
    return new EventsAddPage(this.page);
  }

  async expectShowsRedirect(path: string) {
    await expect(this.page).toHaveURL(new RegExp(`redirect=${encodeURIComponent(path)}`));
  }
}
