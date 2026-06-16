import { test, expect } from '@playwright/test';

import { loginAsAdmin } from './helpers/admin-session';
import { EventsAddPage } from './pages/events-add.page';
import { ToolsPage } from './pages/tools.page';

test.describe('Tools admin CRUD', () => {
  test('admin manages tools and exposes them on the event form', async ({ page }) => {
    const toolName = 'Test Tool E2E';
    const toolUpdatedName = 'Test Tool E2E Updated';
    const tools = new ToolsPage(page);
    const eventsAdd = new EventsAddPage(page);

    await loginAsAdmin(page);
    await tools.navigateViaMenu();
    await expect(tools.heading).toBeVisible();

    // Remove leftovers from a previous run that failed before delete.
    for (const name of [toolUpdatedName, toolName]) {
      if ((await tools.row(name).count()) > 0) {
        await tools.delete(name);
      }
    }

    await tools.addTool(toolName);
    await tools.expectRowVisible(toolName);

    await tools.openEdit(toolName);
    await tools.saveTool(toolUpdatedName);
    await tools.expectRowVisible(toolUpdatedName);

    await eventsAdd.navigateViaMenu();
    await eventsAdd.expectToolOption(toolUpdatedName);

    await tools.navigateViaMenu();
    await tools.delete(toolUpdatedName);
    await tools.expectRowHidden(toolUpdatedName);
  });
});
