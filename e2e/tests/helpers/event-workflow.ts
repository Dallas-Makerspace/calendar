import type { Page } from '@playwright/test';

import { loginAsAdmin, loginAsMember } from './admin-session';
import { EventsAddPage, type EventFormOptions } from '../pages/events-add.page';
import { EventsEditPage } from '../pages/events-edit.page';
import { PendingEventsPage } from '../pages/pending-events.page';

export async function createPendingEvent(page: Page, options: EventFormOptions) {
  const eventsAdd = new EventsAddPage(page);
  await eventsAdd.navigateViaMenu();
  await eventsAdd.fillAndSubmit(options);
  await eventsAdd.expectSuccessFlash();
}

export async function approveEvent(page: Page, eventName: string): Promise<number> {
  await loginAsAdmin(page);
  const pending = new PendingEventsPage(page);
  await pending.navigateViaMenu();
  const eventId = await pending.getEventId(eventName);
  await pending.approveEvent(eventName);
  return eventId;
}

export async function cancelEvent(page: Page, eventId: number) {
  const edit = new EventsEditPage(page);
  await edit.navigateViaUrl(eventId);
  await edit.cancelEvent();
}

export async function createApprovedEvent(
  page: Page,
  options: EventFormOptions,
): Promise<number> {
  await loginAsMember(page);
  await createPendingEvent(page, options);
  return approveEvent(page, options.title);
}

export async function rejectEvent(page: Page, eventName: string, reason: string) {
  await loginAsAdmin(page);
  const pending = new PendingEventsPage(page);
  await pending.navigateViaMenu();
  const rejection = await pending.openRejectForm(eventName);
  await rejection.rejectWithReason(reason);
}
