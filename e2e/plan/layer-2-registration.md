# Layer 2b — Registration flows

Registration, payment, RSVP (§2.10–2.19).

Part of the [E2E implementation plan](README.md). Implement tests bottom-up; check boxes when a Playwright spec covers the case.

Spec naming: `tests/<area>.spec.ts` · Page objects under `tests/pages/`.

---

> [!NOTE]
> **Tracking:** Check `[x]` when a Playwright spec covers the test section. Steps below are manual procedure reference — not separate tests.


### 2.10 Free registration — auto confirm
- [x] 2.10 Free registration — auto confirm


**Steps:**

1. Log out (or use incognito).
2. Open approved `E2E Free Class` → **Register for this Event**.
3. Fill name, email, phone; submit.


**Expected:** Registration status **confirmed**; redirect to `/registrations/view/{id}` with success message.

**Expected (confirmation email):** `[INFRA: SparkPost]` / `[INFRA: Email-dev]` — `sendRegistrationConfirmation` to registrant email.

**Steps (logged-in member):**

1. Log in as `user3`; register for another approved free event.


**Expected:** Fields pre-filled from AD; `ad_username` stored.

**Steps (already registered redirect):**

1. As logged-in user, open `/registrations/event/{eventId}` for event already registered.


**Expected:** Redirect to existing `/registrations/view/{id}` (`RegistrationsController::event`).

**Steps (duplicate AD username per event):**

1. Attempt second registration with different email but same member account on same event.


**Expected:** Unique validation on `ad_username` scoped to `event_id`.

---


### 2.11 Registration — requires host approval
- [x] 2.11 Registration — requires host approval


**Steps:**

1. Open `E2E Approval Required` → register as guest or member.
2. Submit registration.


**Expected:** Status **pending**; pending message on registration view.

**Steps (host approve):**

1. Log in as event owner (`user1`) or `user2`.
2. Open event view → **Registered Attendees** tab.
3. Click **Approve** on pending registration.


**Expected:** Status **confirmed**; approval email `[INFRA: SparkPost]` / `[INFRA: Email-dev]` (`sendRegistrationApproved`).

**Steps (host reject):**

1. Register another user for same event (use different email).
2. **Reject** from registrations tab.


**Expected:** Status **rejected**; rejection email `[INFRA: SparkPost]` / `[INFRA: Email-dev]` (`sendRegistrationRejected`); refund message if paid `[INFRA: Braintree]`.

**Steps (host is event creator — `RegistrationsController::isAuthorized`):**

1. As `user1` (event owner, not admin), approve/reject pending registration on own event.


**Expected:** Owner authorized via `created_by` match without Calendar Admin role.

**Steps (instructor notification on approve path):**

1. For auto-confirm event with **Notify Instructor on Registrations**, register — verify instructor receives registration email `[INFRA: SparkPost]` / `[INFRA: Email-dev]` (`sendRegistrationToInstructor`).


**Steps (pending + requested emails):** `[INFRA: SparkPost]` / `[INFRA: Email-dev]`

1. Register for `E2E Approval Required` — verify **both** `sendRegistrationPending` (to registrant) and `sendRegistrationRequested` (to host) delivered.


---


### 2.12 Registration — members only
- [x] 2.12 Registration — members only


**Steps:**

1. Log out; open `E2E Members Only`.


**Expected:** Login prompt; no registration form.

**Steps:**

1. Log in as `user1`; register.


**Expected:** Form available; registration succeeds.

---


### 2.13 Registration — prerequisite gating
- [ ] 2.13 Registration — prerequisite gating


**Steps (negative — before LDAP setup):**

1. Remove `user1` from `3D Printer Basics` if added; re-login.
2. Open `E2E Prereq Gated`.


**Expected:** Warning about missing prerequisite; no form.

**Steps (positive — after §2.4.1):**

1. Add `user1` to group; re-login.
2. Register for `E2E Prereq Gated`.


**Expected:** Form available; registration succeeds.

**Steps (SSO prerequisite refresh):** `[INFRA: OIDC]` + code fix for `getGroupsFromUserInfo`

1. Log in via SSO; register for prereq-gated event after group added in LDAP mid-session.


**Expected:** `currentUserInGroup(..., forceRefreshGroups=true)` re-fetches groups via OIDC `updateGroups` (currently broken: missing `return` in `getGroupsFromUserInfo`).

---


### 2.14 Registration — advisories & age gate
- [x] 2.14 Registration — advisories & age gate


**Steps:**

1. Open `E2E Age 18+` → register.
2. Confirm **Safety/advisories** checkbox required.
3. Confirm **Age confirmation** checkbox required.
4. Submit without checking — browser validation or server error.
5. Check both; submit.


**Expected:** Registration succeeds only with acknowledgments.

---


### 2.15 Guest registration & edit_key
- [x] 2.15 Guest registration & edit_key


**Steps:**

1. Log out; register for free event with email `guest@test.local`.
2. Note URL includes `?edit_key=...` or copy link from confirmation page.
3. Open registration view in new session using full URL with `edit_key`.
4. **Cancel RSVP** before cutoff.


**Expected:** Guest can view/cancel without login via edit_key.

**Steps (registration view authorization — negative):**

1. Open `/registrations/view/{id}` without login and without valid `edit_key`.


**Expected:** Redirect away (`RegistrationsController::view` + `isOwnedBy` fails).

