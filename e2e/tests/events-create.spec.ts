import { test, expect } from '@playwright/test';

import { testUsers } from './data/test-users';
import { loginAsAdmin, loginAsMember } from './helpers/admin-session';
import { approveEvent, cancelEvent, createPendingEvent } from './helpers/event-workflow';
import { eventStartEnd } from './helpers/dates';
import { ContactsPage } from './pages/contacts.page';
import { EventsAddPage } from './pages/events-add.page';
import { MemberEventsPage } from './pages/member-events.page';

test.describe('Event creation', () => {
  test('blacklisted member cannot access the submit event form', async ({ page }) => {
    const contacts = new ContactsPage(page);
    const eventsAdd = new EventsAddPage(page);

    await loginAsAdmin(page);
    await contacts.openEditForAdUsername(testUsers.member.username);
    await contacts.setBlacklisted(true);

    await loginAsMember(page);
    await eventsAdd.navigateViaMenu();
    await eventsAdd.expectBlacklistedAlert();

    await loginAsAdmin(page);
    await contacts.openEditForAdUsername(testUsers.member.username);
    await contacts.setBlacklisted(false);
  });

  test('non-sponsored submit links event to the logged-in member contact', async ({ page }) => {
    const eventTitle = `E2E Non-Sponsored ${Date.now()}`;
    const eventsAdd = new EventsAddPage(page);
    const memberEvents = new MemberEventsPage(page);

    await loginAsMember(page);
    await createPendingEvent(page, {
      title: eventTitle,
      room: 'Common Area',
      category: 'Fiber Arts',
      freeSpaces: 10,
    });

    await memberEvents.navigateToHostingViaMenu();
    await memberEvents.expectHostedEvent(eventTitle, 'pending');

    const eventId = await approveEvent(page, eventTitle);
    await loginAsMember(page);
    await memberEvents.navigateToHostingViaMenu();
    await memberEvents.expectHostedEvent(eventTitle, 'approved');

    await loginAsAdmin(page);
    await cancelEvent(page, eventId);
  });

  test('member submits a free class event', async ({ page }) => {
    const eventTitle = `E2E Free Class ${Date.now()}`;
    const memberEvents = new MemberEventsPage(page);

    await loginAsMember(page);
    await createPendingEvent(page, {
      title: eventTitle,
      room: 'Common Area',
      category: 'Fiber Arts',
      freeSpaces: 10,
    });
    await memberEvents.navigateToHostingViaMenu();
    await memberEvents.expectHostedEvent(eventTitle, 'pending');

    const eventId = await approveEvent(page, eventTitle);
    await loginAsAdmin(page);
    await cancelEvent(page, eventId);
  });

  test('validation rejects start date before minimum lead time', async ({ page }) => {
    const eventsAdd = new EventsAddPage(page);
    const { start, end } = eventStartEnd(1, 2);

    await loginAsMember(page);
    await eventsAdd.navigateViaMenu();
    await eventsAdd.selectClassType();
    await eventsAdd.fillEventForm({
      title: `E2E Start Too Soon ${Date.now()}`,
      startDaysFromNow: 5,
      freeSpaces: 5,
    });
    await eventsAdd.fillDates(start, end);
    await eventsAdd.submit();
    await eventsAdd.expectFieldError(/scheduled at least \d+ days in advance/);
  });

  test('validation rejects end date before start date', async ({ page }) => {
    const eventsAdd = new EventsAddPage(page);
    const { start } = eventStartEnd(5, 2);
    const { end: invalidEnd } = eventStartEnd(3, 2);

    await loginAsMember(page);
    await eventsAdd.navigateViaMenu();
    await eventsAdd.selectClassType();
    await eventsAdd.fillEventForm({
      title: `E2E End Before Start ${Date.now()}`,
      startDaysFromNow: 5,
    });
    await eventsAdd.fillDates(start, invalidEnd);
    await eventsAdd.submit();
    await eventsAdd.expectFieldError(/can not end before it starts/);
  });

  test('validation rejects events scheduled beyond maximum lead time', async ({ page }) => {
    const eventsAdd = new EventsAddPage(page);
    const { start, end } = eventStartEnd(200, 2);

    await loginAsMember(page);
    await eventsAdd.navigateViaMenu();
    await eventsAdd.fillEventForm({
      title: `E2E Max Lead ${Date.now()}`,
      startDaysFromNow: 5,
      freeSpaces: 5,
    });
    await eventsAdd.fillDates(start, end);
    await eventsAdd.submit();
    await eventsAdd.expectFieldError(/can only be scheduled \d+ days in advance/);
  });

  test('requires prerequisite automatically enables members only', async ({ page }) => {
    const eventsAdd = new EventsAddPage(page);

    await loginAsMember(page);
    await eventsAdd.navigateViaMenu();
    await eventsAdd.fillEventForm({
      title: `E2E Prereq Members Only ${Date.now()}`,
      requiresPrerequisite: '3D Printer Basics',
      freeSpaces: 5,
    });
    await eventsAdd.expectMembersOnlyChecked(true);
  });
});
