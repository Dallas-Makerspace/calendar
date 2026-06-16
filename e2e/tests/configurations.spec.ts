import { test, expect } from '@playwright/test';

import { referenceData } from './data/reference-data';
import { loginAsAdmin } from './helpers/admin-session';
import { ConfigurationsPage } from './pages/configurations.page';
import { EventsAddPage } from './pages/events-add.page';

const configurationNames = [
  'Automatic Approval Time',
  'Honoraria Approval Time',
  'Honoraria Booking Lead Time',
  'Minimum Booking Lead Time',
  'Maximum Booking Lead Time',
  'Role Call Cutoff',
] as const;

test.describe('System configuration admin', () => {
  test('admin views and edits booking lead time configuration', async ({ page }) => {
    const { configuration } = referenceData;
    const configurations = new ConfigurationsPage(page);
    const eventsAdd = new EventsAddPage(page);

    await loginAsAdmin(page);
    await configurations.navigateViaMenu();
    await expect(configurations.heading).toBeVisible();
    await configurations.expectConfigurationRows(configurationNames);
    await configurations.expectAllowHonorariaHidden();

    await configurations.openEdit(configuration.minimumBookingLeadTime);
    await configurations.saveValue(configuration.testMinimumLeadDays);
    await configurations.expectValueInIndex(
      configuration.minimumBookingLeadTime,
      configuration.testMinimumLeadDays,
    );

    await eventsAdd.navigateViaMenu();
    await expect(eventsAdd.minimumBookingLeadTime).toHaveText(configuration.testMinimumLeadDays);
    await expect(eventsAdd.eventStartHelp).toContainText('10 days from today');

    await configurations.navigateViaMenu();
    await configurations.openEdit(configuration.minimumBookingLeadTime);
    await configurations.saveValue(configuration.defaultMinimumLeadDays);
    await configurations.expectValueInIndex(
      configuration.minimumBookingLeadTime,
      configuration.defaultMinimumLeadDays,
    );
  });
});
