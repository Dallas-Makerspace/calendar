import { expect, type Locator } from '@playwright/test';

export class HeaderComponent {
  constructor(private readonly root: Locator) {}

  get adminMenu(): Locator {
    return this.root.getByRole('button', { name: 'Admin', exact: true });
  }

  get honorariaMenu(): Locator {
    return this.root.getByRole('button', { name: 'Honoraria', exact: true });
  }

  get financialsMenu(): Locator {
    return this.root.getByRole('button', { name: 'Financials', exact: true });
  }

  get superCalendarAdminMenu(): Locator {
    return this.root.getByRole('button', { name: 'Super Calendar Admin', exact: true });
  }

  get dmsLoginLink(): Locator {
    return this.root.getByRole('link', { name: 'DMS Login' });
  }

  get myAccountMenu(): Locator {
    return this.root.getByRole('button', { name: 'My Account' });
  }

  private get logoutLink(): Locator {
    return this.root.getByRole('link', { name: 'Logout' });
  }

  private get submitEventLink(): Locator {
    return this.root.getByRole('link', { name: 'Submit Event' });
  }

  async goToSubmitEvent() {
    await this.submitEventLink.click();
  }

  async goHome() {
    await this.root.getByRole('link', { name: 'Dallas Makerspace Calendar' }).click();
  }

  async expectLoggedOut() {
    await expect(this.dmsLoginLink).toBeVisible();
    await expect(this.myAccountMenu).toBeHidden();
    await expect(this.adminMenu).toBeHidden();
    await expect(this.honorariaMenu).toBeHidden();
    await expect(this.financialsMenu).toBeHidden();
    await expect(this.superCalendarAdminMenu).toBeHidden();
  }

  async expectNoAdminMenus() {
    await expect(this.adminMenu).toBeHidden();
    await expect(this.honorariaMenu).toBeHidden();
    await expect(this.financialsMenu).toBeHidden();
    await expect(this.superCalendarAdminMenu).toBeHidden();
  }

  async expectAllAdminMenus() {
    await expect(this.adminMenu).toBeVisible();
    await expect(this.honorariaMenu).toBeVisible();
    await expect(this.financialsMenu).toBeVisible();
    await expect(this.superCalendarAdminMenu).toBeVisible();
  }

  private get adminContactsLink(): Locator {
    return this.root.getByRole('link', { name: 'Contacts' });
  }

  async expectAdminContactsLinkVisible() {
    await this.adminMenu.click();
    await expect(this.adminContactsLink).toBeVisible();
  }

  async openFinancialsLink(linkName: string) {
    await this.financialsMenu.click();
    await this.root.getByRole('link', { name: linkName }).click();
  }

  async openHonorariaLink(linkName: string) {
    await this.honorariaMenu.click();
    await this.root.getByRole('link', { name: linkName }).click();
  }

  async logout() {
    await this.myAccountMenu.click();
    await this.logoutLink.click();
  }

  async openAdminLink(linkName: string) {
    await this.adminMenu.click();
    await this.root.getByRole('link', { name: linkName }).click();
  }

  async openSuperCalendarAdminSettings() {
    await this.superCalendarAdminMenu.click();
    await this.root.getByRole('link', { name: 'Settings' }).click();
  }

  async openHostingEvents() {
    await this.myAccountMenu.click();
    await this.root.getByRole('link', { name: 'Hosting Events' }).click();
  }

  async openAttendingEvents() {
    await this.myAccountMenu.click();
    await this.root.getByRole('link', { name: 'Attending Events' }).click();
  }
}
