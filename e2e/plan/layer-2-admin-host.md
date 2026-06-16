# Layer 2c — Host, admin & integrations

Host ops, export, cron, email, SSO (§2.20–2.28).

Part of the [E2E implementation plan](README.md). Implement tests bottom-up; check boxes when a Playwright spec covers the case.

Spec naming: `tests/<area>.spec.ts` · Page objects under `tests/pages/`.

---

> [!NOTE]
> **Tracking:** Check `[x]` when a Playwright spec covers the test section. Steps below are manual procedure reference — not separate tests.


### 2.20 Host — attendance marking
- [ ] 2.20 Host — attendance marking


**Steps:**

1. Set event `E2E Free Class` start time to **1 hour ago** (edit as admin or update DB).
2. As owner, open event view → **Attendance** tab.
3. Check **Attended** for confirmed registrants.
4. Click **Mark Attended**.


**Expected:** Within config 6 × 24h window after start — form submittable; attended flags saved.

**Steps (closed window):**

1. Set event start to **3 days ago**; reload attendance tab.


**Expected:** “Attendance is closed for this class.”

---


### 2.21 Host — AD group assignment
- [ ] 2.21 Host — AD group assignment


**Requires:** Approved `E2E Fulfills Prereq` with confirmed registrant (`user3`).

**Steps:**

1. As owner/admin, open event view → **AD Assignment** tab.
2. Read irreversibility warning.
3. Check **Assign AD** for a confirmed member with AD username.
4. Submit **Assign AD Group**.


**Expected:** Checkbox replaced with checkmark; user added to LDAP group (verify in phpLDAPadmin).

---


### 2.22 Cancel entire event
- [x] 2.22 Cancel entire event


**Steps:**

1. Create approved event with at least one confirmed registration.
2. As owner, **Edit Event → Cancel Event**; confirm.


**Expected:** Event status cancelled; banner on view; all registrations cancelled/refunded; cancellation emails `[INFRA: SparkPost]` / `[INFRA: Email-dev]` (`sendEventCancelled` per registrant).

**Steps (multipart cancellation):**

1. Cancel primary multipart event — verify continuation events also `cancelled` in DB.


**Steps (Twilio SMS on event cancel):** `[INFRA: Twilio]`

1. Register for event with valid US phone; check **Receive text message alerts** on registration form.
2. As owner/admin, cancel entire event (§2.22 step 1–2).


**Expected:** SMS sent via `EventsController::__sendText` with body “has been cancelled”; verify in Twilio console **or** app logs on failure.

**Note:** Cron cancellation/start reminder SMS paths are **commented out** in source — not `[INFRA: Twilio]` tests.

**Steps (admin cancel):**

1. As Calendar Admin, cancel another user's event via edit page.


**Expected:** Same cascade to registrations/refunds.

---


### 2.23 Events archive & honorarium lists
- [x] 2.23 Events archive & honorarium lists


**Steps:**

1. **Admin → Events Archive** — confirm all statuses listed; test date filter.
2. **Honoraria → Accepted / Rejected / Counts** — pages load; counts show monthly totals.


**Expected:** Lists paginate; filters work on archive.

**Steps (member lists — `Events/submitted`, `Events/attending`):**

1. As `user1`, **My Account → Hosting Events** — lists own events across statuses (`submitted` action).
2. **My Account → Attending Events** — lists events with registrations for current user (`attending` action).


**Expected:** Only user's hosted/attended events; links to view/edit/copy where applicable.

**Steps (upcoming honoraria counts accuracy):**

1. **Honoraria → Counts** — note per-month totals; cross-check against DB count of honorarium events in each month window (`upcomingHonoraria`).


**Steps (honoraria list sorting):**

1. On **Accepted** honoraria list, use column sort headers if present.


**Expected:** Default sort by event start DESC when no `?sort` query.

---


### 2.24 Financial export — `/events/export-honoraria`
- [x] 2.24 Financial export — `/events/export-honoraria`


**Steps:**

1. As `user2`, **Financials → Export Honoraria**.
2. Set date range including a **completed** honorarium event with 3+ attended registrants.
3. Click **List Honoraria**.
4. Change **Paid** dropdown; click **Save**.
5. Click **Export List as CSV**.


