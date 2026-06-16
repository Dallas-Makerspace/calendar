import { test, expect } from '@playwright/test';

import { loginAsAdmin } from './helpers/admin-session';
import { CalendarAdminPage } from './pages/calendar-admin.page';
import { EventsAddPage } from './pages/events-add.page';

test.describe('Super Calendar Admin settings', () => {
  // Both tests POST the same singleton settings form; run sequentially to avoid races.
  test.describe.configure({ mode: 'serial' });

  test('honoraria toggle enables and disables honorarium requests on the event form', async ({
    page,
  }) => {
    const calendarAdmin = new CalendarAdminPage(page);
    const eventsAdd = new EventsAddPage(page);

    await loginAsAdmin(page);

    await calendarAdmin.navigateViaMenu();
    await expect(calendarAdmin.heading).toBeVisible();
    await calendarAdmin.setAllowHonoraria(true);

    await calendarAdmin.setAllowHonoraria(false);

    await eventsAdd.navigateViaMenu();
    await eventsAdd.selectClassType();
    await eventsAdd.expectRequestHonorariumEnabled(false);

    await calendarAdmin.navigateViaMenu();
    await calendarAdmin.setAllowHonoraria(true);

    await eventsAdd.navigateViaMenu();
    await eventsAdd.selectClassType();
    await eventsAdd.expectRequestHonorariumEnabled(true);
  });

  test('custom honorarium message appears on the event form', async ({ page }) => {
    const honorariumMessage = 'Test honorarium message';
    const calendarAdmin = new CalendarAdminPage(page);
    const eventsAdd = new EventsAddPage(page);

    await loginAsAdmin(page);

    await calendarAdmin.navigateViaMenu();
    await calendarAdmin.setHonorariumMessage('');

    await calendarAdmin.setHonorariumMessage(honorariumMessage);

    await eventsAdd.navigateViaMenu();
    await eventsAdd.selectClassType();
    await eventsAdd.expectHonorariumMessage(honorariumMessage);

    await calendarAdmin.navigateViaMenu();
    await calendarAdmin.setHonorariumMessage('');

    await eventsAdd.navigateViaMenu();
    await eventsAdd.selectClassType();
    await eventsAdd.expectHonorariumMessageHidden(honorariumMessage);
  });
});
