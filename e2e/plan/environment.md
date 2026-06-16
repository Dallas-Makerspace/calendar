# Environment & infrastructure

Part of the [E2E implementation plan](README.md). Reference material — individual infra checks are checkboxes in [Layer 0 §0.3](layer-0-preflight.md).

---


### Services

| Service | URL | Purpose |
|---|---|---|
| Calendar app | http://localhost:8000 | Application under test |
| MailHog | http://localhost:8025 | Present in Docker; app ignores unless `[INFRA: Email-dev]` patch |
| phpMyAdmin | http://localhost:8081 | DB inspection (`root` / `cakephp`) |
| phpLDAPadmin | http://localhost:8888 | LDAP group management |
| Keycloak | http://localhost:8080 | SSO (`[INFRA: OIDC]` — needs hosts + env) |

### Infrastructure dependency tags

Every test that needs something beyond bare `docker compose up` is **included in this plan** and marked with a tag. **Do not skip tagged tests** — run them when the listed setup is in place, or record **BLOCKED** with the tag reason.

| Tag | In default Docker stack? | What you need |
|---|---|---|
| `[INFRA: SparkPost]` | No — `EmailComponent` hardcodes SparkPost transport | `SPARKPOST_APIKEY` in `.env` **and** passed into `app` container (see §0.3). Alternative: `[INFRA: Email-dev]` |
| `[INFRA: Email-dev]` | Partial — MailHog runs but app ignores it | Temporarily point `EmailComponent::sendEmail` at SMTP transport `default` (MailHog `mail:1025`) |
| `[INFRA: Braintree]` | No — empty `.env` keys | Braintree sandbox credentials in `.env` + `app` container env |
| `[INFRA: OIDC]` | Partial — Keycloak container runs | `127.0.0.1 keycloak` in host `/etc/hosts`; OIDC URLs aimed at localhost; client secret in `.env`; fix `getGroupsFromUserInfo` return for group tests |
| `[INFRA: Twilio]` | No | `TWILIO_ACCTSID`, `TWILIO_AUTHTOKEN`, `TWILIO_PHONENUM` in `.env` + container env |
| `[INFRA: LDAP-setup]` | Yes — phpLDAPadmin on :8888 | Manual group membership edits (prerequisites, single-group auth matrix) |
| `[INFRA: Time-manual]` | Yes — no scheduler | Edit event timestamps in DB/phpMyAdmin; hit `GET /events/cron` manually |
| `[INFRA: Pre-2017-data]` | No — seed data is modern | Insert synthetic `old_registrations` rows for events before 2017-01-01 |

**Passing env vars to the app container (dev):** Add to `docker-compose.yml` under `app.environment` (values from `.env`):

```yaml
SPARKPOST_APIKEY: ${SPARKPOST_APIKEY}
BRAINTREE_ENV: ${BRAINTREE_ENV}
BRAINTREE_MERCHID: ${BRAINTREE_MERCHID}
BRAINTREE_PUBKEY: ${BRAINTREE_PUBKEY}
BRAINTREE_PRIVKEY: ${BRAINTREE_PRIVKEY}
TWILIO_ACCTSID: ${TWILIO_ACCTSID}
TWILIO_AUTHTOKEN: ${TWILIO_AUTHTOKEN}
TWILIO_PHONENUM: ${TWILIO_PHONENUM}
OIDC_CLIENT_ID: ${OIDC_CLIENT_ID:-calendar}
OIDC_CLIENT_SECRET: ${OIDC_CLIENT_SECRET:-dummy-secret-for-dev-mode}
OIDC_URL_AUTHORIZE: http://keycloak:8080/realms/DMS/
OIDC_URL_ACCESS_TOKEN: http://keycloak:8080/realms/DMS/protocol/openid-connect/token
OIDC_URL_RESOURCE_OWNER: http://keycloak:8080/realms/DMS/protocol/openid-connect/userinfo
```

Restart `app` after changes. `.docker/environment.conf` already exposes these to PHP when set in the container.

### Known infrastructure gaps (summary)

| Gap | Impact | Tag |
|---|---|---|
| Email uses SparkPost, not MailHog SMTP | No emails in MailHog UI unless SparkPost key or Email-dev patch | `[INFRA: SparkPost]` or `[INFRA: Email-dev]` |
| Braintree keys empty | Paid registration / refunds untestable | `[INFRA: Braintree]` |
| OIDC URLs default to production hostnames | SSO redirect fails without hosts + local URLs | `[INFRA: OIDC]` |
| Twilio keys empty | Cancel-event SMS fails silently (logged) | `[INFRA: Twilio]` |
| `user1` not in prerequisite LDAP groups | Prereq positive path blocked until phpLDAPadmin edit | `[INFRA: LDAP-setup]` |
| No cron daemon | Time-based behavior needs manual cron URL + DB dates | `[INFRA: Time-manual]` |
| Cron reminder/start SMS calls commented out | Only **event cancel** sends SMS (`EventsController::cancel`) | N/A — code disabled |

### Start stack

```bash
git submodule update --init --recursive
./setup.sh
docker compose up
```

Wait until the app responds at http://localhost:8000 and migrations/seeds complete in the `app` container logs.

### Test accounts

| User | Password | AD groups |
|---|---|---|
| `user1` | `password` | Members (regular member) |
| `user2` | `password` | Calendar Admins, Honorarium Admins, Financial Reporting, Calendar Super Admins |
| `honorariumadmin` | `password` | Members, Honorarium Admins only |
| `financialadmin` | `password` | Members, Financial Reporting only |
| `user3` | `password` | Members, Electronics committee |
| LDAP admin | `Adm1n!` | `cn=admin,dc=dms,dc=local` |

### Known code issues affecting tests

| Issue | Location | Test impact |
|---|---|---|
| `getGroupsFromUserInfo()` missing `return $result` | `OpenIDConnectService.php` | SSO group checks and SSO prerequisite refresh always fail until fixed |
| `$$registration` typo | `RegistrationsController.php` ~line 164 | Free registration when full may error instead of showing flash |
| Braintree failure `stopPropagation` commented out | `RegistrationsController.php` ~line 158 | Failed payment may still save registration |
| OIDC success redirect uses invalid `controller => '/'` | `UsersController.php` | SSO callback may error after auth |
| SSO login bypasses `Auth.afterIdentify` | `UsersController::oidcCallback` | SSO users may lack `contact_id` / `contact_error` handling |
| Address null uses hardcoded room ids 23, 58 | `EventsController::_applyAddress` | Offsite/online address suppression only if those DB ids match production |
| Config index excludes id ≥ 7 | `ConfigurationsController` | “Allow Honoraria” only editable via Super Admin, not Configuration index |

---

