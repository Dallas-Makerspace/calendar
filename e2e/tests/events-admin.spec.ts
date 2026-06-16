import { test, expect } from '@playwright/test';

import { loginAsAdmin, loginAsMember } from './helpers/admin-session';
import {
  approveEvent,
  cancelEvent,
  createApprovedEvent,
  createPendingEvent,
  rejectEvent,
} from './helpers/event-workflow';
import { EventsAddPage } from './pages/events-add.page';
import { EventsEditPage } from './pages/events-edit.page';
import { EventsViewPage } from './pages/events-view.page';
import { PendingEventsPage } from './pages/pending-events.page';
import { PendingHonorariaPage } from './pages/pending-honoraria.page';
import { ProcessRejectionPage } from './pages/process-rejection.page';

test.describe('Event approval and administration', () => {
  test('admin approves a pending free class event', async ({ page }) => {
    const eventTitle = `E2E Approve ${Date.now()}`;
    const pending = new PendingEventsPage(page);
    const eventsView = new EventsViewPage(page);

    await loginAsMember(page);
    await createPendingEvent(page, { title: eventTitle, freeSpaces: 10 });

    const eventId = await approveEvent(page, eventTitle);
    await pending.navigateViaMenu();
    await expect(pending.row(eventTitle)).toBeHidden();

    await eventsView.navigateViaUrl(eventId);
    await eventsView.expectTitle(eventTitle);

    await loginAsAdmin(page);
    await cancelEvent(page, eventId);
  });

  test('admin rejects a pending event with a reason', async ({ page }) => {
    const eventTitle = `E2E Reject ${Date.now()}`;
    const pending = new PendingEventsPage(page);

    await loginAsMember(page);
    await createPendingEvent(page, { title: eventTitle, freeSpaces: 5 });

    await loginAsAdmin(page);
    await pending.navigateViaMenu();
    const eventId = await pending.getEventId(eventTitle);
    await rejectEvent(page, eventTitle, 'E2E test rejection reason');

    await pending.navigateViaMenu();
    await expect(pending.row(eventTitle)).toBeHidden();

    const eventsView = new EventsViewPage(page);
    await eventsView.navigateViaUrl(eventId);
    await eventsView.expectCancelledBanner();
  });

  test('reject flow uses process-rejection form', async ({ page }) => {
    const eventTitle = `E2E Process Rejection ${Date.now()}`;
    const pending = new PendingEventsPage(page);
    const processRejection = new ProcessRejectionPage(page);

    await loginAsMember(page);
    await createPendingEvent(page, { title: eventTitle, freeSpaces: 5 });

    await loginAsAdmin(page);
    await pending.navigateViaMenu();
    await pending.openRejectForm(eventTitle);
    await processRejection.expectVisible();
    await processRejection.rejectWithReason('Not suitable for calendar');
  });

  test('honorarium pending events appear in honoraria queue', async ({ page }) => {
    const eventTitle = `E2E Honorarium ${Date.now()}`;
    const honorariaPending = new PendingHonorariaPage(page);

    await loginAsMember(page);
    await createPendingEvent(page, {
      title: eventTitle,
      freeSpaces: 10,
      requestHonorarium: true,
      committee: 'Creative Arts',
      startDaysFromNow: 12,
    });

    await loginAsAdmin(page);
    await honorariaPending.navigateViaMenu();
    await expect(honorariaPending.row(eventTitle)).toBeVisible();
    await honorariaPending.approveEvent(eventTitle);

    const pending = new PendingEventsPage(page);
    await pending.navigateViaMenu();
    await expect(pending.row(eventTitle)).toBeHidden();
  });

  test('owner can edit short description on approved event', async ({ page }) => {
    const eventTitle = `E2E Owner Edit ${Date.now()}`;
    const updatedDescription = 'Updated short description from E2E';
    const eventsView = new EventsViewPage(page);
    const eventsEdit = new EventsEditPage(page);

    const eventId = await createApprovedEvent(page, { title: eventTitle, freeSpaces: 10 });

    await loginAsMember(page);
    await eventsView.navigateViaUrl(eventId);
    await eventsView.openEdit();
    await eventsEdit.updateShortDescription(updatedDescription);
    await eventsEdit.expectSuccessFlash();

    await eventsView.navigateViaUrl(eventId);
    await eventsView.expectShortDescription(updatedDescription);

    await loginAsAdmin(page);
    await cancelEvent(page, eventId);
  });

  test('admin can edit schedule fields the owner cannot', async ({ page }) => {
    const eventTitle = `E2E Admin Edit ${Date.now()}`;
    const eventsView = new EventsViewPage(page);
    const eventsEdit = new EventsEditPage(page);

    const eventId = await createApprovedEvent(page, { title: eventTitle, freeSpaces: 10 });

    await loginAsMember(page);
    await eventsEdit.navigateViaUrl(eventId);
    await eventsEdit.expectEventStartReadOnly();

    await loginAsAdmin(page);
    await eventsEdit.navigateViaUrl(eventId);
    await eventsEdit.expectEventStartEditable();
    await eventsEdit.updateSchedule(10);

    await cancelEvent(page, eventId);
  });

  test('member can copy an owned approved event', async ({ page }) => {
    const eventTitle = `E2E Copy Source ${Date.now()}`;
    const copyTitle = `E2E Copy Target ${Date.now()}`;
    const eventsView = new EventsViewPage(page);
    const eventsAdd = new EventsAddPage(page);

    const eventId = await createApprovedEvent(page, {
      title: eventTitle,
      freeSpaces: 10,
      category: 'Fiber Arts',
    });

    await loginAsMember(page);
    await eventsView.navigateViaUrl(eventId);
    await eventsView.openCopy();
    await eventsAdd.expectPrefilledTitle(eventTitle);
    await eventsAdd.expectEmptyDates();
    await eventsAdd.fillEventForm({ title: copyTitle, startDaysFromNow: 8, freeSpaces: 10 });
    await eventsAdd.submit();
    await eventsAdd.expectSuccessFlash();

    await loginAsAdmin(page);
    await cancelEvent(page, eventId);
    const copyId = await approveEvent(page, copyTitle);
    await cancelEvent(page, copyId);
  });

  test('rejected events hide the edit button for the owner', async ({ page }) => {
    const eventTitle = `E2E Rejected Edit ${Date.now()}`;
    const eventsView = new EventsViewPage(page);
    const pending = new PendingEventsPage(page);

    await loginAsMember(page);
    await createPendingEvent(page, { title: eventTitle, freeSpaces: 5 });

    await loginAsAdmin(page);
    await pending.navigateViaMenu();
    const eventId = await pending.getEventId(eventTitle);
    await rejectEvent(page, eventTitle, 'E2E rejection');

    await loginAsMember(page);
    await eventsView.navigateViaUrl(eventId);
    await eventsView.expectEditEventHidden();
  });
});