2. As different logged-in member (not registrant, not host, not admin), open same URL.


**Expected:** Access denied / redirect.

---


### 2.16 Registration — full event / duplicate email
- [x] 2.16 Registration — full event / duplicate email


**Steps (full — capped capacity):**

1. Create event with `free_spaces = 1`; approve.
2. Register two different emails.


**Expected:** Second registrant sees “no spaces available”.

**Steps (unlimited capacity — `getTotalSpaces` returns true):**

1. Create event with `free_spaces=0` and `paid_spaces=0`; approve.
2. Register multiple attendees.


**Expected:** No capacity limit; UI may omit “X of Y spaces” (`openSpaces` true).

**Steps (mixed free + paid pools):**

1. Use `E2E Mixed Free/Paid` — registration form shows type selector (free observer vs paid participant).
2. Fill free pool first; confirm paid still available and vice versa (`hasFreeSpaces` / `hasPaidSpaces`).


**Steps (duplicate email):**

1. Register same email twice on one event.


**Expected:** Validation error.

**Steps (race — full free pool at submit):**

1. With one free space left, open two registration tabs; submit both nearly simultaneously.


**Expected:** Second submit shows “no free spaces” flash; note known `$$registration` typo may cause PHP error instead of clean validation.

---


### 2.17 Registration — cutoff & extend
- [ ] 2.17 Registration — cutoff & extend


**Steps:**

1. Find event where cancellation cutoff has passed (or temporarily set `attendee_cancellation` in DB to past).
2. Attempt registration.


**Expected:** “Registration closed” on event view; `/registrations/event/{id}` redirects away.

**Steps (extend registration):**

1. Event with `extend_registration = 30` minutes — verify registration allowed briefly after nominal cutoff (if event still approved and before event start).


**Steps (accept vs reject cutoff asymmetry):**

1. After nominal cutoff but within extend window: host **Accept** pending registration — should succeed.
2. Host **Reject** after nominal cutoff — should fail (`reject` does not add extend minutes).


**Steps (wrong event state):**

1. Open `/registrations/event/{id}` for pending/unapproved event or multipart child event.


**Expected:** Redirect away (`RegistrationsController::event` guards).

---


### 2.18 Paid registration (Braintree) `[INFRA: Braintree]`
- [ ] 2.18 Paid registration (Braintree) `[INFRA: Braintree]`


Complete §0.3 Braintree check first. All steps in this section require sandbox credentials.

**Steps:**

1. Create approved paid event: cost `$5`, paid_spaces `5`, free_spaces `0`.
2. Open registration — Braintree drop-in UI visible.
3. Use Braintree sandbox test card (e.g. `4111111111111111`).
4. Complete registration.


**Expected:** `transaction_id` saved; status confirmed; charge in Braintree sandbox dashboard.

**Steps (refund on cancel):** `[INFRA: Braintree]` + `[INFRA: SparkPost]` / `[INFRA: Email-dev]`

1. Cancel registration before cutoff.


**Expected:** Braintree void/refund; `sendRegistrationCancelled` email.

**Steps (Braintree failure):** `[INFRA: Braintree]`

1. Submit paid registration with declined test card (e.g. Braintree sandbox decline nonce).


**Expected:** Flash error with gateway message; verify whether registration row is created (known issue: `stopPropagation` commented out).

**Steps (refund void vs settle — `RegistrationsTable::refund`):** `[INFRA: Braintree]`

1. Cancel paid registration while transaction still `submitted_for_settlement` — void path.
2. Cancel after settlement — refund path.


**Expected:** Appropriate Braintree void/refund API called.

**Steps (refund no-op):**

1. Cancel registration already `cancelled` or without `transaction_id`.


**Expected:** `refund()` returns without error (no-op branches).

**If Braintree not configured:** Record **BLOCKED** `[INFRA: Braintree]` — do not skip; return when credentials available.

---


### 2.19 Cancel RSVP
- [x] 2.19 Cancel RSVP


**Steps:**

1. As registrant (or guest with edit_key), open registration view.
2. Click **Cancel RSVP**; confirm dialog.


**Expected:** Status cancelled; cannot cancel again; refund message for paid events `[INFRA: Braintree]`.

**Steps (cancellation email):** `[INFRA: SparkPost]` / `[INFRA: Email-dev]`

1. After cancel — verify `sendRegistrationCancelled` delivered to registrant email.


**Steps (after cutoff):**

1. As `user1`, attempt cancel after cutoff.


**Expected:** Blocked with error flash.

**Steps (admin override):**

1. As `user2` (Calendar Admin), cancel same registration after cutoff.


**Expected:** Admin can cancel past cutoff.

**Steps (cancel instructor notification):**

1. Register for event with **Notify Instructor on Cancellations** enabled; cancel registration.


**Expected:** Instructor receives `sendCancellationToInstructor` email `[INFRA: SparkPost]` / `[INFRA: Email-dev]`.

**Steps (send_text on registration form):**

1. On `/registrations/event/{id}`, confirm **Receive text message alerts** checkbox is visible (`Registrations/event.ctp`).


**Expected:** `send_text` field rendered (only cancel-event SMS is active in code; cron SMS is commented out).

**Steps (already cancelled):**

1. Attempt cancel again on cancelled registration.


**Expected:** Cancel button not shown on registration view.

---


