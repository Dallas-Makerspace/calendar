# Layer 2a — Auth, events & files

Events, validation, approval, editing (§2.1–2.9).

Part of the [E2E implementation plan](README.md). Implement tests bottom-up; check boxes when a Playwright spec covers the case.

Spec naming: `tests/<area>.spec.ts` · Page objects under `tests/pages/`.

---

> [!NOTE]
> **Tracking:** Check `[x]` when a Playwright spec covers the test section. Steps below are manual procedure reference — not separate tests.


### 2.1 Member login & contact provisioning
- [x] 2.1 Member login & contact provisioning


**Steps:**

1. Log out; log in as `user1` / `password`.
2. Confirm **Submit Event** visible; no Admin menus.
3. As `user2`, open **Admin → Contacts** — find `user1` auto-created contact with AD username.


**Expected:** Member menus only; LDAP login creates/syncs contact record.

---


### 2.1b Blocked submission states (`Events/add.ctp`)
- [x] 2.1b Blocked submission states (`Events/add.ctp`)


**Steps (blacklisted):**

1. As `user2`, set `user1` contact **blacklisted** = true.
2. Log in as `user1` → **Submit Event**.


**Expected:** Red alert “event submission privileges have been revoked”; **no form** rendered.

**Steps (contact_error — incomplete AD profile):**

1. Create LDAP user missing `mail` or `telephonenumber` (via phpLDAPadmin) and log in.


**Expected:** Alert about AD sync failure; `contact_error` prevents form (`UsersController::afterIdentify`).

**Steps (cleanup):**

1. Remove blacklist from `user1` before continuing.


---


### 2.1c Non-sponsored submit path (`__constructPostForMarshal`)
- [x] 2.1c Non-sponsored submit path (`__constructPostForMarshal`)


**Steps:**

1. As `user1`, submit event with **Sponsored Event** unchecked.
2. In phpMyAdmin, verify event `contact_id` matches `user1`'s contact record (not a manual instructor contact).


**Expected:** Logged-in user's contact linked automatically; no instructor name/email fields required.

---


### 2.2 Event creation — free class (baseline)
- [x] 2.2 Event creation — free class (baseline)


**Steps:**

1. As `user1`, click **Submit Event**.
2. Fill **General:**


   - Title: `E2E Free Class`
   - Type: **Class** (radio)
   - Short description: `Short desc for E2E test`
   - Long description: `Long description body`
   - Start: at least **3 days** from today (respect config 4 minimum)
   - End: same day, 2 hours after start
3. **Facilities:** select a non-exclusive room; setup/teardown as needed.
4. **Attendance:** cost $0, free spaces `10`, paid spaces `0`, members-only off, approval off, age restriction none.
5. Select one optional category and one tool.
6. Submit.


**Expected:** Success flash (48-hour approval message); event in **My Account → Hosting Events** as `pending`.

**Expected (submission email):** `[INFRA: SparkPost]` or `[INFRA: Email-dev]` — `sendEventSubmitted` to submitter contact; verify in SparkPost/MailHog per §0.3.

---


### 2.3 Event validation (negative tests)
- [x] 2.3 Event validation (negative tests)


Repeat **Submit Event** with intentional errors:


**Start too soon:**

1. Set start date to tomorrow (less than config 4 days).
2. Submit.


**Expected:** Validation error on start date.


**End before start:**

1. Set end before start.
2. Submit.


**Expected:** “Event cannot end before it starts.”


**Exclusive room conflict:**

1. Note an approved/pending event using Conference Room (exclusive) at a specific time.
2. Submit new event with same room and overlapping booking window.


**Expected:** Conflict error with link to conflicting event.


**Tool conflict:**

1. Submit event using same tool and overlapping time as existing pending/approved event.


**Expected:** Error listing tools in use.


**Maximum lead time (config 5, default 190 days):**

1. Set event start beyond config 5 maximum horizon.


**Expected:** Validation error: events can only be scheduled N days in advance.


**Honorarium minimum lead (config 3, default 10 days):**

1. Submit honorarium class with start only 5 days out.


**Expected:** Error referencing honorarium lead time (config 3).


**Multipart session 2 room conflict:**

1. Submit multipart event where **second session** overlaps another booking in same exclusive room.


**Expected:** Error mentions “second date” / `class_number` wording.


