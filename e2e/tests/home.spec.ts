import { test, expect } from '@playwright/test';

import { EventsIndexPage } from './pages/events-index.page';

test('homepage displays upcoming events', async ({ page }) => {
  const eventsIndex = new EventsIndexPage(page);

  await eventsIndex.navigateViaUrl();
  await expect(eventsIndex.heading).toBeVisible();
});
