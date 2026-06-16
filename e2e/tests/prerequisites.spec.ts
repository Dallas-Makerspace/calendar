import { test, expect } from '@playwright/test';

import { loginAsAdmin } from './helpers/admin-session';
import { EventsAddPage } from './pages/events-add.page';
import { PrerequisitesPage } from './pages/prerequisites.page';

test.describe('Prerequisites admin CRUD', () => {
  test('admin manages prerequisites and exposes them on the event form', async ({ page }) => {
    const prerequisiteName = 'Test Prereq E2E';
    const prerequisiteAdGroup = 'Test Prereq E2E';
    const prerequisiteUpdatedAdGroup = 'Test Prereq E2E Updated';
    const prerequisites = new PrerequisitesPage(page);
    const eventsAdd = new EventsAddPage(page);

    await loginAsAdmin(page);
    await prerequisites.navigateViaMenu();
    await expect(prerequisites.heading).toBeVisible();

    // Remove leftovers from a previous run that failed before delete.
    if ((await prerequisites.row(prerequisiteName).count()) > 0) {
      await prerequisites.delete(prerequisiteName);
    }

    await prerequisites.addPrerequisite(prerequisiteName, prerequisiteAdGroup);
    await prerequisites.expectRowVisible(prerequisiteName);

    await prerequisites.openEdit(prerequisiteName);
    await prerequisites.savePrerequisite(prerequisiteName, prerequisiteUpdatedAdGroup);
    await expect(prerequisites.row(prerequisiteName)).toContainText(prerequisiteUpdatedAdGroup);

    await eventsAdd.navigateViaMenu();
    await eventsAdd.expectPrerequisiteInDropdowns(prerequisiteName);

    await prerequisites.navigateViaMenu();
    await prerequisites.expectRowVisible(prerequisiteName);

    await prerequisites.delete(prerequisiteName);
    await prerequisites.expectRowHidden(prerequisiteName);
  });
});