**Age restriction values:**

1. Submit events with age restrictions 13, 16, and 21 (not only 18).


**Expected:** Each allowed value saves; invalid values rejected.


**Extend registration values:**

1. Create events with `extend_registration` of 15, 20, and 25 minutes; verify registration cutoff behavior differs from 0.


**Expected:** Only values in `[0, 15, 20, 25, 30]` accepted.


**Requires prerequisite → members-only (`__constructPostForMarshal`):**

1. Set **Requires prerequisite** without checking members-only manually.


**Expected:** Server sets `members_only=1` on save.

---


### 2.4 Event variants (create one of each for later tests)
- [ ] 2.4 Event variants (create one of each for later tests)


Create separate events as `user1`; approve in §2.6. Each row is a fixture event used by later specs.

1. Create & approve `E2E Approval Required` (`attendees_require_approval`)
2. Create & approve `E2E Members Only` (`members_only`)
3. Create & approve `E2E Age 18+` (age restriction 18 + advisories)
4. Create & approve `E2E Prereq Gated` (requires `3D Printer Basics`) `[INFRA: LDAP-setup]`
5. Create & approve `E2E Fulfills Prereq` (fulfills prerequisite)
6. Create & approve `E2E Multipart` (session 2 dates)
7. Create & approve `E2E Sponsored` (external instructor contact)
8. Create & approve `E2E Eventbrite` (Eventbrite URL)
9. Create & approve `E2E Honorarium` (honorarium + committee)
10. Create & approve `E2E Unlimited Capacity` (`free_spaces=0`, `paid_spaces=0`)
11. Create & approve `E2E Mixed Free/Paid`
12. Create & approve `E2E Notify Instructor` (both instructor notification flags)


**Variant verification (after approval):**

1. Eventbrite view — external link + disclaimer; no Braintree
2. Notify instructor — register; confirm email `[INFRA: SparkPost]` / `[INFRA: Email-dev]`
3. Unlimited capacity — no “X of Y spaces” on public view
4. Age 13/16/21 variants — repeat age gate (§2.14)
5. Offsite room — DMS address not shown on view/calendar/index


---


#### 2.4.1 Prerequisite positive path setup `[INFRA: LDAP-setup]`
- [ ] 2.4.1 Prerequisite positive path setup `[INFRA: LDAP-setup]`


**Steps:**

