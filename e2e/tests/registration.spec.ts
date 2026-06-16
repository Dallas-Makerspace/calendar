import { test, expect } from '@playwright/test';

import { testUsers } from './data/test-users';
import { loginAs, loginAsAdmin, loginAsMember, logout } from './helpers/admin-session';
import { cancelEvent, createApprovedEvent } from './helpers/event-workflow';
import { EventsViewPage } from './pages/events-view.page';
import { RegistrationsEventPage } from './pages/registrations-event.page';
import { RegistrationsViewPage } from './pages/registrations-view.page';

test.describe('Event registration', () => {
  test('guest registers for a free approved event', async ({ page }) => {
    const eventTitle = `E2E Register Free ${Date.now()}`;
    const guestEmail = `guest-${Date.now()}@test.local`;
    const eventsView = new EventsViewPage(page);

    const eventId = await createApprovedEvent(page, { title: eventTitle, freeSpaces: 10 });

    await logout(page);
    await eventsView.navigateViaUrl(eventId);
    const registration = await eventsView.registerForEvent();
    const registrationView = await registration.fillAndSubmit({
      name: 'Guest Registrant',
      email: guestEmail,
      phone: '555-0101',
    });
    await registrationView.expectConfirmed();

    await loginAsAdmin(page);
    await cancelEvent(page, eventId);
  });

  test('logged-in member registers with prefilled fields', async ({ page }) => {
    const eventTitle = `E2E Member Register ${Date.now()}`;
    const eventsView = new EventsViewPage(page);
    const registration = new RegistrationsEventPage(page);

    const eventId = await createApprovedEvent(page, { title: eventTitle, freeSpaces: 10 });

    await loginAs(page, testUsers.memberCommittee.username, testUsers.memberCommittee.password);
    await registration.navigateViaUrl(eventId);
    await registration.expectNamePrefilled();
    const registrationView = await registration.fillAndSubmit({
      name: 'Committee Member',
      email: `user3-${Date.now()}@test.local`,
      phone: '555-0102',
    });
    await registrationView.expectConfirmed();

    await loginAsAdmin(page);
    await cancelEvent(page, eventId);
  });

  test('approval-required registration stays pending until host approves', async ({ page }) => {
    const eventTitle = `E2E Approval Required ${Date.now()}`;
    const guestEmail = `approval-${Date.now()}@test.local`;
    const eventsView = new EventsViewPage(page);

    const eventId = await createApprovedEvent(page, {
      title: eventTitle,
      freeSpaces: 10,
      attendeesRequireApproval: true,
    });

    await logout(page);
    await eventsView.navigateViaUrl(eventId);
    const registration = await eventsView.registerForEvent();
    const registrationView = await registration.fillAndSubmit({
      name: 'Pending Guest',
      email: guestEmail,
      phone: '555-0103',
    });
    await registrationView.expectPending();
    const registrationId = await registrationView.getRegistrationIdFromUrl();

    await loginAsMember(page);
    await eventsView.navigateViaUrl(eventId);
    await eventsView.openRegisteredAttendeesTab();
    await eventsView.approveRegistration('Pending Guest');

    await registrationView.navigateViaUrl(registrationId);
    await registrationView.expectConfirmed();

    await loginAsAdmin(page);
    await cancelEvent(page, eventId);
  });

  test('members-only event requires login to register', async ({ page }) => {
    const eventTitle = `E2E Members Only ${Date.now()}`;
    const registration = new RegistrationsEventPage(page);

    const eventId = await createApprovedEvent(page, {
      title: eventTitle,
      freeSpaces: 10,
      membersOnly: true,
    });

    await logout(page);
    await registration.navigateViaUrl(eventId);
    await registration.expectMembersOnlyGate();

    await loginAsMember(page);
    await registration.navigateViaUrl(eventId);
    const registrationView = await registration.fillAndSubmit({
      name: 'Member Attendee',
      email: `member-only-${Date.now()}@test.local`,
      phone: '555-0104',
    });
    await registrationView.expectConfirmed();

    await loginAsAdmin(page);
    await cancelEvent(page, eventId);
  });

  test('age-restricted event requires advisory and age confirmations', async ({ page }) => {
    const eventTitle = `E2E Age 18 ${Date.now()}`;
    const eventsView = new EventsViewPage(page);
    const registration = new RegistrationsEventPage(page);

    const eventId = await createApprovedEvent(page, {
      title: eventTitle,
      freeSpaces: 10,
      ageRestriction: '18 and up',
      advisories: 'Safety glasses required.',
    });

    await logout(page);
    await eventsView.navigateViaUrl(eventId);
    await eventsView.registerForEvent();
    const registrationView = await registration.fillAndSubmit({
      name: 'Age Gate Guest',
      email: `age-${Date.now()}@test.local`,
      phone: '555-0105',
      safetyConfirmation: true,
      ageConfirmation: true,
    });
    await registrationView.expectConfirmed();

    await loginAsAdmin(page);
    await cancelEvent(page, eventId);
  });

  test('guest can cancel RSVP using edit_key without logging in', async ({ page }) => {
    const eventTitle = `E2E Guest Cancel ${Date.now()}`;
    const guestEmail = `guest-cancel-${Date.now()}@test.local`;
    const eventsView = new EventsViewPage(page);

    const eventId = await createApprovedEvent(page, { title: eventTitle, freeSpaces: 10 });

    await logout(page);
    await eventsView.navigateViaUrl(eventId);
    const first = await eventsView.registerForEvent();
    const registrationView = await first.fillAndSubmit({
      name: 'Guest Cancel',
      email: guestEmail,
      phone: '555-0106',
    });
    const registrationId = await registrationView.getRegistrationIdFromUrl();
    const editKey = await registrationView.getEditKeyFromUrl();

    await registrationView.cancelRsvp();
    await registrationView.navigateViaUrl(registrationId, editKey ?? undefined);
    await registrationView.expectCancelRsvpHidden();

    await loginAsAdmin(page);
    await cancelEvent(page, eventId);
  });

  test('duplicate email registration is rejected', async ({ page }) => {
    const eventTitle = `E2E Duplicate Email ${Date.now()}`;
    const guestEmail = `duplicate-${Date.now()}@test.local`;
    const eventsView = new EventsViewPage(page);
    const registration = new RegistrationsEventPage(page);

    const eventId = await createApprovedEvent(page, { title: eventTitle, freeSpaces: 10 });

    await logout(page);
    await eventsView.navigateViaUrl(eventId);
    const first = await eventsView.registerForEvent();
    await first.fillAndSubmit({
      name: 'First Guest',
      email: guestEmail,
      phone: '555-0107',
    });

    await registration.navigateViaUrl(eventId);
    await expect(registration.submitButton).toBeVisible();
    await registration.fillRegistrationForm({
      name: 'Second Guest',
      email: guestEmail,
      phone: '555-0108',
    });
    await registration.submit();
    await registration.expectDuplicateEmailError();

    await loginAsAdmin(page);
    await cancelEvent(page, eventId);
  });

  test('full event shows no spaces available', async ({ page }) => {
    const eventTitle = `E2E Full Event ${Date.now()}`;
    const eventsView = new EventsViewPage(page);
    const registration = new RegistrationsEventPage(page);

    const eventId = await createApprovedEvent(page, { title: eventTitle, freeSpaces: 1 });

    await logout(page);
    await eventsView.navigateViaUrl(eventId);
    const first = await eventsView.registerForEvent();
    await first.fillAndSubmit({
      name: 'First Space',
      email: `full1-${Date.now()}@test.local`,
      phone: '555-0109',
    });

    await registration.navigateViaUrl(eventId);
    await expect(registration.fullEventNotice).toBeVisible();

    await loginAsAdmin(page);
    await cancelEvent(page, eventId);
  });
});
