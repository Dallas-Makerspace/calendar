# Writing Playwright tests for DMS Calendar

**Implementation plan:** [plan/README.md](plan/README.md) — test checklist with one checkbox per test section.

Guide for authoring end-to-end tests in this repo. Based on [Playwright documentation](https://playwright.dev/docs/intro), the [Page Object Model pattern](https://playwright.dev/docs/pom), and common community guidance on POM + fixtures.

---

## Non‑negotiable rule: Page Object Model only

**Every UI interaction and locator lives in a page or component object.** Spec files never call `page.getByRole`, `page.getByLabel`, `page.locator`, or `page.goto` directly.

| Layer | Location | Allowed |
| --- | --- | --- |
| **Locators & actions** | `tests/pages/*.page.ts`, `tests/components/*.component.ts` | Define locators, navigation, user actions |
| **Test data** | `tests/data/*.ts` | Usernames, passwords, seed IDs — not selectors |
| **Specs** | `tests/*.spec.ts` | Instantiate page objects, call methods, `expect` on page object locators |
| **Fixtures** | `fixtures/*.ts` | Auth lifecycle, wiring page objects into tests |

```typescript
// ❌ Never in a spec file
await page.goto('/users/login');
await page.getByLabel('Username').fill('user1');

// ✅ Spec file
const loginPage = new LoginPage(page);
await loginPage.navigateViaUrl();
await loginPage.loginAsMember(testUsers.member.username, testUsers.member.password);
await expect(eventsIndex.heading).toBeVisible();
```

Codegen and prototyping are fine — but **move every locator into a page object before merging**. There are no one-off or “just this once” exceptions.

---

## Quick start

### Prerequisites

1. Start the full app stack (from repo root):

   ```bash
   ./setup.sh          # first time on Linux/macOS
   docker compose up
   ```

2. App should be available at **http://localhost:8000**.

3. Install and run tests:

   ```bash
   cd e2e
   npm ci
   npx playwright install chromium   # first time only, when not using Docker
   npm test
   ```

### Useful commands

| Command | Purpose |
| --- | --- |
| `npm test` | Run all tests headless |
| `npm run test:headed` | Run with visible browser |
| `npm run test:ui` | Interactive UI mode |
| `npx playwright test login.spec.ts` | Run one file |
| `npx playwright test -g "log in"` | Run tests matching title |
| `npx playwright test --debug` | Step through with Inspector |
| `npx playwright codegen http://localhost:8000` | Discover locators → copy into page objects |
| `npx playwright show-report` | Open last HTML report |

### Environment

| Variable | Default | Used for |
| --- | --- | --- |
| `BASE_URL` | `http://localhost:8000` | App root (`playwright.config.ts`) |
| `CI` | unset locally | Retries, GitHub reporter, stricter behavior |

### Test accounts

Defined in `tests/data/test-users.ts` (base users from `dms-ad-openldap/03-users.ldif`; role-only users from `.docker/e2e-ldap/`):

| Key | Username | Password | Notes |
| --- | --- | --- | --- |
| `testUsers.member` | `user1` | `password` | Regular member |
| `testUsers.admin` | `user2` | `password` | Full admin (all admin AD groups) |
| `testUsers.honorariumAdmin` | `honorariumadmin` | `password` | Honorarium Admins only |
| `testUsers.financialAdmin` | `financialadmin` | `password` | Financial Reporting only |

### Test data

| File | Use for |
| --- | --- |
| `tests/data/test-users.ts` | LDAP usernames/passwords — always shared |
| `tests/data/reference-data.ts` | Domain values referenced by **two or more** spec files |
| Inline in the spec | Values used by a **single** test — keep them in that test |

**Rule:** Do not add data to `reference-data.ts` unless a second spec needs the same value. Put test-specific names, emails, and seed references as `const` declarations at the top of the test (or inline) so readers see everything in one place.

```typescript
test('admin manages categories', async ({ page }) => {
  const categoryName = 'Test Category E2E';
  // ...
});
```

Never put locators or page object logic in `tests/data/`.

---

## Project layout

```
e2e/
├── PLAYWRIGHT.md
├── plan/                        # E2E implementation checklist (one checkbox per test)
│   ├── README.md
│   ├── layer-*.md
│   └── appendices/
├── playwright.config.ts
├── package.json
├── tests/
│   ├── *.spec.ts              # orchestration + assertions only
│   ├── data/
│   │   ├── test-users.ts      # LDAP credentials — shared across specs
│   │   └── reference-data.ts  # values shared by 2+ specs only
│   ├── pages/
│   │   ├── login.page.ts
│   │   └── events-index.page.ts
│   └── components/            # shared UI (navbar, modals)
│       └── header.component.ts
└── fixtures/                  # auth lifecycle + page object wiring
    └── test.ts
```

| Pattern | Location | Purpose |
| --- | --- | --- |
| **Page object** | `tests/pages/` | One class per logical page (`LoginPage`, `EventsIndexPage`) |
| **Component object** | `tests/components/` | UI shared across pages (navbar, dialogs) |
| **Test data** | `tests/data/` | Credentials and values reused across specs |
| **Fixture** | `fixtures/` | Setup/teardown (auth state, shared sessions) |
| **Spec** | `tests/*.spec.ts` | User journey; **no locators** |

---

## Page Object Model rules

| Concept | Rule |
| --- | --- |
| **Locators** | Private getter properties; public getters only when specs need them for `expect` |
| **Actions** | Public methods for user intent: `loginAsMember()`, `openCalendarView()` |
| **Navigation** | `navigateViaMenu()` or `navigateViaUrl()` on page objects — never `page.goto()` in specs |
| **Cross-page flows** | Methods return the **destination page object** (`loginAsMember()` → `EventsIndexPage`) |
| **Assertions** | In **specs**, against locators exposed by page objects (`expect(loginPage.heading)`) |
| **State** | Page objects are stateless — no cached DOM text or step tracking |
| **Constructor** | `Page` for pages, `Locator` for components — no credentials in constructor |
| **Naming** | `LoginPage` / `login.page.ts`; `HeaderComponent` / `header.component.ts` |

### Locator priority (inside page objects only)

This app uses CakePHP + Bootstrap without `data-testid` today. Define locators in page objects using:

1. `getByRole` — buttons, headings, links, navigation
2. `getByLabel` — form fields (`Username`, `Password`)
3. `getByText` / `getByPlaceholder` — when role/label are insufficient
4. `getByTestId` — after adding `data-testid` in PHP templates
5. CSS / XPath — last resort

Use chaining and filter inside page objects:

```typescript
// tests/pages/events-index.page.ts
private eventRow(title: string): Locator {
  return this.page.getByRole('row').filter({ hasText: title });
}
```

### Locator properties

Define locators as **getter properties**, not public `readonly` fields. Use `private get` for locators consumed only by actions; use `get` (public) when specs assert against them.

```typescript
export class LoginPage {
  constructor(private readonly page: Page) {}

  /** Public — specs call expect(loginPage.heading).toBeVisible() */
  get heading(): Locator {
    return this.page.getByRole('heading', { name: 'DMS Member Log In' });
  }

  /** Private — only used inside submitCredentials() */
  private get usernameInput(): Locator {
    return this.page.getByLabel('Username');
  }

  async submitCredentials(username: string, password: string) {
    await this.usernameInput.fill(username);
    // ...
  }
}
```

Add a new page object file **before** writing a spec that touches that page.

---

## Component objects

Use when the same UI region appears on multiple pages (e.g. `src/Template/Element/Header/default.ctp`).

```typescript
// tests/components/header.component.ts
import type { Locator } from '@playwright/test';

export class HeaderComponent {
  constructor(private readonly root: Locator) {}

  get logInLink(): Locator {
    return this.root.getByRole('link', { name: 'Log In' });
  }

  private get submitEventLink(): Locator {
    return this.root.getByRole('link', { name: 'Submit Event' });
  }

  async goToSubmitEvent() {
    await this.submitEventLink.click();
  }
}
```

Compose into page objects:

```typescript
// inside EventsIndexPage constructor
this.header = new HeaderComponent(page.getByRole('navigation'));
```

Specs call `eventsIndex.header.goToSubmitEvent()` — never reach into the DOM themselves.

---

## Spec examples (required style)

### Public page

```typescript
// tests/home.spec.ts
import { test, expect } from '@playwright/test';
import { EventsIndexPage } from './pages/events-index.page';

test('homepage displays upcoming events', async ({ page }) => {
  const eventsIndex = new EventsIndexPage(page);

  await eventsIndex.navigateViaUrl();
  await expect(eventsIndex.heading).toBeVisible();
});
```

### Authenticated flow

```typescript
// tests/login.spec.ts
import { test, expect } from '@playwright/test';
import { testUsers } from './data/test-users';
import { EventsIndexPage } from './pages/events-index.page';
import { LoginPage } from './pages/login.page';

test('member can log in with LDAP credentials', async ({ page }) => {
  const loginPage = new LoginPage(page);
  const eventsIndex = new EventsIndexPage(page);

  await loginPage.navigateViaUrl();
  await expect(loginPage.heading).toBeVisible();

  await loginPage.loginAsMember(
    testUsers.member.username,
    testUsers.member.password,
  );

  await expect(eventsIndex.heading).toBeVisible();
});
```

---

## Adding a new test (checklist)

1. **Pick a test** from [plan/README.md](plan/README.md) and check it off when done.
2. **Define the user goal** — e.g. “Member opens calendar view from home.”
2. **Identify pages touched** — list each screen in the flow.
3. **Create or extend page objects first** — add locators and actions for every new interaction.
4. **Add test data** — inline values in the spec when used by one test; add to `tests/data/` only when shared (see [Test data](#test-data))
5. **Write the spec** — page object methods + `expect` on page object locators only.
6. **Run locally** — `npm test`, then `--headed` or `--debug` on failure.
7. **Mark done** — `[x]` on the test line in `plan/layer-*.md`.
8. **Push** — CI runs via `.github/workflows/playwright.yml`.

### Spec template

```typescript
import { test, expect } from '@playwright/test';
import { SomePage } from './pages/some.page';

test.describe('Feature area', () => {
  test('role can do thing', async ({ page }) => {
    const somePage = new SomePage(page);

    await somePage.navigateViaMenu();
    await somePage.doSomething();

    await expect(somePage.resultLocator).toBeVisible();
  });
});
```

### Naming

- **Page files:** `events-calendar.page.ts` → `EventsCalendarPage`
- **Spec files:** `events-calendar.spec.ts`
- **Tests:** `'guest can browse upcoming events'`, `'admin can open honoraria pending list'`
- **Do not** prefix `test` or `test.describe` titles with plan section numbers (e.g. `1.1`, `§2.4`). Section IDs belong in [plan/](plan/README.md) checkboxes and the spec-file mapping table — spec titles should read as plain user-facing behavior.

---

## Authentication

Always go through `LoginPage` (or a fixture that uses it). Never duplicate login locators in specs or fixtures.

### Per-test login

```typescript
test.beforeEach(async ({ page }) => {
  const loginPage = new LoginPage(page);
  const eventsIndex = new EventsIndexPage(page);

  await loginPage.navigateViaUrl();
  await loginPage.loginAsMember(
    testUsers.member.username,
    testUsers.member.password,
  );
  await expect(eventsIndex.heading).toBeVisible();
});
```

### Setup project + `storageState` (many tests)

[Playwright auth docs](https://playwright.dev/docs/auth) — still uses page objects in the setup file:

```typescript
// tests/auth.setup.ts
import { test as setup, expect } from '@playwright/test';
import path from 'path';
import { testUsers } from './data/test-users';
import { EventsIndexPage } from './pages/events-index.page';
import { LoginPage } from './pages/login.page';

const memberAuth = path.join(__dirname, '../playwright/.auth/member.json');

setup('authenticate as member', async ({ page }) => {
  const loginPage = new LoginPage(page);
  const eventsIndex = new EventsIndexPage(page);

  await loginPage.navigateViaUrl();
  await loginPage.loginAsMember(
    testUsers.member.username,
    testUsers.member.password,
  );
  await expect(eventsIndex.heading).toBeVisible();
  await page.context().storageState({ path: memberAuth });
});
```

**Do not commit** `playwright/.auth/*.json`.

### Custom fixture

Fixtures wire page objects; they still must not contain locators:

```typescript
// fixtures/test.ts
import { test as base, expect } from '@playwright/test';
import { testUsers } from '../tests/data/test-users';
import { EventsIndexPage } from '../tests/pages/events-index.page';
import { LoginPage } from '../tests/pages/login.page';

export const test = base.extend<{ memberSession: void }>({
  memberSession: async ({ page }, use) => {
    const loginPage = new LoginPage(page);
    const eventsIndex = new EventsIndexPage(page);

    await loginPage.navigateViaUrl();
    await loginPage.loginAsMember(
      testUsers.member.username,
      testUsers.member.password,
    );
    await expect(eventsIndex.heading).toBeVisible();
    await use();
  },
});

export { expect } from '@playwright/test';
```

---

## Navigation

Prefer **menu clicks** via `navigateViaMenu()` when the nav exposes a path; use `navigateViaUrl()` for direct URLs (login, homepage, pages with no nav link, access-denial tests).

| Method | Use when |
| --- | --- |
| `navigateViaMenu()` | Header menus, top-level links |
| `navigateViaUrl()` | No menu link (`/logs`, `/honoraria`), cold-start entry (`/`, `/users/login`), access denial, or no list link to target |
| `HeaderComponent.goHome()` | Return to `/` via the brand link |

Implementation detail: both methods live on page objects; `page.goto()` is only used inside page object methods, never in specs.

---

## Core principles

### Test user-visible behavior

Page objects should target headings, labels, and link text — not `.btn-info` or `#username`.

### Web-first assertions (in specs)

```typescript
await expect(eventsIndex.heading).toBeVisible();   // ✅
expect(await eventsIndex.heading.isVisible()).toBe(true);  // ❌
```

Never use `page.waitForTimeout()`. Playwright auto-waits on actions and `expect`.

### Test isolation

Each test gets a fresh browser context. Tests must also be **self-contained on the server**:

- Create every entity the test needs; delete it (or restore global settings) before the test ends.
- Do not use `test.describe.configure({ mode: 'serial' })` to share mutable state between tests.
- Exception: serialize tests in the same file when they POST the same singleton form (Super Calendar Admin settings) and concurrent saves would race.
- Do not leave CRUD rows, config edits, or toggles for a later test or layer — later specs create their own data.
- Pre-test “remove leftovers” loops are only for recovering from an **interrupted run of the same test** (fail-fast, no `try/finally` restore on failure).

Shared LDAP users and seed rows shipped with the app (e.g. `Fiber Arts`, `Conference Room`) are fine; mutable test fixtures are not.

### No third-party assertions

Do not test `talk.dallasmakerspace.org` or other external URLs. Stub with [`page.route()`](https://playwright.dev/docs/network) inside a page object if needed.

---

## Public vs authenticated routes

| Area | Path (examples) | Auth |
| --- | --- | --- |
| Events list | `/` | Public |
| Calendar / RSS / embed | `/events/calendar`, `/events/feed/rss` | Public |
| Event view | `/events/view/:id` | Public |
| Login | `/users/login` | Public |
| Submit event | `/events/add` | Member (`testUsers.member`) |
| Admin / honoraria | `/events/honoraria/*` | Admin (`testUsers.admin`) |

---

## Debugging

| Tool | When |
| --- | --- |
| `npx playwright test --debug` | Step through locally |
| `npx playwright show-report` | HTML report after local run |
| Trace viewer | `trace: 'on-first-retry'` in config |
| **Playwright Tests** check (CI) | JUnit XML → GitHub Checks + job Summary via `dorny/test-reporter` |
| **playwright-junit** artifact | Raw `junit.xml` from each CI run |
| **docker-compose-logs** artifact | CI failure only |

On CI, open the workflow run → **Summary** tab, or the **Playwright Tests** check on the commit/PR, for pass/fail details per test. Locally, CI uses JUnit XML instead of the HTML reporter.

Use codegen to **find** locators, then paste them into the appropriate page object file.

---

## CI

- Workflow: `.github/workflows/playwright.yml`
- Stack: `docker compose up`; tests in `mcr.microsoft.com/playwright:v1.60.0-jammy`
- Keep `PLAYWRIGHT_VERSION` in sync with `@playwright/test` in `package.json`
- Requires `dms-ad-openldap` submodule for LDAP login
- CI publishes JUnit XML (`test-results/junit.xml`) as the **Playwright Tests** GitHub check

---

## Anti-patterns

| Do not | Do instead |
| --- | --- |
| `page.getByRole(...)` in a spec | Add locator to page object |
| `readonly foo: Locator` public fields | Private/public getter properties |
| `page.goto(...)` in a spec | `somePage.navigateViaMenu()` or `somePage.navigateViaUrl()` |
| Inline / one-off tests without POM | Create page object first |
| CSS / `#id` in page objects | `getByRole`, `getByLabel` |
| Credentials in spec files | `tests/data/test-users.ts` |
| Single-test CRUD names in `reference-data.ts` | Inline `const` in the spec |
| `serial` describe to pass state between tests | Self-contained setup + teardown in each test |
| `serial` without a singleton-form race reason | Only serialize when concurrent POSTs to one form would clobber fields |
| Leave test entities for a later layer | Create and delete inside the same test |
| `waitForTimeout()` | `expect(locator).toBeVisible()` |
| Giant page object for whole app | Split by page + components |
| Committing `playwright/.auth/*.json` | `.gitignore` |
| `test.only` in commits | Remove before push |

---

## Adding `data-testid` (when locators are ambiguous)

Add in CakePHP templates, define **once** in the page object:

```php
<?= $this->Form->button(__('Login'), ['data-testid' => 'login-submit']); ?>
```

```typescript
// login.page.ts constructor
private get loginButton(): Locator {
  return this.page.getByTestId('login-submit');
}
```

---

## Further reading

- [Page object models](https://playwright.dev/docs/pom)
- [Locators](https://playwright.dev/docs/locators)
- [Best practices](https://playwright.dev/docs/best-practices)
- [Fixtures](https://playwright.dev/docs/test-fixtures)
- [Authentication](https://playwright.dev/docs/auth)
- [Codegen](https://playwright.dev/docs/codegen)
