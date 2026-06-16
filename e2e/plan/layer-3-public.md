# Layer 3 — Public calendar

Anonymous and navigation flows.

Part of the [E2E implementation plan](README.md).

---

> [!NOTE]
> **Tracking:** Check `[x]` when a Playwright spec covers the test section. Steps below are manual procedure reference — not separate tests.


### 3.1 Event index — `/`
- [x] 3.1 Event index — `/`


**Steps:**

1. Log out; open http://localhost:8000.
2. Confirm only **approved upcoming** events listed.
3. Click **By Type → Class** — list filters.
4. Click **By Type → Event** — list filters.
5. **By Category** — pick a category; list narrows.
6. **By Tool** — pick a tool.
7. **By Room** — pick a room.
8. Apply **type + category** together.


**Expected:** Each filter reduces list correctly; combined type+category uses AND logic.

**Steps (links):**

1. Click event title or “More Info and RSVP »”.


**Expected:** Navigates to event view.

**Steps (RSS):**

1. Click RSS icon — feed URL includes active filter query params.


---


### 3.2 Calendar view — `/events/calendar`
- [ ] 3.2 Calendar view — `/events/calendar`


**Steps:**

1. Click **Calendar View** from index.
2. Confirm month grid shows events on correct days.
3. Navigate to `/events/calendar/2026/6/1` (adjust year/month).
4. Open daily view `/events/calendar/2026/6/15`.
5. Apply `?category={id}` filter on calendar URL.


**Expected:** Month and day views render; approved + completed events in range; filters apply.

**Steps (completed events on calendar only):**

1. Open `/events/calendar` for month containing a **completed** event (from §2.20 / cron).


**Expected:** Completed event appears on calendar (calendar includes `approved` + `completed`; index does not).

**Steps (highlight flag — `highlight` template var):**

1. Navigate to `/events/calendar/{currentYear}/{currentMonth}/1` — today’s date highlighted in month grid.
2. Navigate to a different month/year (e.g. `/events/calendar/2027/1/1`).


**Expected:** `highlight=false`; today styling not applied on non-current month view.

**Steps (invalid / edge date params):**

1. Open `/events/calendar/2015/6/1` (year below 2016 minimum).


**Expected:** Year param ignored; falls back to current year behavior (no crash).
2. Open `/events/calendar/2026/13/1` or `/events/calendar/2026/6/32`.


**Expected:** Invalid month/day ignored; page still renders.

**Steps (offsite address on calendar list):**

1. Filter calendar to month with **Offsite** event — no DMS address appended to location line.


---


### 3.3 Embed view — `/events/embed`
- [ ] 3.3 Embed view — `/events/embed`


**Steps:**

1. Open http://localhost:8000/events/embed.
2. Confirm no site header/footer (minimal chrome).
3. Apply `?tool={id}` filter.


**Expected:** Embeddable list; filters work.

---


### 3.4 Event detail page — `/events/view/:id`
- [ ] 3.4 Event detail page — `/events/view/:id`


**Steps (content):**

1. Open an approved upcoming event.
2. Verify: title, when/where/what/host, categories (clickable filters), tools, long description, advisories.
3. Multipart event: all session dates listed.
4. Download public attachment; view image tabs.


**Expected:** All public fields render; URLs in description become links.

**Steps (registration states):**

1. **Before cutoff, spaces available:** green Register button.
2. **Already registered (logged in):** “View Your Registration”.
3. **Full event:** “no more spaces” alert.
4. **Pending event:** pending approval message.
5. **Past cutoff:** registration closed.
6. **Cancelled event:** red cancellation banner.


**Steps (add to calendar):**

1. Click Google Calendar icon — opens Google with prefilled event.
2. Click Outlook icon — opens Outlook compose.
3. Click Apple/iCal — downloads `.ics`.


**Steps (private files):**

1. As anonymous, open event with private attachment — private file not listed.
2. Log in as owner — private file visible.


**Steps (instructor controls):**

1. As anonymous — no Instructor Controls section.
2. As owner — Registered Attendees / Attendance / AD Assignment tabs visible.


**Steps (Eventbrite paid type):**

1. Open approved `E2E Eventbrite` as anonymous.


**Expected:** “Paid through Eventbrite” cost line; external registration link; Eventbrite disclaimer; **no** DMS Register button / Braintree.

**Steps (pending / rejected / completed public view):**

1. Open **pending** event by direct URL `/events/view/{pendingId}`.


**Expected:** “pending approval” info alert; no registration form.
2. Open **rejected** event — rejection banner; **Edit Event** hidden for owner (§2.7).
3. Open **completed** event — no registration button; attendance/history may still show for owner.


**Steps (offsite address):**

1. Open offsite-room event — location shows room name only; no “1825 Monetary Ln” address block.


**Steps (space count on index vs detail):**

1. On index, note registration count for capped event; open detail — counts consistent with `openSpaces` / `hasOpenSpaces`.


---


### 3.5 Feeds & ICS API
- [ ] 3.5 Feeds & ICS API


**Steps:**

| URL | Check |
|---|---|
| `/events/feed/vcal` | Valid iCal; future approved events |
| `/events/feed/rss` | XML; descriptions contain HTML |
| `/events/feed/json` | JSON feed body |
| `/events/feed/newJson` | JSON array; `Access-Control-Allow-Origin: *` |
| `/events/ics/{eventId}` | Single-event `.ics` download |


**Filter test:**

1. `/events/feed/rss?category={id}&tool={id}` — feed contents match filters.


**Expected:** Private attachments excluded from feeds.

**Steps (unknown feedtype defaults to RSS):**

1. Open `/events/feed/atom` or `/events/feed/unknown`.


**Expected:** Valid RSS XML (default branch in `feed()` else block).

**Steps (newJson unlimited capacity — `-1` sentinel):**

1. Open `/events/feed/newJson`; find `E2E Unlimited Capacity` entry.


**Expected:** `totalSpaces` and `filledSpaces` are `-1` when unlimited (`eventsJson`).

**Steps (newJson file/image payloads):**

1. Confirm public images appear under `images[]`; non-image public files under `files[]`; private files omitted.


**Steps (feed filter title addon):**

1. `/events/feed/rss?category={id}` — channel title/description include filter subject names.


**Steps (ics single event):**

1. `/events/ics/{eventId}` for offsite event — verify LOCATION/address handling matches view.


---


### 3.6 Navigation & session UX
- [ ] 3.6 Navigation & session UX


**Anonymous:**

1. **Submit Event** → redirects to login with `?redirect=/events/add`.
2. **DMS Login** from any page preserves return URL.


**Logged in (`user1`):**

1. **Submit Event** → add form directly.
2. **My Account → Hosting Events / Attending Events** — lists populate.


**Logged in (`user2`):**

1. Admin, Honoraria, Financials, Super Calendar Admin menus visible.


**Development:**

1. With `DEBUG=true`, navbar has red background.


---


### 3.7 End-to-end smoke (all layers)
- [ ] 3.7 End-to-end smoke (all layers)


**Steps:**

1. **Layer 1:** Admin adds category `Smoke Category` and room `Smoke Room`.
2. **Layer 2:** `user1` submits class using those references; `user2` approves.
3. **Layer 3:** Anonymous user filters index by `Smoke Category` — event appears.
4. Anonymous opens event detail — register for free event.
5. Index shows decreased space count (if capped).
6. `/events/feed/rss` includes the event.
7. `/events/calendar` shows event on correct day.


**Expected:** Full path from admin setup → member submit → public discovery → registration without errors.

---


