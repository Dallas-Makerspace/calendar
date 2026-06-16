import { expect, type Locator, type Page } from '@playwright/test';

import { RegistrationsViewPage } from './registrations-view.page';

export type RegistrationFormData = {
  name: string;
  email: string;
  phone: string;
  safetyConfirmation?: boolean;
  ageConfirmation?: boolean;
  type?: 'free' | 'paid';
};

export class RegistrationsEventPage {
  constructor(private readonly page: Page) {}

  get heading(): Locator {
    return this.page.getByRole('heading', { name: 'Event Registration' });
  }

  get membersOnlyNotice(): Locator {
    return this.page.getByText('This event is for DMS Members only');
  }

  get fullEventNotice(): Locator {
    return this.page.getByText('No available spaces!');
  }

  private get nameInput(): Locator {
    return this.page.getByLabel('Name');
  }

  private get emailInput(): Locator {
    return this.page.getByLabel('Email');
  }

  private get phoneInput(): Locator {
    return this.page.getByLabel('Phone');
  }

  private get safetyConfirmationCheckbox(): Locator {
    return this.page.getByLabel('I acknowledge the above considerations and warnings.');
  }

  private ageConfirmationCheckbox(age: number): Locator {
    return this.page.getByLabel(`I acknowledge that I am ${age} years old or older.`);
  }

  private get registrationTypeSelect(): Locator {
    return this.page.getByLabel('Registration Type');
  }

  private get submitButton(): Locator {
    return this.page.getByRole('button', { name: /Confirm Registration|Submit Registration for Approval/ });
  }

  async navigateViaUrl(eventId: number) {
    await this.page.goto(`/registrations/event/${eventId}`);
  }

  async expectPrerequisiteGate(prerequisiteName: string) {
    await expect(this.page.getByText(`requires completion of the ${prerequisiteName}`)).toBeVisible();
    await expect(this.submitButton).toBeHidden();
  }

  async expectMembersOnlyGate() {
    await expect(this.membersOnlyNotice).toBeVisible();
    await expect(this.submitButton).toBeHidden();
  }

  async fillRegistrationForm(data: RegistrationFormData) {
    await this.nameInput.fill(data.name);
    await this.emailInput.fill(data.email);
    await this.phoneInput.fill(data.phone);
    if (data.safetyConfirmation) {
      await this.safetyConfirmationCheckbox.check();
    }
    if (data.ageConfirmation) {
      await this.ageConfirmationCheckbox(18).check();
    }
    if (data.type) {
      const label = data.type === 'paid' ? /Paid Registration/ : 'Free Registration';
      await this.registrationTypeSelect.selectOption({ label });
    }
  }

  async submit(): Promise<RegistrationsViewPage> {
    await this.submitButton.click();
    return new RegistrationsViewPage(this.page);
  }

  async fillAndSubmit(data: RegistrationFormData): Promise<RegistrationsViewPage> {
    await this.fillRegistrationForm(data);
    return this.submit();
  }

  async expectDuplicateEmailError() {
    await expect(this.page.getByText('already associated with a registration')).toBeVisible();
  }

  async expectNamePrefilled() {
    await expect(this.nameInput).not.toHaveValue('');
  }
}
