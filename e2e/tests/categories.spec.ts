import { test, expect } from '@playwright/test';

import { loginAsAdmin } from './helpers/admin-session';
import { CategoriesPage } from './pages/categories.page';
import { EventsAddPage } from './pages/events-add.page';

test.describe('Categories admin CRUD', () => {
  test('admin manages categories and exposes them on the event form', async ({ page }) => {
    const categoryName = 'Test Category E2E';
    const categoryUpdatedName = 'Test Category E2E Updated';
    const seedCategoryName = 'Fiber Arts';
    const categories = new CategoriesPage(page);
    const eventsAdd = new EventsAddPage(page);

    await loginAsAdmin(page);
    await categories.navigateViaMenu();
    await expect(categories.heading).toBeVisible();

    // Remove leftovers from a previous run that failed before delete.
    for (const name of [categoryUpdatedName, categoryName]) {
      if ((await categories.row(name).count()) > 0) {
        await categories.delete(name);
      }
    }

    await categories.expectTypeCategoriesHidden();
    await categories.expectRowVisible(seedCategoryName);

    await categories.addCategory(categoryName);
    await categories.expectRowVisible(categoryName);

    await categories.openEdit(categoryName);
    await categories.saveCategory(categoryUpdatedName);
    await categories.expectRowVisible(categoryUpdatedName);

    await categories.delete(categoryUpdatedName);
    await categories.expectRowHidden(categoryUpdatedName);

    await categories.addCategory(categoryName);
    await categories.expectRowVisible(categoryName);

    await eventsAdd.navigateViaMenu();
    await eventsAdd.expectOptionalCategoryOption(categoryName);

    await categories.navigateViaMenu();
    await categories.delete(categoryName);
    await categories.expectRowHidden(categoryName);
  });
});
