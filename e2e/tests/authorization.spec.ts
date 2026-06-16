import { test, expect } from '@playwright/test';

import { testUsers } from './data/test-users';
import { loginAs, loginAsAdmin } from './helpers/admin-session';
import { CategoriesPage } from './pages/categories.page';
import { EventsIndexPage } from './pages/events-index.page';
import { ExportHonorariaPage } from './pages/export-honoraria.page';
import { HonorariaIndexPage } from './pages/honoraria-index.page';
import { PendingEventsPage } from './pages/pending-events.page';
import { PendingHonorariaPage } from './pages/pending-honoraria.page';

test.describe('Authorization and menu flags', () => {
  test('members without admin groups see no admin menus', async ({ page }) => {
    const eventsIndex = new EventsIndexPage(page);

    for (const user of [testUsers.member, testUsers.memberCommittee]) {
      await loginAs(page, user.username, user.password);
      await eventsIndex.header.expectNoAdminMenus();
      await eventsIndex.header.logout();
    }
  });

  test('full admin sees all admin menus and contacts link', async ({ page }) => {
    const eventsIndex = new EventsIndexPage(page);

    await loginAsAdmin(page);
    await eventsIndex.header.expectAllAdminMenus();
    await eventsIndex.header.expectAdminContactsLinkVisible();
  });

  test('financial admin sees export but not calendar admin CRUD', async ({ page }) => {
    const eventsIndex = new EventsIndexPage(page);
    const exportHonoraria = new ExportHonorariaPage(page);
    const categories = new CategoriesPage(page);

    await loginAs(
      page,
      testUsers.financialAdmin.username,
      testUsers.financialAdmin.password,
    );
    await expect(eventsIndex.header.financialsMenu).toBeVisible();
    await expect(eventsIndex.header.adminMenu).toBeHidden();

    await exportHonoraria.navigateViaMenu();
    await expect(exportHonoraria.heading).toBeVisible();

    await categories.navigateViaUrl();
    await categories.expectAccessDenied();
  });

  test('honorarium admin sees honoraria menu but not calendar admin routes', async ({ page }) => {
    const eventsIndex = new EventsIndexPage(page);
    const pendingEvents = new PendingEventsPage(page);
    const pendingHonoraria = new PendingHonorariaPage(page);
    const honorariaIndex = new HonorariaIndexPage(page);

    await loginAs(
      page,
      testUsers.honorariumAdmin.username,
      testUsers.honorariumAdmin.password,
    );
    await expect(eventsIndex.header.honorariaMenu).toBeVisible();
    await expect(eventsIndex.header.adminMenu).toBeHidden();

    await pendingHonoraria.navigateViaMenu();
    await expect(pendingHonoraria.heading).toBeVisible();

    await pendingEvents.navigateViaUrl();
    await pendingEvents.expectAccessDenied();

    await honorariaIndex.navigateViaUrl();
    await honorariaIndex.expectAccessDenied();
  });
});
