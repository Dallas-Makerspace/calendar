import { test, expect } from '@playwright/test';

import { testUsers } from './data/test-users';
import { EventsAddPage } from './pages/events-add.page';
import { EventsIndexPage } from './pages/events-index.page';
import { LoginPage } from './pages/login.page';

test.describe('LDAP login (admin access)', () => {
  test('admin login shows admin navigation menus', async ({ page }) => {
    const loginPage = new LoginPage(page);
    const eventsIndex = new EventsIndexPage(page);

    await loginPage.navigateViaUrl();
    await loginPage.header.expectLoggedOut();
    await loginPage.loginAsMember(testUsers.admin.username, testUsers.admin.password);

    await expect(eventsIndex.heading).toBeVisible();
    await expect(eventsIndex.header.adminMenu).toBeVisible();
    await expect(eventsIndex.header.honorariaMenu).toBeVisible();
    await expect(eventsIndex.header.financialsMenu).toBeVisible();
    await expect(eventsIndex.header.superCalendarAdminMenu).toBeVisible();
  });

  test('wrong password shows invalid credentials flash', async ({ page }) => {
    const loginPage = new LoginPage(page);

    await loginPage.navigateViaUrl();
    await loginPage.header.expectLoggedOut();
    await loginPage.submitCredentials(testUsers.admin.username, 'wrong-password');

    await expect(loginPage.heading).toBeVisible();
    await expect(loginPage.errorMessage).toContainText('Invalid username or password, try again.');
    await loginPage.header.expectLoggedOut();
  });

  test('email as username shows DMS username guidance', async ({ page }) => {
    const loginPage = new LoginPage(page);

    await loginPage.navigateViaUrl();
    await loginPage.header.expectLoggedOut();
    await loginPage.submitCredentials(testUsers.admin.email, testUsers.admin.password);

    await expect(loginPage.heading).toBeVisible();
    await expect(loginPage.errorMessage).toContainText('Invalid username or password, try again.');
    await expect(loginPage.errorMessage).toContainText(
      'Be sure to use your DMS username, NOT your email or Talk username',
    );
    await loginPage.header.expectLoggedOut();
  });

  test('unknown user shows generic invalid credentials flash', async ({ page }) => {
    const loginPage = new LoginPage(page);

    await loginPage.navigateViaUrl();
    await loginPage.header.expectLoggedOut();
    await loginPage.submitCredentials(testUsers.unknown.username, testUsers.unknown.password);

    await expect(loginPage.heading).toBeVisible();
    await expect(loginPage.errorMessage).toContainText('Invalid username or password, try again.');
    await loginPage.header.expectLoggedOut();
  });

  test('disabled account cannot log in', async ({ page }) => {
    const loginPage = new LoginPage(page);

    await loginPage.navigateViaUrl();
    await loginPage.header.expectLoggedOut();
    await loginPage.submitCredentials(testUsers.disabled.username, testUsers.disabled.password);

    await expect(loginPage.heading).toBeVisible();
    await expect(loginPage.errorMessage).toContainText('Invalid username or password, try again.');
    await loginPage.header.expectLoggedOut();
  });

  test('login redirect returns to protected page after authentication', async ({ page }) => {
    const eventsAdd = new EventsAddPage(page);
    const loginPage = new LoginPage(page);

    await eventsAdd.navigateViaUrl();
    await loginPage.expectShowsRedirect('/events/add');
    await loginPage.header.expectLoggedOut();

    await loginPage.loginAndReturnToEventsAdd(
      testUsers.admin.username,
      testUsers.admin.password,
    );

    await expect(eventsAdd.generalLegend).toBeVisible();
    await expect(eventsAdd.submitEventButton).toBeVisible();
  });

  test('logout clears session and hides admin menus', async ({ page }) => {
    const loginPage = new LoginPage(page);
    const eventsIndex = new EventsIndexPage(page);

    await loginPage.navigateViaUrl();
    await loginPage.loginAsMember(testUsers.admin.username, testUsers.admin.password);
    await expect(eventsIndex.header.adminMenu).toBeVisible();

    await eventsIndex.header.logout();

    await expect(eventsIndex.heading).toBeVisible();
    await eventsIndex.header.expectLoggedOut();
  });

  test('logout then DMS Login link opens login form for a new session', async ({ page }) => {
    const loginPage = new LoginPage(page);
    const eventsIndex = new EventsIndexPage(page);

    await loginPage.navigateViaUrl();
    await loginPage.loginAsMember(testUsers.member.username, testUsers.member.password);
    await expect(eventsIndex.heading).toBeVisible();

    await eventsIndex.header.logout();
    await eventsIndex.header.expectLoggedOut();

    await loginPage.navigateViaMenu();
    await expect(loginPage.heading).toBeVisible();

    await loginPage.loginAsMember(testUsers.member.username, testUsers.member.password);
    await expect(eventsIndex.heading).toBeVisible();
  });
});