1. Open phpLDAPadmin (http://localhost:8888).
2. Log in as admin.
3. Find group `3D Printer Basics` under `ou=Security,ou=Groups,dc=dms,dc=local`.
4. Add `cn=user1,ou=Members,dc=dms,dc=local` as member.
5. Log out and log in as `user1` again (refresh LDAP groups in session).


**Expected:** `user1` can register for `E2E Prereq Gated` after event is approved.

---


### 2.5 File attachments on create
- [ ] 2.5 File attachments on create


**Steps:**

1. On any new event submit, upload:


   - File 1: public image (jpg/png)
   - File 2: private PDF (check **private**)
2. After approval, view event as anonymous — public image visible, private hidden.
3. View as owner — both visible.


**Expected:** Public/private file visibility rules enforced.

---


### 2.6 Event approval
- [x] 2.6 Event approval


**Steps (non-honorarium):**

1. Log in as `user2`.
2. **Admin → Pending Events**.
3. Find `E2E Free Class` (and other non-honorarium pending events).
4. Click **Approve**; confirm dialog.


**Expected:** Event status `approved`; approval email to submitter `[INFRA: SparkPost]` / `[INFRA: Email-dev]` (`sendEventApproved`).

**Steps (reject):**

1. Submit another throwaway event as `user1`.
2. As `user2`, **Pending Events → Reject**.
3. Enter rejection reason on process-rejection form; submit.


**Expected:** Status `rejected`; rejection email `[INFRA: SparkPost]` / `[INFRA: Email-dev]` (`sendEventRejected`); rejected banner on event view.

**Steps (reject multipart cascade):**

1. Reject pending **multipart** event — verify continuation rows (`part_of_id`) also `rejected` in DB.


**Steps (honorarium):**

1. **Honoraria → Pending** — approve `E2E Honorarium`.


**Expected:** Honorarium pending queue separate from Admin pending; approve works.

**Steps (approve multipart cascade):**

1. Approve pending multipart event — verify continuation rows set to `approved` in DB; approval email `[INFRA: SparkPost]` / `[INFRA: Email-dev]` sent once to submitter.


**Steps (honorarium approval auth split):** `[INFRA: LDAP-setup]`

1. Create pending event **with** honorarium as `user1`.
2. Temporarily remove `user2` from **Honorarium Admins** (phpLDAPadmin) keeping Calendar Admins — attempt approve from **Honoraria → Pending**.


**Expected:** With honorarium attached, only **Honorarium Admins** may approve (`EventsController::isAuthorized` + `hasHonorarium`).

**Steps (reject honorarium event):**

1. Reject a pending honorarium event via **process-rejection** form.
2. Open **Honoraria → Rejected** — event listed with reason and rejector.


**Steps (pending vs honorarium pending separation):**

1. Confirm non-honorarium pending events appear in **Admin → Pending Events** only, not Honoraria pending.


**Steps (processRejection page):**

1. From pending list, click **Reject** — lands on `/events/process-rejection/{id}` with reason field before final POST to `/events/reject/{id}`.


---


### 2.7 Event editing
- [x] 2.7 Event editing


**Steps (owner — limited):**

1. As `user1`, open approved `E2E Free Class` → **Edit Event**.
2. Change short description; save.


**Expected:** Success flash; change visible on view.

**Steps (admin — full):**

1. As `user2`, edit same event — change start/end times and booking times.
2. Save.


**Expected:** Admin can edit scheduling fields owner cannot.

**Steps (multipart edit):**

1. Edit `E2E Multipart` — verify continued session dates editable by admin.


**Steps (authorization):**

1. As `user1`, attempt `/events/edit/{other-users-event-id}`.


**Expected:** Access denied for another user's event.

**Steps (rejected event — no edit button):**

1. Open **rejected** event view as owner — **Edit Event** button must not appear (`view.ctp`).


**Steps (multipart child redirect):**

1. Note id of a continuation event (`part_of_id` set) in phpMyAdmin; browse `/events/view/{childId}` and `/events/edit/{childId}`.


**Expected:** Redirect to primary (parent) event view/edit.

**Steps (owner cannot edit schedule — behaviors removed):**

1. As owner, confirm event start/end fields read-only or not shown compared to admin edit.


**Steps (admin booking offset round-trip — `RelationalTimeBehavior`):**

1. As admin, edit cancellation window days and setup/teardown minutes; save and re-open edit form.


**Expected:** Display values match saved offsets (`convertToOffset` / `convertToFormat`).

**Steps (failed edit logging):**

1. Submit edit with invalid data (e.g. end before start) — check `/logs` for failure entry (`_afterUpdate` failure path).


**Steps (cancel event — §2.22 cross-ref):**

1. See §2.22 for full event cancellation with registrant emails/SMS.


---


### 2.8 Copy event
- [x] 2.8 Copy event


**Steps:**

1. As `user1`, open owned approved event → **Copy Event** (or `/events/add?copy={id}`).
2. Confirm form pre-filled except dates/status.
3. Set new future dates; submit.


**Expected:** New pending event; files copied; categories preserved.

**Steps (copy unauthorized):**

1. As `user1`, open `/events/add?copy={event-owned-by-user2}`.


**Expected:** No prefill from unauthorized event (copy guard in `EventsController::add`).

**Steps (multipart copy):**

1. Copy a multipart event — verify continued dates cleared on copy form; files copied via `files_to_copy`.


---


### 2.9 Delete event file
- [ ] 2.9 Delete event file


**Steps:**

1. As `user2`, edit event with attachments.
2. Delete one file via delete link (`/files/delete/{fileId}/{eventId}`).


**Expected:** File removed from event; disk file removed if no other references.

**Steps (authorization — owner cannot delete):**

1. As event owner (non-admin), attempt file delete URL.


**Expected:** Only Calendar Admins authorized (`FilesController::isAuthorized` → parent Calendar Admins).

**Steps (multipart sibling cleanup):**

1. Delete file on primary multipart event — verify same file row removed from continuation events (`beforeDelete` hook).


**Steps (oversized upload — `FilesTable` validation):**

1. Attempt upload exceeding form size limit.


**Expected:** Validation error “file is too large” or upload rejected.

---


