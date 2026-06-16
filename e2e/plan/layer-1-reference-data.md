# Layer 1 — Reference data

Admin CRUD and auth menus (reference data leaves).

Part of the [E2E implementation plan](README.md).

---

> [!NOTE]
> **Tracking:** Check `[x]` when a Playwright spec covers the test section. Steps below are manual procedure reference — not separate tests.


### 1.1 LDAP login (admin access)
- [x] 1.1 LDAP login (admin access)


**Steps:**

1. Log out if logged in.
2. Go to http://localhost:8000/users/login.
3. Enter username `user2`, password `password`, click **Login**.


**Expected:** Redirect to event index; **Admin**, **Honoraria**, **Financials**, and **Super Calendar Admin** menus visible.

**Steps (negative):**

1. Log out.
2. Enter `user2` with wrong password.


**Expected:** Flash error: invalid username or password.

**Steps (email rejection):**

1. Enter an email address (e.g. `user2@dms.local`) as username.


**Expected:** Specific error telling you to use DMS username, not email.

**Steps (unknown user):**

1. Enter username `nonexistent`, password `password`.


**Expected:** Generic invalid username/password flash (LDAP user not found).

**Steps (disabled account):**

1. Log in as `disableduser` / `password` (UserAccountControl disabled in LDAP seed).


**Expected:** Login fails.

**Steps (login redirect):**

1. While logged out, open http://localhost:8000/events/add — redirect to login with `?redirect=/events/add`.
2. Log in successfully.


**Expected:** Lands on event add form after login.

**Steps (logout):**

1. **My Account → Logout**.


**Expected:** Session cleared; returned to public index; admin menus hidden.

---


### 1.2 Categories — `/categories`
- [x] 1.2 Categories — `/categories`


**Steps (index):**

1. **Admin → Categories**.
2. Confirm list shows optional categories only (not Class/Event types).


**Expected:** Categories with id > 2 listed alphabetically; no “Class” or “Event” type rows.

**Steps (add):**

1. Click **Add Category** (or `/categories/add`).
2. Enter name `Test Category E2E`, save.


**Expected:** Success flash; category appears in index.

**Steps (edit):**

1. Edit the new category; rename to `Test Category E2E Updated`, save.


**Expected:** Updated name in index.

**Steps (delete):**

1. Delete the test category.


**Expected:** Removed from index.

**Steps (downstream check):**

1. Go to **Submit Event** — optional categories multi-select includes categories from index.


**Expected:** New categories appear in event form after add; delete the test category when done.

---


### 1.3 Committees — `/committees`
- [x] 1.3 Committees — `/committees`


**Steps:**

1. **Admin → Committees**.
2. **Add** committee named `Test Committee E2E`.
3. **Edit** to `Test Committee E2E Updated`.
4. Open **Submit Event** → honorarium section (Class type) — confirm committee in dropdown.
5. **Delete** test committee (if unused).


**Expected:** Full CRUD works; committee appears in honorarium dropdown on event add form.

---


### 1.4 Prerequisites — `/prerequisites`
- [x] 1.4 Prerequisites — `/prerequisites`


**Steps:**

1. **Admin → Prerequisites**.
2. **Add** prerequisite: name `Test Prereq E2E`, AD group `Test Prereq E2E`.
3. **Edit** the AD group name if needed.
4. On event add form, confirm it appears in **Requires** and **Fulfills** dropdowns.
5. Delete the test prerequisite after confirming it on the event form.


**Expected:** CRUD works; dropdowns on event form update.

---


### 1.5 Rooms — `/rooms`
- [x] 1.5 Rooms — `/rooms`


**Steps:**

1. **Admin → Rooms**.
2. Note an **exclusive** room (e.g. Conference Room) and **non-exclusive** room (e.g. Common Area).
3. **Add** room `Test Room E2E`, exclusive = unchecked, save.
4. **Edit** — toggle exclusive on, save.
5. On event add form, confirm all rooms in **Select Room** dropdown.
6. Delete test room.


**Expected:** CRUD works; exclusive flag persists; rooms available on event form.

---


### 1.6 Tools — `/tools`
- [x] 1.6 Tools — `/tools`


**Steps:**

1. **Admin → Tools**.
2. **Add** tool `Test Tool E2E`.
3. **Edit** name to `Test Tool E2E Updated`.
4. On event add form, confirm tool in multi-select.
5. Delete test tool.


**Expected:** CRUD works; tools appear on event form and index **By Tool** filter (Layer 3).

---


### 1.7 System configuration — `/configurations`
- [x] 1.7 System configuration — `/configurations`


**Steps:**

1. **Admin → Configuration**.
2. Confirm six rows (ids 1–6): Automatic Approval, Honoraria Approval, Honoraria Booking Lead Time, Minimum Booking Lead Time, Maximum Booking Lead Time, Role Call Cutoff.
3. **Edit** config 4 (Minimum Booking Lead Time) — change value to `3`, save.
4. Open **Submit Event** — read help text on start date field.
5. Restore config 4 to `2`.


**Expected:** Index shows values in days; edits persist; lead-time validation uses config values on submit.

**Steps (config 7 not in this index):**

1. Confirm **Allow Honoraria** (config id 7) does **not** appear in this list — it is only on **Super Calendar Admin → Settings** (§1.8).


**Expected:** Six rows only (ids 1–6); honoraria toggle isolated to Super Admin.

---


