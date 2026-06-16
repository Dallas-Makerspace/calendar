import { test, expect } from '@playwright/test';

import { localIsoDate } from './helpers/dates';
import { loginAsAdmin, loginAs } from './helpers/admin-session';
import { testUsers } from './data/test-users';
import { CategoriesPage } from './pages/categories.page';
import { ConfigurationsPage } from './pages/configurations.page';
import { EventsViewPage } from './pages/events-view.page';
import { LogsPage } from './pages/logs.page';

test.describe('Audit logs', () => {
  test('super admin can browse and filter audit logs', async ({ page }) => {
    const logs = new LogsPage(page);
    const today = localIsoDate();

    await loginAsAdmin(page);
    await logs.navigateViaUrl();
    await expect(logs.heading).toBeVisible();
    await logs.expectTableColumnsVisible();
    await logs.expectHasEntries();

    await logs.filter({ startDate: today, endDate: today });
    await expect(logs.heading).toBeVisible();

    await logs.navigateViaUrl();
    await logs.filter({ userName: testUsers.admin.username });
    await expect(logs.rowContaining(testUsers.admin.username).first()).toBeVisible();

    await logs.filter({ searchString: 'Viewed' });
    await expect(logs.rowContaining('Viewed').first()).toBeVisible();
  });

  test('member cannot access audit logs', async ({ page }) => {
    const logs = new LogsPage(page);

    await loginAs(page, testUsers.member.username, testUsers.member.password);
    await logs.navigateViaUrl();
    await logs.expectAccessDenied();
  });

  test('configuration edits are logged but index and view actions are skipped', async ({ page }) => {
    const logs = new LogsPage(page);
    const configurations = new ConfigurationsPage(page);
    const categories = new CategoriesPage(page);
    const eventsView = new EventsViewPage(page);
    const configurationName = 'Maximum Booking Lead Time';
    const defaultMaximumLeadDays = '190';
    const testMaximumLeadDays = '191';
    const configEditFilter = {
      userName: testUsers.admin.username,
      searchString: 'Viewed /configurations/edit/5',
    };

    await loginAsAdmin(page);

    await configurations.navigateViaMenu();
    await configurations.openEdit(configurationName);
    await configurations.saveValue(defaultMaximumLeadDays);

    const configEditLog = await logs.expectFilteredLogEntryAdded(configEditFilter);

    await configurations.navigateViaMenu();
    await configurations.openEdit(configurationName);
    await configurations.saveValue(testMaximumLeadDays);

    await configEditLog.assertNewEntry();
    const afterEditCount = await configEditLog.countFiltered();

    await categories.navigateViaMenu();
    const afterCategoriesCount = await logs.countFiltered(configEditFilter);
    expect(afterCategoriesCount).toBe(afterEditCount);

    await eventsView.navigateViaUrl(1);
    const afterViewCount = await logs.countFiltered(configEditFilter);
    expect(afterViewCount).toBe(afterCategoriesCount);

    await configurations.navigateViaMenu();
    await configurations.openEdit(configurationName);
    await configurations.saveValue(defaultMaximumLeadDays);
  });

  test('date filter requires both start and end dates', async ({ page }) => {
    const logs = new LogsPage(page);
    const today = localIsoDate();

    await loginAsAdmin(page);
    await logs.navigateViaUrl();
    const unfilteredCount = await logs.rowCount();

    await logs.filter({ startDate: today });
    const singleDateCount = await logs.rowCount();
    expect(singleDateCount).toBe(unfilteredCount);
  });
});
