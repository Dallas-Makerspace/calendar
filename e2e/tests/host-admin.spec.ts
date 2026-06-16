import { test, expect } from '@playwright/test';

import { testUsers } from './data/test-users';
import { loginAsAdmin, loginAsMember } from './helpers/admin-session';
import { cancelEvent, createApprovedEvent } from './helpers/event-workflow';
import { EventsArchivePage } from './pages/events-archive.page';
import { EventsEditPage } from './pages/events-edit.page';
import { EventsViewPage } from './pages/events-view.page';
import { ExportHonorariaPage } from './pages/export-honoraria.page';
import { HonorariaIndexPage } from './pages/honoraria-index.page';
import { MemberEventsPage } from './pages/member-events.page';
import { RegistrationsEventPage } from './pages/registrations-event.page';

test.describe('Host and admin operations', () => {
  test('member sees hosting and attending event lists', async ({ page }) => {
    const eventTitle = `E2E Hosting List ${Date.now()}`;
    const memberEvents = new MemberEventsPage(page);

    await loginAsMember(page);
    await memberEvents.navigateToHostingViaMenu();
    await expect(memberEvents.hostingHeading).toBeVisible();

    await memberEvents.navigateToAttendingViaMenu();
    await expect(memberEvents.attendingHeading).toBeVisible();

    const eventId = await createApprovedEvent(page, { title: eventTitle, freeSpaces: 10 });
    await loginAsMember(page);
    await memberEvents.navigateToHostingViaMenu();
    await memberEvents.expectHostedEvent(eventTitle);

    await loginAsAdmin(page);
    await cancelEvent(page, eventId);
  });

  test('admin can browse the events archive', async ({ page }) => {
    const archive = new EventsArchivePage(page);

    await loginAsAdmin(page);
    await archive.navigateViaMenu();
    await archive.expectVisible();
  });

  test('financial export page loads for admin and denies members', async ({ page }) => {
    const exportPage = new ExportHonorariaPage(page);

    await loginAsAdmin(page);
    await exportPage.navigateViaMenu();
    await expect(exportPage.heading).toBeVisible();

    await loginAsMember(page);
    await exportPage.navigateViaUrl();
    await expect(exportPage.heading).toBeHidden();
  });

  test('standalone honoraria CRUD index is admin-only', async ({ page }) => {
    const honoraria = new HonorariaIndexPage(page);

    await loginAsAdmin(page);
    await honoraria.navigateViaUrl();
    await expect(honoraria.heading).toBeVisible();

    await loginAsMember(page);
    await honoraria.navigateViaUrl();
    await honoraria.expectAccessDenied();
  });

  test('owner can cancel an approved event with registrations', async ({ page }) => {
    const eventTitle = `E2E Cancel Event ${Date.now()}`;
    const eventsView = new EventsViewPage(page);
    const registration = new RegistrationsEventPage(page);

    const eventId = await createApprovedEvent(page, { title: eventTitle, freeSpaces: 10 });

    await eventsView.navigateViaUrl(eventId);
    await eventsView.registerForEvent();
    await registration.fillAndSubmit({
      name: 'Cancel Test Guest',
      email: `cancel-event-${Date.now()}@test.local`,
      phone: '555-0110',
    });

    await loginAsMember(page);
    const edit = new EventsEditPage(page);
    await edit.navigateViaUrl(eventId);
    await edit.cancelEvent();

    await eventsView.navigateViaUrl(eventId);
    await eventsView.expectCancelledBanner();
  });
});

test.describe('Cron endpoint', () => {
  test('cron URL is reachable without authentication', async ({ page }) => {
    const response = await page.goto('/events/cron');
    expect(response?.ok()).toBeTruthy();
  });
});

test.describe('Paid registration', () => {
  test.skip(
    !process.env.BRAINTREE_MERCHID,
    'INFRA: Braintree — set BRAINTREE_MERCHID to run paid registration tests',
  );

  test('placeholder for Braintree paid registration', async () => {
    // Implemented when Braintree sandbox credentials are available.
  });
});

test.describe('SSO login', () => {
  test.skip(
    process.env.OIDC_ENABLED !== 'true',
    'INFRA: OIDC — enable Keycloak and set OIDC_ENABLED=true to run SSO tests',
  );

  test('placeholder for SSO login flow', async () => {
    // Implemented when OIDC infrastructure is configured.
  });
});

test.describe('Email delivery verification', () => {
  test.skip(
    !process.env.SPARKPOST_APIKEY && process.env.EMAIL_DEV !== 'true',
    'INFRA: SparkPost or Email-dev — configure email transport to verify delivery',
  );

  test('placeholder for email delivery checklist', async () => {
    // Implemented when SparkPost or MailHog SMTP patch is active.
  });
});
