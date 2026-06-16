import { expect, type Page } from '@playwright/test';

import { testUsers } from '../data/test-users';
import { EventsIndexPage } from '../pages/events-index.page';
import { LoginPage } from '../pages/login.page';

export async function logout(page: Page): Promise<void> {
  await page.goto('/');
  const eventsIndex = new EventsIndexPage(page);
  if (await eventsIndex.header.myAccountMenu.isVisible()) {
    await eventsIndex.header.logout();
  }
  await eventsIndex.header.expectLoggedOut();
}

export async function loginAs(
  page: Page,
  username: string,
  password: string,
): Promise<EventsIndexPage> {
  const loginPage = new LoginPage(page);
  await loginPage.navigateViaUrl();
  const eventsIndex = await loginPage.loginAsMember(username, password);
  await expect(eventsIndex.heading).toBeVisible();
  return eventsIndex;
}

export async function loginAsAdmin(page: Page): Promise<EventsIndexPage> {
  return loginAs(page, testUsers.admin.username, testUsers.admin.password);
}

export async function loginAsMember(page: Page): Promise<EventsIndexPage> {
  return loginAs(page, testUsers.member.username, testUsers.member.password);
}
