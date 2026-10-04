# Prerequisites Management API — v1 Design

Status: **implemented** (branch `feature/prerequisites-api`). The checklist in
§9 items 1–9 is built, with the fix-pass refinements noted inline
(case-insensitive duplicate pre-check in the migration, MissingRouteException →
404 in the renderer, model-rule-only AD check, duplicate-race 422, delete-race
409 re-check, locked-down entity mass assignment).

Scope (confirmed): the API manages **prerequisite records** (the `name` ↔ `ad_group`
mapping in the `prerequisites` table). It does **not** create, rename, or delete
actual AD group objects. On create/update it must validate that `ad_group`
exists in AD via a read-only LDAP lookup and reject with a clear error otherwise.

## 1. What exists today (verified in code)

- Table `prerequisites`: `id`, `name` (80), `ad_group` (100), `created`, `modified`.
  Validation requires `name` and `ad_group`; no uniqueness rules, no delete rules
  (`src/Model/Table/PrerequisitesTable.php`).
- `src/Controller/PrerequisitesController.php` uses the friendsofcake/crud plugin
  (`Index/Add/Edit/Delete`, `View` disabled) with a `beforePaginate` hook ordering
  by name.
- `AppController` loads `Crud.Crud` + `RequestHandler` and its `beforeRender`
  sets `_serialize = true` whenever the response type is JSON/XML. So today a
  request with `Accept: application/json` to `/prerequisites` *does* emit JSON —
  but the payload shape is whatever the Crud plugin sets (`prerequisites`,
  `success`, `message` view vars), with no stable contract, no pagination meta,
  and no machine-auth story. The v1 API below replaces that accident with an
  explicit contract.
- Auth: session-based AD login (`Auth` component + `AdAuthenticate` + LdapRecord);
  authorization in `isAuthorized()` = membership in the AD group
  **"Calendar Admins"**. OIDC via Keycloak exists (`src/Auth/OpenIDConnectService.php`)
  but is a session-login path, not machine auth.
- `EventsTable` has `belongsTo('FulfillsPrerequisites')` /
  `belongsTo('RequiresPrerequisites')` on `fulfills_prerequisite_id` /
  `requires_prerequisite_id`. `EventsController` looks up the AD group by
  `cn = ad_group` at event close and attaches members — so `ad_group` strings must
  be real AD group CNs.
- `AppController::customLog()` writes admin actions to the `Logs` table
  (description, user = `samaccountname`, date_time, ip_address, url, controller,
  action). It skips `index`/`view` and anonymous users.
- Seed (`config/Seeds/PrerequisitesSeed.php`) currently has `ad_group == name`
  for all rows. **Design decision: they may diverge.** `name` is the display
  label; `ad_group` is the authoritative AD group `cn` consumed by
  `EventsController` (event close) and the `func-dms-prereq` serverless function.
  Renaming the display label must not break group resolution, so they are
  independent fields. No schema change needed; keep seed as-is.

## 2. Routing (versioned)

Add to `config/routes.php`:

```php
Router::prefix('api/v1', function (RouteBuilder $routes) {
    $routes->setExtensions(['json']);
    $routes->resources('Prerequisites', ['only' => ['index', 'view', 'create', 'update', 'delete']]);
});
```

- Maps `GET/POST /api/v1/prerequisites` and `GET/PUT/PATCH/DELETE /api/v1/prerequisites/:id`
  to `App\Controller\Api\V1\PrerequisitesController`. Note `view` is included
  even though the HTML controller disables it — the API is a record API.
- `DashedRoute` default + prefix inflects `Prerequisites` → `/api/v1/prerequisites`.
- JSON is the only served type (`setExtensions(['json'])`). An unknown
  extension (e.g. `/api/v1/prerequisites.xml`) matches no route → 404 via the
  renderer's `MissingRouteException` mapping; a non-JSON `Accept` header →
  406 (`ApiAppController::assertAcceptsJson()`).
- Versioning strategy: URL-prefix versioning. Additive, backwards-compatible
  changes land in `v1`. A breaking change gets a new `Api\V2` namespace and
  route prefix; `v1` keeps working. The contract in §7 is frozen for `v1`.

## 3. Extensibility pattern (future APIs reuse this)

Shared base controller: `src/Controller/Api/ApiAppController.php` extends
`Cake\Controller\Controller` (NOT `AppController` — we don't want Crud listeners,
menu view vars, or `beforeRender` auto-serialize of the HTML side).

`ApiAppController` provides:

