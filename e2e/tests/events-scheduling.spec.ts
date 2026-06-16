import { test, expect } from '@playwright/test';

import { loginAsAdmin, loginAsMember } from './helpers/admin-session';
import {
  chicagoYmd,
  formatChicagoCancellationDeadline,
  formatChicagoViewEndTime,
  formatChicagoViewTime,
  scheduleSession,
  type ScheduleSession,
} from './helpers/dates';
import { cancelEvent, createApprovedEvent } from './helpers/event-workflow';
import { EventsAddPage } from './pages/events-add.page';
import { EventsEditPage } from './pages/events-edit.page';
import { EventsViewPage } from './pages/events-view.page';

const cancellationDays = 3;
const createdEventIds: number[] = [];

function uniqueBaseDay() {
  return 14 + (Date.now() % 25);
}

async function trackApprovedEvent(
  page: import('@playwright/test').Page,
  options: Parameters<typeof createApprovedEvent>[1],
) {
  const eventId = await createApprovedEvent(page, options);
  createdEventIds.push(eventId);
  return eventId;
}

test.describe('Event scheduling and capacity on the event page', () => {
  test.afterEach(async ({ page }) => {
    while (createdEventIds.length > 0) {
      const eventId = createdEventIds.pop()!;
      try {
        await loginAsAdmin(page);
        await cancelEvent(page, eventId);
      } catch {
        // Best-effort cleanup so later tests are not blocked by leftover bookings.
      }
    }
  });

  test('same-day event spans multiple hours on one calendar day', async ({ page }) => {
    const baseDay = uniqueBaseDay();
    const title = `E2E Same Day Workshop ${Date.now()}`;
    const schedule: ScheduleSession = { daysFromNow: baseDay, startHour: 10, durationHours: 7 };
    const primary = scheduleSession(schedule);
    const eventsView = new EventsViewPage(page);

    const eventId = await trackApprovedEvent(page, {
      title,
      shortDescription: 'All-day workshop on a single day',
      schedule,
      room: 'Common Area',
      freeSpaces: 20,
    });

    await eventsView.navigateViaUrl(eventId);
    await eventsView.expectTitle(title);
    await eventsView.expectWhenSectionContains(formatChicagoViewTime(primary.startDate));
    await eventsView.expectWhenSectionContains(
      formatChicagoViewEndTime(primary.startDate, primary.endDate),
    );
    await eventsView.expectWhenSectionLineCount(1);
    await eventsView.expectRegisterButtonVisible();
    await eventsView.expectSpaceCountAvailable(20, 20);
  });

  test('48-hour hackathon spans from one day to the next on the event page', async ({ page }) => {
    const baseDay = uniqueBaseDay();
    const title = `E2E 48 Hour Hackathon ${Date.now()}`;
    const schedule: ScheduleSession = { daysFromNow: baseDay, startHour: 18, durationHours: 48 };
    const primary = scheduleSession(schedule);
    const eventsView = new EventsViewPage(page);

    const eventId = await trackApprovedEvent(page, {
      title,
      shortDescription: 'Continuous 48-hour hackathon across two calendar days',
      schedule,
      primaryType: 'Event',
      room: 'Back Parking Lot',
      freeSpaces: 100,
    });

    await eventsView.navigateViaUrl(eventId);
    await eventsView.expectTitle(title);
    await eventsView.expectWhenSectionContains(formatChicagoViewTime(primary.startDate));
    await eventsView.expectWhenSectionContains(formatChicagoViewTime(primary.endDate));
    expect(chicagoYmd(primary.startDate)).not.toBe(chicagoYmd(primary.endDate));
    await eventsView.expectWhenSectionLineCount(1);
    await eventsView.expectRegisterButtonVisible();
  });

  test('multipart wood skills training lists every session on the event page', async ({ page }) => {
    const baseDay = uniqueBaseDay();
    const title = `E2E Wood Skills Training ${Date.now()}`;
    const schedule: ScheduleSession = { daysFromNow: baseDay, startHour: 19, durationHours: 2 };
    const continuedSessions: ScheduleSession[] = [
      { daysFromNow: baseDay + 2, startHour: 19, durationHours: 2 },
      { daysFromNow: baseDay + 7, startHour: 10, durationHours: 2 },
      { daysFromNow: baseDay + 8, startHour: 10, durationHours: 2 },
    ];
    const eventsView = new EventsViewPage(page);

    const eventId = await trackApprovedEvent(page, {
      title,
      shortDescription: 'Evening and weekend wood skills sessions in one registration',
      schedule,
      continuedSessions,
      multipart: true,
      room: 'Common Area',
      category: 'Woodshop',
      tool: 'Table Saw',
      freeSpaces: 12,
    });

    await eventsView.navigateViaUrl(eventId);
    await eventsView.expectTitle(title);
    await eventsView.expectWhenSectionLineCount(4);
    for (const session of [schedule, ...continuedSessions]) {
      const dates = scheduleSession(session);
      await eventsView.expectWhenSectionContains(formatChicagoViewTime(dates.startDate));
      await eventsView.expectWhenSectionContains(
        formatChicagoViewEndTime(dates.startDate, dates.endDate),
      );
    }
    await eventsView.expectRegisterButtonVisible();
  });

  test('setup and teardown extend room booking for single-day and multiday events', async ({ page }) => {
    const baseDay = uniqueBaseDay();
    const sameDayTitle = `E2E Setup Same Day ${Date.now()}`;
    const hackathonTitle = `E2E Setup Hackathon ${Date.now()}`;
    const sameDaySchedule: ScheduleSession = { daysFromNow: baseDay, startHour: 11, durationHours: 4 };
    const hackathonSchedule: ScheduleSession = {
      daysFromNow: baseDay + 10,
      startHour: 18,
      durationHours: 48,
    };
    const eventsEdit = new EventsEditPage(page);

    const sameDayId = await trackApprovedEvent(page, {
      title: sameDayTitle,
      schedule: sameDaySchedule,
      room: 'Purple Classroom',
      setupMinutes: 30,
      teardownMinutes: 30,
      freeSpaces: 15,
    });

    await loginAsAdmin(page);
    await eventsEdit.navigateViaUrl(sameDayId);
    await eventsEdit.expectSetupMinutes(30);
    await eventsEdit.expectTeardownMinutes(30);

    const hackathonId = await trackApprovedEvent(page, {
      title: hackathonTitle,
      schedule: hackathonSchedule,
      room: 'Back Parking Lot',
      setupMinutes: 30,
      teardownMinutes: 30,
      freeSpaces: 50,
    });

    await loginAsAdmin(page);
    await eventsEdit.navigateViaUrl(hackathonId);
    await eventsEdit.expectSetupMinutes(30);
    await eventsEdit.expectTeardownMinutes(30);
  });

  test('multipart setup and teardown reserve the room and tools for each session', async ({
    page,
  }) => {
    const baseDay = uniqueBaseDay() + 20;
    const title = `E2E Multipart Booking ${Date.now()}`;
    const schedule: ScheduleSession = { daysFromNow: baseDay, startHour: 9, durationHours: 2 };
    const secondSession: ScheduleSession = { daysFromNow: baseDay + 3, startHour: 9, durationHours: 2 };
    const continuedSessions = [secondSession];
    const secondDates = scheduleSession(secondSession);
    const eventsEdit = new EventsEditPage(page);
    const eventsAdd = new EventsAddPage(page);

    const eventId = await trackApprovedEvent(page, {
      title,
      schedule,
      continuedSessions,
      multipart: true,
      room: 'Interactive Classroom',
      tool: 'Leather Sewing Machine',
      setupMinutes: 30,
      teardownMinutes: 30,
      freeSpaces: 8,
    });

    await loginAsAdmin(page);
    await eventsEdit.navigateViaUrl(eventId);
    await eventsEdit.expectSetupMinutes(30);
    await eventsEdit.expectContinuedDateStart(2, secondDates.start);

    await loginAsMember(page);
    await eventsAdd.navigateViaUrl();
    await eventsAdd.fillEventForm({
      title: `E2E Room Conflict ${Date.now()}`,
      schedule: secondSession,
      room: 'Interactive Classroom',
      setupMinutes: 30,
      teardownMinutes: 30,
      freeSpaces: 5,
    });
    await eventsAdd.submit();
    await eventsAdd.expectFieldError(/not available at the requested time/i);
    await expect(page.getByRole('link', { name: title })).toBeVisible();

    await eventsAdd.navigateViaUrl();
    await eventsAdd.fillEventForm({
      title: `E2E Tool Conflict ${Date.now()}`,
      schedule: secondSession,
      room: 'Galley',
      tool: 'Leather Sewing Machine',
      setupMinutes: 30,
      teardownMinutes: 30,
      freeSpaces: 5,
    });
    await eventsAdd.submit();
    await eventsAdd.expectFieldError(/tools selected for this event are not available/i);
    await expect(
      page.locator('.help-block').filter({ hasText: /tools selected for this event are not available/i }),
    ).toContainText('Leather Sewing Machine');
  });

  test('cancellation window is based on the first session start for all schedule shapes', async ({
    page,
  }) => {
    const baseDay = uniqueBaseDay() + 30;
    const eventsView = new EventsViewPage(page);

    const scenarios = [
      {
        title: `E2E Cancel Same Day ${Date.now()}`,
        schedule: { daysFromNow: baseDay, startHour: 10, durationHours: 8 } satisfies ScheduleSession,
      },
      {
        title: `E2E Cancel Hackathon ${Date.now()}`,
        schedule: { daysFromNow: baseDay + 1, startHour: 18, durationHours: 48 } satisfies ScheduleSession,
      },
      {
        title: `E2E Cancel Multipart ${Date.now()}`,
        schedule: { daysFromNow: baseDay + 2, startHour: 19, durationHours: 2 } satisfies ScheduleSession,
        continuedSessions: [
          { daysFromNow: baseDay + 4, startHour: 19, durationHours: 2 },
          { daysFromNow: baseDay + 9, startHour: 10, durationHours: 2 },
        ] as ScheduleSession[],
        multipart: true,
      },
    ];

    for (const scenario of scenarios) {
      const primary = scheduleSession(scenario.schedule);
      const wrongSession = scenario.continuedSessions?.[0]
        ? scheduleSession(scenario.continuedSessions[0])
        : null;
      const expectedDeadline = formatChicagoCancellationDeadline(
        primary.startDate,
        cancellationDays,
      );

      const eventId = await trackApprovedEvent(page, {
        title: scenario.title,
        schedule: scenario.schedule,
        continuedSessions: scenario.continuedSessions,
        multipart: scenario.multipart,
        cancellationDays,
        room: 'Common Area',
        freeSpaces: 10,
      });

      await eventsView.navigateViaUrl(eventId);
      await eventsView.expectCancellationDeadline(expectedDeadline);
      if (wrongSession) {
        const wrongDeadline = formatChicagoCancellationDeadline(
          wrongSession.startDate,
          cancellationDays,
        );
        await expect(eventsView.cancellationNotice).not.toContainText(wrongDeadline);
      }
    }
  });

  test('zero free and zero paid spaces means open attendance without a capacity counter', async ({
    page,
  }) => {
    const title = `E2E Open House ${Date.now()}`;
    const eventsView = new EventsViewPage(page);

    const eventId = await trackApprovedEvent(page, {
      title,
      shortDescription:
        'Open house with no registration cap (0 free / 0 paid spaces — walk-ins welcome)',
      schedule: { daysFromNow: uniqueBaseDay(), startHour: 12, durationHours: 3 },
      room: 'Common Area',
      freeSpaces: 0,
      paidSpaces: 0,
    });

    await eventsView.navigateViaUrl(eventId);
    await eventsView.expectTitle(title);
    await eventsView.expectCost('Free');
    await eventsView.expectNoCapacityCountDisplayed();
    await eventsView.expectRegisterButtonVisible();
  });

  test('mixed free and paid capacity appears correctly on the event page', async ({ page }) => {
    const title = `E2E Mixed Capacity ${Date.now()}`;
    const eventsView = new EventsViewPage(page);

    const eventId = await trackApprovedEvent(page, {
      title,
      shortDescription: 'Limited free observer spots plus paid participant seats',
      schedule: { daysFromNow: uniqueBaseDay(), startHour: 14, durationHours: 2 },
      room: 'Common Area',
      paidEventType: 'paid',
      cost: 10,
      freeSpaces: 5,
      paidSpaces: 10,
    });

    await eventsView.navigateViaUrl(eventId);
    await eventsView.expectCost('$10.00');
    await eventsView.expectSpaceCountAvailable(15, 15);
    await eventsView.expectRegisterButtonVisible();
  });

  test('free spots with unlimited paid seats shows only the free pool on the event page', async ({
    page,
  }) => {
    const title = `E2E Free Plus Unlimited Paid ${Date.now()}`;
    const eventsView = new EventsViewPage(page);

    const eventId = await trackApprovedEvent(page, {
      title,
      shortDescription: 'Capped free observers with unlimited paid participant slots (paid_spaces=0)',
      schedule: { daysFromNow: uniqueBaseDay(), startHour: 16, durationHours: 2 },
      room: 'Common Area',
      paidEventType: 'paid',
      cost: 5,
      freeSpaces: 5,
      paidSpaces: 0,
    });

    await eventsView.navigateViaUrl(eventId);
    await eventsView.expectCost('$5.00');
    await eventsView.expectSpaceCountAvailable(5, 5);
    await eventsView.expectRegisterButtonVisible();
  });
});
