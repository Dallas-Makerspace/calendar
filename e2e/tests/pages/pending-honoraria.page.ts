import { expect, type Locator, type Page } from '@playwright/test';

import { HeaderComponent } from '../components/header.component';
import { ProcessRejectionPage } from './process-rejection.page';

export class PendingHonorariaPage {
  readonly header: HeaderComponent;

  constructor(private readonly page: Page) {
    this.header = new HeaderComponent(page.getByRole('navigation'));
  }

  get heading(): Locator {
    return this.page.getByRole('heading', { name: 'Pending Events Requesting Honorarium' });
  }

  async navigateViaMenu() {
    await this.header.openHonorariaLink('Pending');
  }

  async navigateViaUrl() {
    await this.page.goto('/events/honoraria/pending');
  }

  row(name: string): Locator {
    return this.page.getByRole('row').filter({ hasText: name });
  }

  async approveEvent(name: string) {
    this.page.once('dialog', (dialog) => dialog.accept());
    await this.row(name).getByRole('link', { name: 'Approve', exact: true }).click();
  }

  async openRejectForm(name: string): Promise<ProcessRejectionPage> {
    await this.row(name).getByRole('link', { name: 'Reject', exact: true }).click();
    return new ProcessRejectionPage(this.page);
  }
}