- Components: `RequestHandler` only (no `Crud`, no `Flash`, no `Security` form
  tampering — APIs don't render forms).
- `beforeFilter`: resolve identity (session first, then API token — §4), enforce
  `isApiAuthorized()` → 401/403 with the error envelope.
- `beforeRender`: force JSON view, apply `_serialize`; on exception render the
  error envelope (§7).
- Helpers: `apiSuccess($data, $status = 200)`, `apiError($status, $title, $detail, $pointer = null)`,
  `apiLog($description)` for the audit pattern (§6).
- Pagination convention (§7): `?page=` / `?per_page=` (default 50, max 200).

Future resources (e.g. `/api/v1/events`) add `src/Controller/Api/V1/XController.php`
extending `ApiAppController`, plus one `$routes->resources(...)` line. Shared
auth, envelope, pagination, and logging come free.

Implementation notes (added during build — without these the API does not work
in production):
- `Application::middleware()` uses
  `App\Error\Middleware\ApiAwareErrorHandlerMiddleware`, which picks
  `ApiExceptionRenderer` for `/api/*` and the default HTML renderer otherwise.
  The base middleware resolves the renderer class from its construction-time
  config, so the `Configure` value written by `ApiAppController::initialize()`
  would never apply to exceptions caught by the middleware.
- `CsrfProtectionMiddleware` exempts `/api/*` via `whitelistCallback()` **only
  for requests carrying an `Authorization` header** (machine clients). API
  requests authenticated by the admin session cookie keep CSRF enforcement —
  a blanket `/api/*` exemption would let a malicious page fire API mutations
  with an admin's session cookie. (The middleware ordering keeps
  `ApiAwareErrorHandlerMiddleware` outermost, so CSRF failures on `/api/*`
  still render as the JSON error envelope.)
- There is no `BodyParserMiddleware` in the app stack, so the v1 controller
  decodes the JSON request body itself and answers 400 on malformed JSON.

## 4. Authentication & authorization

Authorization for this API: **admin-only** — the caller must be a member of the
AD group `Calendar Admins`, same rule as the HTML controller's `isAuthorized()`.

Two auth mechanisms, both funnel into the same authorization check:

1. **Session auth (browser admins).** Existing AD login via the `Auth` component
   keeps working unchanged. `ApiAppController` checks `Auth->user()` first and
   reuses `inAdminstrativeGroup($user, 'Calendar Admins')`.

2. **Machine clients (member-portal integration): hashed API tokens** — the one
   concrete approach proposed. Rationale over OIDC client-credentials via
   Keycloak: Keycloak is only wired into the dev/docker environment; production
   identity is AD, and minting a Keycloak client-credentials flow would need new
   IdP operational work and a service account with group-membership claims.
   Hashed tokens are self-contained, trivially revocable, auditable per token,
   and need no new external dependency.

New table `api_tokens` (migration `config/Migrations/*_CreateApiTokens.php`):

| column | type | notes |
|---|---|---|
| `id` | int PK | |
| `name` | varchar(100) | human label, e.g. `member-portal-prod` |
| `token_hash` | char(64) | SHA-256 of the token; only the hash is stored |
| `scopes` | varchar(255) | comma list; must contain `admin` for this API |
| `created_by` | varchar(100) | samaccountname of the issuing admin |
| `last_used_at` | datetime nullable | updated on each use |
| `expires_at` | datetime nullable | |
| `revoked` | bool default false | |
| `created`, `modified` | datetime | Timestamp behavior |

Flow: `Authorization: Bearer <token>` → `ApiAppController` hashes with SHA-256,
looks up a non-revoked, non-expired row, checks `scopes` contains `admin`, sets a
synthetic identity `['principal' => 'token:<name>', 'groups' => ['Calendar Admins']]`
so the same `inAdminstrativeGroup` check applies. Token issuance UI: a minimal
admin screen (or a `bin/cake` shell) that generates a token, prints it **once**,
and stores only the hash. Missing/invalid token → 401; valid token without
`admin` scope or non-admin session → 403. Raw tokens never appear in logs.

## 5. Validation rules

`PrerequisitesTable::validationDefault()` (existing) stays: `name` and `ad_group`
required, non-empty. Additions:

- **Uniqueness.** Add `buildRules()` entries: `name` unique and `ad_group` unique.
  Migration adds unique indexes on both (collation `utf8_general_ci` makes them
  case-insensitive, matching AD's case-insensitive `cn`). Rationale: one display
  label maps to one AD group; a duplicate `ad_group` would make event-close
  membership writes ambiguous. The migration pre-checks for case-insensitive
  duplicates and aborts with the offending values before adding the indexes
  (dedupe first, then re-run). **Note:** these `isUnique` rules also apply to
  the existing HTML `PrerequisitesController` saves (Crud add/edit), which
  previously had no uniqueness rules — this is intentional. A check-then-insert
  race between the rule and the INSERT surfaces as a duplicate-key error, which
  the API maps to 422 with the same detail text.
- **`ad_group` must exist in AD (read-only).** New service
  `src/Service/DirectoryService.php` with `groupExists(string $cn): bool`,
  using the existing service-account bind pattern (same config keys as
  `AppController::_refreshAdGroups()` / `EventsController` event close):
  query `cn = $cn AND objectclass = group`, return whether a record is found.
  Wired as the single source of truth in `PrerequisitesTable::buildRules()` —
  there is deliberately no controller-level pre-check (it would double the LDAP
  binds and could disagree with the rule). Rule semantics: throws
  `DirectoryUnavailableException` when LDAP is unreachable (the API catches it
  around `save()` → 503, fail closed), returns an error string when the group
  genuinely does not exist (→ 422 with `source.pointer =
  /data/attributes/ad_group` and detail like `"ad_group 'Laser Basics' was not
  found in Active Directory"`), and skips blank values so
  `validationDefault()`'s `notEmpty` reports them as 422. **Note:** like the
  uniqueness rules, this AD-existence rule also runs on the existing HTML
  `PrerequisitesController` saves. Fail closed: if LDAP is unreachable, the
  write is rejected with 503 and a clear message — never accept an unverified
  `ad_group`, since a bad value silently breaks event-close membership writes
  and the `func-dms-prereq` function downstream.
- **Delete guard.** `PrerequisitesTable::buildRules()` delete rule (or an explicit
  check in the controller): if any `events` row references the id via
  `fulfills_prerequisite_id` or `requires_prerequisite_id`, reject with
  **409 Conflict**, detail e.g. `"Cannot delete: prerequisite is referenced by
  3 event(s) (e.g. 'Intro to Laser', …)"`. Listing up to 3 example event names
  keeps the message actionable without dumping data.

## 6. Audit logging

Reuse the existing `Logs` pattern. `ApiAppController::apiLog($description)`
writes the same `Logs` entity fields as `AppController::customLog()` —
`description`, `user` (session `samaccountname` or `token:<name>` for machine
clients), `date_time`, `ip_address`, `url`, `controller`, `action` — but logs
**all** mutations (add/edit/delete), since `customLog`'s index/view skip doesn't
apply to an API. Call it from the v1 controller's create/update/delete actions
with descriptions like `"API: created prerequisite 'Laser Basics' (ad_group='Laser Basics')"`.
Failed validation/auth attempts are not logged to `Logs` (noise); LDAP errors go
to the app log via `Log::warning`, matching existing practice.

## 7. JSON contract (frozen for v1; member-portal integration target)

- Field naming: **snake_case**, matching CakePHP column/entity names:
  `id`, `name`, `ad_group`, `created`, `modified`. Datetimes ISO-8601 UTC
  (`2018-11-05T02:11:23+00:00`).
- Success envelope: `{ "data": <object|array>, "meta": { ... } }`. `meta` on
  `index` only: `{ "page": 1, "per_page": 50, "total": 4, "total_pages": 1 }`.
  `meta` is `{}` otherwise. No `success` flags, no Flash-style messages.
- Error envelope (always, for 4xx/5xx):
  ```json
  { "errors": [ { "status": "422", "title": "Validation failed",
                  "detail": "ad_group 'Foo' was not found in Active Directory",
                  "source": { "pointer": "/data/attributes/ad_group" } } ] }
  ```
- Status codes: `200` index/view/update, `201` create (with `Location` header),
  `204` delete (empty body), `400` malformed JSON, `401` unauthenticated,
  `403` authenticated but not a Calendar Admin, `404` unknown id, `409` delete
  blocked by event references, `422` validation (incl. unknown `ad_group`),
  `503` LDAP unreachable during validation.

### Examples

`GET /api/v1/prerequisites` →
```json
{
  "data": [
    { "id": 1, "name": "Laser Basics", "ad_group": "Laser Basics",
      "created": "2026-01-15T18:02:11+00:00", "modified": "2026-01-15T18:02:11+00:00" },
    { "id": 2, "name": "Woodshop Basics", "ad_group": "DMS-Woodshop-Basics",
      "created": "2026-01-15T18:02:11+00:00", "modified": "2026-03-02T09:44:00+00:00" }
  ],
  "meta": { "page": 1, "per_page": 50, "total": 2, "total_pages": 1 }
}
```

`POST /api/v1/prerequisites` with
`{ "data": { "attributes": { "name": "Kiln Basics", "ad_group": "Kiln Basics" } } }`
→ `201`, `Location: /api/v1/prerequisites/5`:
```json
{ "data": { "id": 5, "name": "Kiln Basics", "ad_group": "Kiln Basics",
            "created": "2026-10-03T19:00:00+00:00", "modified": "2026-10-03T19:00:00+00:00" },
  "meta": {} }
```
Request body shape: JSON:API-lite — attributes under `data.attributes`; the
controller also accepts a flat `{ "name": ..., "ad_group": ... }` body for
curl-friendliness (both documented; `data.attributes` is canonical).

`PATCH /api/v1/prerequisites/5` (rename display label only, `ad_group`
unchanged) → `200` with updated entity. Changing `ad_group` re-runs the
AD-existence validation.

`DELETE /api/v1/prerequisites/1` when referenced → `409`:
```json
{ "errors": [ { "status": "409", "title": "Prerequisite in use",
  "detail": "Cannot delete prerequisite 'Laser Basics': it is referenced by 3 event(s), e.g. 'Laser Basics 101'. Remove the references first.",
  "source": { "pointer": "/data/id" } } ] }
```
(On a delete race — an event reference appearing between the controller's
pre-check and the actual delete — the `addDelete` model rule blocks the delete
and the API re-checks references, still answering 409; only a genuinely
unreferenced failure is 500.)

## 8. Test strategy

- `tests/TestCase/Controller/Api/V1/PrerequisitesControllerTest.php`:
  `IntegrationTestCase` with fixtures `app.Prerequisites`, `app.Events`
  (for the 409 guard), `app.ApiTokens`, `app.Logs` (audit writes). (CamelCase
  fixture names — the underscored form is deprecated in CakePHP 3.10.)
  - Auth: session-login path tested via `session(['Auth.User' => [...groups...]])`-style
    helpers per existing controller tests; token path via `Authorization: Bearer`
    header with a fixture token whose hash is a known value.
  - Cover: index pagination meta, view (incl. 404), create 201 + AD validation
    pass/fail, update, delete 204, delete-when-referenced 409, 401/403 cases,
    envelope shape assertions on errors.
- **LDAP stubbing:** `DirectoryService::groupExists()` reads a stub map from
  `Configure::read('Directory.stubGroups')` in the `test` env
  (`config/app.testing.php`), so unit/integration tests never hit the network.
  A separate `tests/TestCase/Service/DirectoryServiceTest.php` exercises the real
  LDAP path against the docker OpenLDAP (`cn=admin,dc=dms,dc=local`; seed a test
  group in `dms-ad-openldap/`) and is the only test allowed to touch LDAP.
- Run: `composer test` (phpunit + phpcs) per CLAUDE.md; e2e via Playwright only
  if an admin UI for token issuance is added later.

## 9. File / migration checklist (implementation order)

1. `config/Migrations/20261003*_AddUniqueIndexesToPrerequisites.php` —
   unique indexes on `prerequisites.name`, `prerequisites.ad_group`.
2. `config/Migrations/20261003*_CreateApiTokens.php` — `api_tokens` table (§4).
3. `src/Model/Table/ApiTokensTable.php` (+ `src/Model/Entity/ApiToken.php`) —
   validation, `hashToken()`, `findValid()` finder (not revoked, not expired).
4. `src/Service/DirectoryService.php` — `groupExists(string $cn): bool`
   (service-account bind, read-only query `cn`+`objectclass=group`); throws on
   connection failure so callers can return 503.
5. `src/Model/Table/PrerequisitesTable.php` — `buildRules()`: unique name,
   unique ad_group, AD-existence rule via `DirectoryService`, delete guard via
   `Events` reference count.
6. `src/Controller/Api/ApiAppController.php` — §3/§4/§6.
7. `src/Controller/Api/V1/PrerequisitesController.php` — explicit
   index/view/add/edit/delete actions using the ORM directly (no Crud plugin),
   calling `apiLog()` on mutations.
8. `config/routes.php` — §2 prefix block.
9. `tests/Fixture/ApiTokensFixture.php` + `tests/TestCase/...` per §8.
10. Optional later: minimal admin UI or `bin/cake api_token create` shell for
    token issuance (prints token once).

## 10. Open questions for the coordinator (not blockers)

- Token issuance UX: admin web UI vs. `bin/cake` shell command — shell is
  cheaper; UI is friendlier for non-technical admins.
- `DirectoryService` connection reuse: `Container::addConnection()` pattern in
  existing code is effectively global — consider one shared connection per
  request to avoid re-binding on every validation call.
- Rate limiting on the token endpoint: not proposed for v1 (admin-only,
  low-volume); revisit if the member portal polls aggressively.
