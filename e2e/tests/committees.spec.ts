import { test, expect } from '@playwright/test';

import { loginAsAdmin } from './helpers/admin-session';
import { CommitteesPage } from './pages/committees.page';
import { EventsAddPage } from './pages/events-add.page';

test.describe('Committees admin CRUD', () => {
  test('admin manages committees and exposes them on the event form', async ({ page }) => {
    const committeeName = 'Test Committee E2E';
    const committeeUpdatedName = 'Test Committee E2E Updated';
    const committees = new CommitteesPage(page);
    const eventsAdd = new EventsAddPage(page);

    await loginAsAdmin(page);
    await committees.navigateViaMenu();
    await expect(committees.heading).toBeVisible();

    // Remove leftovers from a previous run that failed before delete.
    for (const name of [committeeUpdatedName, committeeName]) {
      if ((await committees.row(name).count()) > 0) {
        await committees.delete(name);
      }
    }

    await committees.addCommittee(committeeName);
    await committees.expectRowVisible(committeeName);

    await committees.openEdit(committeeName);
    await committees.saveCommittee(committeeUpdatedName);
    await committees.expectRowVisible(committeeUpdatedName);

    await eventsAdd.navigateViaMenu();
    await eventsAdd.selectClassType();
    await eventsAdd.expectCommitteeOption(committeeUpdatedName);

    await committees.navigateViaMenu();
    await committees.delete(committeeUpdatedName);
    await committees.expectRowHidden(committeeUpdatedName);
  });
});
