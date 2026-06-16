# Appendix F — Infrastructure-tagged tests

[← E2E plan index](../README.md)

---


Complete list of tests requiring setup beyond default `docker compose up`. Run §0.3 first.

| Section | Tag(s) | What is verified |
|---|---|---|
| §0.3 | All | Infrastructure readiness smoke checks |
| §1.9 step 10–11 | ~~`[INFRA: LDAP-setup]`~~ | Honorarium / Financial contact view — covered by `honorariumadmin` / `financialadmin` seed users |
| §1.11 steps 4–8 | `[INFRA: LDAP-setup]` | Financial-only and honorarium-only auth matrix |
| §2.2 | `[INFRA: SparkPost]` / `[INFRA: Email-dev]` | Event submission email |
| §2.4.1 | `[INFRA: LDAP-setup]` | Prerequisite positive path |
| §2.4 variant notes | `[INFRA: SparkPost]` / `[INFRA: Email-dev]` | Instructor notification email |
| §2.6 approve/reject | `[INFRA: SparkPost]` / `[INFRA: Email-dev]` | Approval/rejection emails |
| §2.6 honorarium auth | `[INFRA: LDAP-setup]` | Honorarium vs calendar admin split |
| §2.10 | `[INFRA: SparkPost]` / `[INFRA: Email-dev]` | Registration confirmation email |
| §2.11 | `[INFRA: SparkPost]` / `[INFRA: Email-dev]` | Pending/approved/rejected/instructor emails |
| §2.11 | `[INFRA: Braintree]` | Paid host-reject refund message |
| §2.13 | `[INFRA: OIDC]` | SSO prerequisite group refresh |
| §2.18 (entire section) | `[INFRA: Braintree]` | Paid registration, void/refund, decline |
| §2.19 | `[INFRA: SparkPost]` / `[INFRA: Email-dev]` | Cancellation + instructor emails |
| §2.19 | `[INFRA: Braintree]` | Paid cancel refund |
| §2.19 step 5 | `[INFRA: SparkPost]` / `[INFRA: Email-dev]` | Instructor cancel notification |
| §2.22 | `[INFRA: SparkPost]` / `[INFRA: Email-dev]` | Event cancelled emails to registrants |
| §2.22 step 4–5 | `[INFRA: Twilio]` | Cancel-event SMS |
| §2.24 step 10–11 | `[INFRA: Pre-2017-data]` | Old registrations export branch |
| §2.25 step 3 | `[INFRA: LDAP-setup]` | Honoraria CRUD requires Calendar Admins |
| §2.26 (entire section) | `[INFRA: Time-manual]` | All cron branches |
| §2.26 email sub-steps | `[INFRA: SparkPost]` / `[INFRA: Email-dev]` | Cron-triggered emails |
| §2.26 paid refund | `[INFRA: Braintree]` | Refund on cron cancel |
| §2.27 (entire section) | `[INFRA: SparkPost]` / `[INFRA: Email-dev]` | Full email method checklist |
| §2.28 (entire section) | `[INFRA: OIDC]` | SSO login, failure, contact gap, groups |

### Re-analysis notes (second pass, post–infra tagging)

| Finding | Source | Test impact |
|---|---|---|
| `EmailComponent` always uses SparkPost transport | `EmailComponent.php:65` | MailHog docker env ignored unless `[INFRA: Email-dev]` patch |
| Cron auto-approve does **not** send email | `EventsController::cron` | §2.26 step 5 — status change only |
| Only `cancel` action calls `__sendText` | `EventsController.php:1489` | §2.22 Twilio; cron SMS tests N/A |
| SSO bypasses `afterIdentify` | `UsersController::oidcCallback` | §2.28 step 6 — contact provisioning gap |
| `/honoraria` uses Calendar Admins auth | `HonorariaController` + parent auth | §2.25 step 3 |
| `sendEventRejected` is wired | `EventsController::reject` afterSave | §2.6 — not dead code |
| `FriendlyTimeBehavior` | `EventsTable` — formats display times on marshal | Covered indirectly via §2.7 edit round-trip |
| Dev `docker-compose.yml` omits Braintree/SparkPost/Twilio/OIDC env | `docker-compose.yml` vs `.docker/environment.conf` | §0.3 / infra tag table — must add vars manually |
| `approve` afterSave sends email; `cron` auto-approve does not | Compare `approve()` vs `cron()` | Different expectations in §2.6 vs §2.26 |
| Multipart reject/approve cascades | `reject`/`approve` afterSave | §2.6 steps added |
