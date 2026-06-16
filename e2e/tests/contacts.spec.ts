import { test, expect } from '@playwright/test';

import { testUsers } from './data/test-users';
import { loginAsAdmin, loginAs } from './helpers/admin-session';
import { ContactsPage } from './pages/contacts.page';
import { EventsAddPage } from './pages/events-add.page';

test.describe('Contacts admin CRUD', () => {
  test('admin manages external contacts for sponsored events', async ({ page }) => {
    const contactName = 'External Instructor';
    const contactEmail = 'external@test.local';
    const contactPhone = '555-0100';
    const duplicateContactName = 'External Instructor Duplicate';
    const contacts = new ContactsPage(page);
    const eventsAdd = new EventsAddPage(page);

    await loginAsAdmin(page);
    await contacts.navigateViaMenu();
    await expect(contacts.heading).toBeVisible();

    // Remove leftovers from a previous run that failed before delete.
    for (const name of [duplicateContactName, contactName]) {
      if ((await contacts.row(name).count()) > 0) {
        await contacts.delete(name);
      }
    }

    await contacts.addContact(contactName, contactEmail, contactPhone);
    await contacts.expectW9OnFile(contactName, false);

    await contacts.openEdit(contactName);
    await contacts.setW9OnFile(true);
    await contacts.expectW9OnFile(contactName, true);

    await eventsAdd.navigateViaMenu();
    await eventsAdd.enableSponsoredEvent();
    await eventsAdd.expectExistingInstructorOption(contactName);

    await contacts.navigateViaMenu();
    await contacts.openAddForm();
    await contacts.fillContactForm(duplicateContactName, contactEmail, contactPhone);
    await contacts.submitAddForm();
    await expect(contacts.emailValidationError).toContainText('This value is already in use');

    await contacts.navigateViaMenu();
    await contacts.delete(contactName);
    await contacts.expectRowHidden(contactName);
  });

  test('contact view shows hosted and attended events for LDAP users', async ({ page }) => {
    const contacts = new ContactsPage(page);

    await loginAsAdmin(page);
    await contacts.openContactFromIndex(testUsers.member.username);

    await expect(contacts.contactViewHeading).toBeVisible();
    await expect(contacts.attendedEventsHeading).toBeVisible();
    await expect(contacts.hostedEventsHeading).toBeVisible();
  });

  test('honorarium admin can view contacts but not open contacts index', async ({ page }) => {
    const contacts = new ContactsPage(page);

    await loginAs(
      page,
      testUsers.honorariumAdmin.username,
      testUsers.honorariumAdmin.password,
    );
    await expect(contacts.header.honorariaMenu).toBeVisible();
    await expect(contacts.header.adminMenu).toBeHidden();

    await contacts.openContactViewDirect(testUsers.member.username);
    await expect(contacts.contactViewHeading).toBeVisible();
    await expect(contacts.attendedEventsHeading).toBeVisible();
    await expect(contacts.hostedEventsHeading).toBeVisible();

    await contacts.navigateViaUrl();
    await contacts.expectIndexAccessDenied();
  });

  test('financial admin can view contacts but not open contacts index', async ({ page }) => {
    const contacts = new ContactsPage(page);

    await loginAs(
      page,
      testUsers.financialAdmin.username,
      testUsers.financialAdmin.password,
    );
    await expect(contacts.header.financialsMenu).toBeVisible();
    await expect(contacts.header.adminMenu).toBeHidden();

    await contacts.openContactViewDirect(testUsers.member.username);
    await expect(contacts.contactViewHeading).toBeVisible();
    await expect(contacts.attendedEventsHeading).toBeVisible();
    await expect(contacts.hostedEventsHeading).toBeVisible();

    await contacts.navigateViaUrl();
    await contacts.expectIndexAccessDenied();
  });
});
