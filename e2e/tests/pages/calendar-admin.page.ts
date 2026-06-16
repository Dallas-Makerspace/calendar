import { expect, type Locator, type Page } from '@playwright/test';

import { HeaderComponent } from '../components/header.component';

export class CalendarAdminPage {
  readonly header: HeaderComponent;

  constructor(private readonly page: Page) {
    this.header = new HeaderComponent(page.getByRole('navigation'));
  }

  get heading(): Locator {
    return this.page.getByRole('heading', { name: 'Calendar Super Admin' });
  }

  get successMessage(): Locator {
    return this.page.getByRole('alert');
  }

  private get allowHonorariaCheckbox(): Locator {
    return this.page.getByLabel('Allow Honoraria');
  }

  private get honorariumMessageInput(): Locator {
    return this.page.getByLabel('Message to be displayed');
  }

  async navigateViaMenu() {
    await this.header.openSuperCalendarAdminSettings();
  }

  private async readFormState() {
    return {
      allowHonoraria: await this.allowHonorariaCheckbox.isChecked(),
      message: await this.honorariumMessageInput.inputValue(),
    };
  }

  private async submitForm(state: { allowHonoraria: boolean; message: string }) {
    if (state.allowHonoraria) {
      await this.allowHonorariaCheckbox.check();
    } else {
      await this.allowHonorariaCheckbox.uncheck();
    }
    await this.honorariumMessageInput.fill(state.message);
    await this.save();
  }

  async setAllowHonoraria(enabled: boolean) {
    const state = await this.readFormState();
    await this.submitForm({ ...state, allowHonoraria: enabled });
  }

  async setHonorariumMessage(message: string) {
    const state = await this.readFormState();
    await this.submitForm({ ...state, message });
  }

  async save() {
    await this.page.getByRole('button', { name: 'Save Configuration Value' }).click();
    await expect(this.successMessage).toContainText('Updated successfully');
  }
}
