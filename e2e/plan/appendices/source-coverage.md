# Appendix B — Source coverage index

[← E2E plan index](../README.md)

---


Maps every user-testable controller action and major auth/branch to manual test section(s). **✓** = explicit steps; **~** = infrastructure-dependent (see tag + Appendix F); **—** = dead or out of scope.

### Controllers — actions

| Source | Test section | Notes |
|---|---|---|
| `UsersController::login` | §1.1, §2.1, §3.6 | Negative paths, redirect, email-as-username |
| `UsersController::logout` | §1.1, §3.6 | |
| `UsersController::ssoLogin` | §2.28 | `[INFRA: OIDC]` |
| `UsersController::oidcCallback` | §2.28 | Failure path; success redirect bug |
| `UsersController::afterIdentify` | §2.1b | Contact sync, blacklist, `contact_error` |
| `CategoriesController::index` (+ CRUD via Crud) | §1.2 | |
| `CommitteesController::index` (+ CRUD) | §1.3 | |
| `PrerequisitesController::index` (+ CRUD) | §1.4 | |
| `RoomsController::index` (+ CRUD) | §1.5 | Exclusive flag |
| `ToolsController::index` (+ CRUD) | §1.6 | |
| `ConfigurationsController::index` (+ edit) | §1.7 | Config 7 excluded from index |
| `CalendarAdminController::edit` | §1.8 | Honoraria toggle + custom message |
| `ContactsController::index/view` (+ CRUD) | §1.9 | Blacklist, W-9 flag, duplicate email |
| `LogsController::index` | §1.10 | Filters, auth, `customLog` skip rules |
| `FilesController::delete` | §2.9 | Auth, multipart cleanup, size validation |
| `EventsController::add` | §2.2–2.5, §2.3 | Copy param, sponsored, variants |
| `EventsController::edit` | §2.7 | Owner vs admin, multipart, logging |
| `EventsController::view` | §2.7, §3.4 | Child redirect, registration states |
| `EventsController::cancel` | §2.22 | Multipart, SMS, refunds |
| `EventsController::approve/reject/processRejection` | §2.6 | Honorarium auth split |
| `EventsController::pending` | §2.6 | Calendar Admins only |
| `EventsController::pendingHonoraria` etc. | §2.6, §2.23 | Honorarium Admins only |
| `EventsController::all` | §2.23 | Date filter, all statuses |
| `EventsController::submitted/attending` | §2.23 | Member lists |
| `EventsController::attendance` | §2.20 | Config 6 window |
| `EventsController::assignments` | §2.21 | LDAP attach, irreversible |
| `EventsController::exportHonoraria/Csv` | §2.24 | Finance auth, pay enums, ≤2 attendees |
| `EventsController::cron` | §2.26 | Complete, auto-approve, reminders |
| `EventsController::index/embed/calendar` | §3.1–3.3 | Filters, completed on calendar only |
| `EventsController::feed/ics/eventsJson` | §3.5 | All feed types, newJson -1 |
| `RegistrationsController::event` | §2.10–2.17 | Guards, Braintree, prereq, pools |
| `RegistrationsController::view/cancel` | §2.15, §2.19 | edit_key, cutoff, refund |
| `RegistrationsController::accept/reject` | §2.11, §2.17 | Host auth, cutoff asymmetry |
| `HonorariaController::index/view/add/edit/delete` | §2.25 | No nav link |
| `AppController::beforeRender` | §1.11 | Menu flags |
| `AppController::customLog` | §1.10 | |
| `AppController::currentUserInGroup` | §2.13 | SSO refresh (broken) |
| `EmailComponent::*` | §2.27 | All send* methods |
| `PagesController::display` | — | Route disabled |
| `W9sController` | — | Nav link only; no controller |

### `EventsController::isAuthorized` branches

| Branch | Test section |
|---|---|
| `add/submitted/attending` → logged in | §2.1, §2.23 |
| `attendance/assignments/cancel/edit` → owner or admin | §2.7, §2.20–2.22 |
| `pending/all` → Calendar Admins | §2.6, §2.23 |
| Honoraria list actions → Honorarium Admins | §2.6, §2.23 |
| Export → Financial Reporting | §2.24, §1.11 |
| `approve/reject` + `hasHonorarium` → Honorarium Admins | §2.6 |
| `approve/reject` without honorarium → Calendar Admins | §2.6 |

### `RegistrationsController::isAuthorized` branches

| Branch | Test section |
|---|---|
| Host (`created_by`) accept/cancel/reject/view | §2.11, §2.19 |
| `isOwnedBy` ad_username | §2.10 |
| `isOwnedBy` edit_key | §2.15 |
| Calendar Admin override | §2.19 |

### Event lifecycle hooks (indirect)

| Hook | Test section |
|---|---|
| `_beforeCreate/_beforeUpdate` marshal | §2.1c, §2.3 |
| `_afterCreate/_afterUpdate` emails, multipart | §2.4, §2.6, §2.7 |
| `_applyAddress` | §2.4, §3.2, §3.4 |
| `_filterContent` | §3.1 |
| `RelationalTimeBehavior` | §2.7 |
| `FilesTable` beforeDelete multipart | §2.9 |

### Model validation & rules (user-visible)

| Rule | Test section |
|---|---|
| `EventsTable` start/end/lead/honorarium lead | §2.3 |
| `EventsTable` room/tool conflict (`buildRules`) | §2.3 |
| `EventsTable` age_restriction enum | §2.3, §2.14 |
| `EventsTable` extend_registration enum | §2.3, §2.17 |
| `EventsTable` requires_prerequisite → members_only | §2.3 |
| `RegistrationsTable` unique email per event | §2.16 |
| `RegistrationsTable` unique ad_username per event | §2.10 |
| `ContactsTable` unique email | §1.9 |
| `FilesTable` file size | §2.9 |
| `HonorariaTable` CRUD validation | §2.25 |

### Coverage summary

| Category | Count | Status |
|---|---|---|
| Controller actions (excl. tests/dead) | 52 | All mapped above |
| Auth branches (`isAuthorized`) | 18 | §1.11, §2.6–2.7, §2.19, §2.24 |
| Email send methods | 15 | §2.27 table |
| Known code defects | 6 | §Known code issues table |
| Dead / unreachable UI | 5 | Appendix D |

---

