import { test, expect } from '@playwright/test';

import { testUsers } from './data/test-users';
import { loginAsAdmin, loginAsMember } from './helpers/admin-session';
import { ContactsPage } from './pages/contacts.page';
import { EventsIndexPage } from './pages/events-index.page';
import { LoginPage } from './pages/login.page';

test('member can log in with LDAP credentials', async ({ page }) => {
  const loginPage = new LoginPage(page);
  const eventsIndex = new EventsIndexPage(page);

  await loginPage.navigateViaUrl();
  await expect(loginPage.heading).toBeVisible();

  await loginPage.loginAsMember(testUsers.member.username, testUsers.member.password);

  await expect(eventsIndex.heading).toBeVisible();
  await expect(eventsIndex.header.adminMenu).toBeHidden();
  await expect(page.getByRole('link', { name: 'Submit Event' })).toBeVisible();
});

test('member login provisions a contact record for admin lookup', async ({ page }) => {
  const loginPage = new LoginPage(page);
  const contacts = new ContactsPage(page);

  await loginPage.navigateViaUrl();
  await loginPage.loginAsMember(testUsers.member.username, testUsers.member.password);

  await loginAsAdmin(page);
  await contacts.navigateViaMenu();
  await contacts.expectRowVisible(testUsers.member.username);
});
