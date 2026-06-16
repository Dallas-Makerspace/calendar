import { test, expect } from '@playwright/test';

import { loginAsAdmin } from './helpers/admin-session';
import { EventsAddPage } from './pages/events-add.page';
import { RoomsPage } from './pages/rooms.page';

test.describe('Rooms admin CRUD', () => {
  test('admin manages rooms and exposes them on the event form', async ({ page }) => {
    const roomName = 'Test Room E2E';
    const exclusiveRoomName = 'Conference Room';
    const nonExclusiveRoomName = 'Common Area';
    const rooms = new RoomsPage(page);
    const eventsAdd = new EventsAddPage(page);

    await loginAsAdmin(page);
    await rooms.navigateViaMenu();
    await expect(rooms.heading).toBeVisible();

    // Remove leftovers from a previous run that failed before delete.
    if ((await rooms.row(roomName).count()) > 0) {
      await rooms.delete(roomName);
    }

    await rooms.expectExclusiveUse(exclusiveRoomName, true);
    await rooms.expectExclusiveUse(nonExclusiveRoomName, false);

    await rooms.addRoom(roomName, false);
    await rooms.expectRowVisible(roomName);
    await rooms.expectExclusiveUse(roomName, false);

    await rooms.openEdit(roomName);
    await rooms.saveRoom(roomName, true);
    await rooms.expectExclusiveUse(roomName, true);

    await eventsAdd.navigateViaMenu();
    await eventsAdd.expectRoomOption(roomName);
    await eventsAdd.expectRoomOption(exclusiveRoomName);

    await rooms.navigateViaMenu();
    await rooms.delete(roomName);
    await rooms.expectRowHidden(roomName);
  });
});