**Expected:** Table shows pay yes/no logic (>2 attendees); CSV downloads.

**Steps (authorization):**

1. As `user1`, open `/events/export-honoraria`.


**Expected:** Access denied.

**Steps (export without date range):**

1. Open `/events/export-honoraria` without submitting date form.


**Expected:** Page renders without honorarium table (`exportHonoraria` only sets data when GET dates present).

**Steps (≤2 attendees — “Don't Pay”):**

1. Include completed honorarium event with 0–2 attended registrants in date range.


**Expected:** Pay column shows “Don't Pay” / “Honoraria Not Met” in HTML and CSV.

**Steps (paid status enum in CSV):**

1. In CSV export, verify paid status labels: Not Paid, Paid, Pending, Missing Info, Denied, Paid by Script (`payTypes` array).


**Steps (pre-2017 old registrations):** `[INFRA: Pre-2017-data]`

1. Insert or identify completed honorarium event with `event_start` before `2017-01-01` and linked `old_registrations` rows (`status=confirmed`).
2. Include in export date range; run **Export List as CSV**.


**Expected:** Attendee count uses `old_registrations` branch when `event_start < oldCutoff` (`exportHonoraria` / `exportHonorariaCsv`).

**Steps (CSV direct URL):** `[INFRA: SparkPost]` not required

1. After HTML export with dates, open `/events/export-honoraria-csv?start_date=YYYY-MM-DD&end_date=YYYY-MM-DD` as `user2`.


**Expected:** CSV download with same rows as HTML table.

---


### 2.25 Standalone honoraria CRUD — `/honoraria`
- [x] 2.25 Standalone honoraria CRUD — `/honoraria`


No nav link. Requires **Calendar Admins** (`HonorariaController` inherits `AppController::isAuthorized` — not Honorarium Admins alone).

**Steps:**

1. As `user2`, open http://localhost:8000/honoraria.
2. View list; open one record; add/edit/delete test honorarium linked to event + committee.


**Expected:** CRUD pages functional (legacy admin UI).

**Steps (honorarium-admin-only denied):** `[INFRA: LDAP-setup]`

1. As user with **Honorarium Admins** only (no Calendar Admins), open `/honoraria`.


**Expected:** Access denied (unlike `/events/pending-honoraria` which uses Honorarium Admins auth).

---


### 2.26 Cron job — `/events/cron` `[INFRA: Time-manual]`
- [x] 2.26 Cron job — `/events/cron` `[INFRA: Time-manual]`


All cron tests require manual HTTP trigger and DB timestamp manipulation. Email sub-steps also need `[INFRA: SparkPost]` / `[INFRA: Email-dev]`.

**Steps (complete event + pending registration cleanup):**

1. Create approved event with `event_end` in the past (DB or edit).
2. Add pending registration on that event.
3. Open http://localhost:8000/events/cron in browser (or `curl`).
4. Refresh event/registrations in phpMyAdmin.


**Expected:**

- Event status → `completed`
- Pending registrations → `cancelled` with refund attempt `[INFRA: Braintree]` if paid
- `sendUnapprovedRegistrationCancelled` email per cancelled pending registration `[INFRA: SparkPost]` / `[INFRA: Email-dev]`

**Steps (auto-approve non-honorarium pending — config 1):** `[INFRA: Time-manual]`

1. Create pending event **without** honorarium; set `created` timestamp older than config 1 days in DB.
2. Run cron.


**Expected:** Event status → `approved` (no approval email — cron auto-approve does not call `sendEventApproved`).

**Steps (cancellation reminder emails):** `[INFRA: Time-manual]` + `[INFRA: SparkPost]` / `[INFRA: Email-dev]`

1. Set approved event `attendee_cancellation` to ~25 hours from now; `cancel_notification = 0`.
2. Run cron.


**Expected:** `sendCancellationReminder` to confirmed/pending registrants; `cancel_notification = 1`.

**Note:** Associated SMS in this cron block is **commented out** — do not expect Twilio delivery here.

**Steps (honorarium auto-approve — config 2):** `[INFRA: Time-manual]`

1. Age a pending honorarium event past config 2 days in DB; run cron.


**Expected:** Event auto-approved (`cron` honorarium branch).

**Steps (event-starting reminder):** `[INFRA: Time-manual]` + `[INFRA: SparkPost]` / `[INFRA: Email-dev]`

1. Set approved event `event_start` within ~24h; `reminder_notification=0`; run cron.


**Expected:** `sendEventStarting` emails; `reminder_notification = 1`.

**Note:** Start-reminder SMS in cron is **commented out**.

**Steps (skip multipart children on complete):**

1. Complete parent event — verify child `part_of_id` rows not double-processed.


**Steps (unauthenticated access):**

1. Confirm `/events/cron` is allowed without login (`beforeFilter` Auth allow).


---


### 2.27 Email verification checklist `[INFRA: SparkPost]` or `[INFRA: Email-dev]`
- [ ] 2.27 Email verification checklist `[INFRA: SparkPost]` or `[INFRA: Email-dev]`


Run §0.3 email check first. Confirm at least one delivery of each method triggered during Layer 2:

| Email method | Typical trigger | Section | Infra |
|---|---|---|---|
1. `sendEventSubmitted` — Member submits event
2. `sendEventApproved` — Admin approves
3. `sendEventRejected` — Admin rejects
4. `sendEventCancelled` — Event cancelled
5. `sendRegistrationConfirmation` — Auto-confirm registration
6. `sendRegistrationPending` — Approval-required registration
7. `sendRegistrationRequested` — Notifies host of pending RSVP
8. `sendRegistrationApproved` — Host accepts
9. `sendRegistrationRejected` — Host rejects
10. `sendRegistrationCancelled` — Registrant cancels
11. `sendRegistrationToInstructor` — Notify on registration
12. `sendCancellationToInstructor` — Notify on cancellation
13. `sendCancellationReminder` — Cron ~24h before cutoff
14. `sendEventStarting` — Cron ~24h before start
15. `sendUnapprovedRegistrationCancelled` — Cron completes event


**Steps (email transport failure — default stack without keys):**

1. With empty `SPARKPOST_APIKEY`, trigger any email action (e.g. submit event).


**Expected:** App does not crash; error logged silently (`EmailComponent::sendEmail` catch). Record **BLOCKED** for content verification, **PASS** for graceful failure.

---


### 2.28 SSO login `[INFRA: OIDC]`
- [ ] 2.28 SSO login `[INFRA: OIDC]`


Complete §0.3 Keycloak check first. Browser must resolve `keycloak` hostname.

**Steps (happy path):**

1. Log out; go to login page.
2. Click **SSO Login**.
3. Authenticate in Keycloak as LDAP user (e.g. `user1` / `password`).
4. Return to app via callback.


**Expected:** Logged in with `ssologin=true` in session; **SSO Profile** link in My Account menu (`ssoprofile` URL).

**Known defect:** Success redirect uses invalid `controller => '/'` — may 404 after auth until fixed; user may still be authenticated.

**Steps (OIDC failure):** `[INFRA: OIDC]`

1. Break OIDC config intentionally (wrong client secret in container env); attempt SSO.


**Expected:** Flash error; redirect to login (`oidcCallback` catch).

**Steps (SSO contact provisioning gap):** `[INFRA: OIDC]`

1. Log in via SSO as user with no existing `contacts` row.
2. Check phpMyAdmin `contacts` table and attempt **Submit Event**.


**Expected (document actual):** LDAP login runs `Auth.afterIdentify` → contact created; SSO sets user directly **without** `afterIdentify` — submit form may fail validation or lack `contact_id` until code fixed.

**Steps (SSO groups empty):** `[INFRA: OIDC]` + code fix

1. After SSO login, attempt action requiring AD group (e.g. admin menu).


**Expected:** Groups empty until `getGroupsFromUserInfo` returns `$result`; admin menus hidden even if user is in LDAP groups.

**Steps (SSO prerequisite refresh):** see §2.13.

---


### Layer 2 exit checklist
- [ ] Layer 2 exit checklist


1. At least 2 approved upcoming events on calendar
2. At least 1 completed event with attendance marked
3. Registrations in confirmed, pending, cancelled states exist
4. Note event IDs for Layer 3


---