### 1.8 Super Calendar Admin — `/calendar-admin/edit`
- [x] 1.8 Super Calendar Admin — `/calendar-admin/edit`


**Steps (honoraria toggle):**

1. **Super Calendar Admin → Settings**.
2. Uncheck **Allow Honoraria**, save.
3. Go to **Submit Event** (Class type) — honorarium checkbox should be disabled.
4. Return to settings; re-enable **Allow Honoraria**, save.


**Expected:** Honorarium request checkbox enabled/disabled with setting.

**Steps (custom message):**

1. Set **Message to be displayed** to `Test honorarium message`, save.
2. Open **Submit Event** — red message visible in honorarium section.
3. Clear message and save.


**Expected:** Message appears/disappears on add form.

---


### 1.9 Contacts — `/contacts`
- [x] 1.9 Contacts — `/contacts`


**Steps (external contact CRUD):**

1. **Admin → Contacts**.
2. **Add** contact: name `External Instructor`, email `external@test.local`, phone `555-0100`, W-9 on file unchecked, blacklisted unchecked.
3. **Edit** — set W-9 on file checked, save.
4. On **Submit Event**, check **Sponsored Event** — `External Instructor` appears in **Existing Instructors** dropdown.


**Expected:** External contact CRUD; appears in sponsored-event dropdown.

**Steps (duplicate email validation):**

1. **Add** another contact with the same email as `External Instructor`.


**Expected:** Validation error (unique email rule).

**Steps (Honorarium Admin view access):** `[INFRA: LDAP-setup]`

1. Create or use LDAP user in **Honorarium Admins** only (remove Calendar Admins temporarily, or use dedicated test user).
2. Log in as that user; open `/contacts/view/user1`.


**Expected:** Contact view allowed for Honorarium Admins / Financial Reporting per `ContactsController::isAuthorized`.

**Steps (blacklist blocks submit — Layer 2 prep):**

1. Set `user1` contact **blacklisted** = true; verify in §2.1b.


**Steps (cleanup):**

1. Delete `External Instructor` and any duplicate test contact after validation checks.
2. Remove blacklist from `user1` before Layer 2 member tests when testing §2.1b (each Layer 2 test restores its own mutations).


**Steps (contact view):**

1. Open `/contacts/view/user1`.


**Expected:** Hosted events and attended events sections render.

---


### 1.10 Audit logs — `/logs`
- [x] 1.10 Audit logs — `/logs`


No nav link — navigate directly.

**Steps:**

1. As `user2`, open http://localhost:8000/logs.
2. Confirm table of recent log entries (date, user, description, URL).
3. Filter: set **start date** and **end date** to today, submit.
4. Filter: enter `user2` in username field.
5. Filter: enter a word from a known log description in search.


**Expected:** Super Admin access only; filters narrow results; config edit from §1.7 may appear in logs.

**Steps (authorization):**

1. Log in as `user1`, open `/logs`.


**Expected:** Access denied or redirect (not Calendar Super Admin).

**Steps (logging rules — `AppController::customLog`):**

1. Edit a configuration (§1.7) — reload logs; entry should appear for the save action.
2. Browse **Admin → Categories** index and a public event **view** — confirm these do **not** create new log rows (`index`/`view` actions skipped).
3. Submit logs filter with only one of start/end date filled — document observed behavior.


---


### 1.11 Authorization & menu flags (`AppController::beforeRender`)
- [x] 1.11 Authorization & menu flags (`AppController::beforeRender`)


Maps AD groups to UI flags. Use accounts with **single** group membership where possible (adjust LDAP temporarily) or infer from combined `user2`.

| Flag / menu | Required AD group | Verify |
|---|---|---|
| Admin menu + CRUD | Calendar Admins | `user2` ✓; `user1` ✗ |
| Honoraria menu | Honorarium Admins | `user2` ✓ |
| Financials → Export | Financial Reporting | `user2` ✓ |
| Super Calendar Admin | Calendar Super Admins | `user2` ✓ |
| `canManageContacts` in header | Calendar Admins | Contacts link in Admin menu |
| Financial-only user | Financial Reporting without Calendar Admins | Export visible; Admin CRUD hidden |

**Steps (financial-only user matrix):** `[INFRA: LDAP-setup]`

1. Temporarily configure LDAP user (e.g. clone `user2` groups): **Financial Reporting** only — no Calendar/Honorarium/Super Admin groups.
2. Log in — confirm **Financials → Export Honoraria** visible; **Admin** menu hidden.
3. Open `/events/export-honoraria` — allowed. Open `/categories` — denied.
4. Restore original group membership after test.


**Steps (honorarium-admin-only user):** `[INFRA: LDAP-setup]`

1. User with **Honorarium Admins** only (no Calendar Admins): **Honoraria** menu works; `/events/pending` denied; `/honoraria` denied (uses parent `Calendar Admins` auth).


**Steps:**

1. As `user1`, confirm no Admin/Honoraria/Financials/Super Admin menus.
2. As `user2`, confirm all four dropdown menus present.
3. As `user3` (Members + Electronics committee only), confirm no admin menus.


**Expected:** Menu visibility matches group membership table in `AppController.php`.

---


### Layer 1 exit checklist
- [x] Layer 1 exit checklist

Each Layer 1 scenario is covered by a **self-contained** Playwright test: create the data the test needs, assert behavior, then delete entities or restore global settings (config, honoraria toggle, custom message) before the test finishes. Tests do not depend on execution order, on other tests in the same file, or on data left behind from earlier layers.

---


