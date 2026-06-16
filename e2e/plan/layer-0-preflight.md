# Layer 0 — Pre-flight

Stack health and baseline before E2E suite.

Part of the [E2E implementation plan](README.md).

---

> [!NOTE]
> **Tracking:** Check `[x]` when a Playwright spec covers the test section. Steps below are manual procedure reference — not separate tests.


### 0.1 Stack health
- [x] 0.1 Stack health


**Steps:**

1. Open http://localhost:8000 — homepage loads without 500 error. _(partial: `tests/home.spec.ts`)_
2. Open http://localhost:8081 — phpMyAdmin loads.
3. Open http://localhost:8888 — phpLDAPadmin loads (`cn=admin,dc=dms,dc=local` / `Adm1n!`).
4. Run `docker compose ps` — all containers show `Up` (app, db, openldap, mail, keycloak, etc.).


**Expected:** All services reachable; event index shows seeded upcoming events or empty list (not an error page).


### 0.2 Record baseline config values
- [ ] 0.2 Record baseline config values


**Steps:**

1. Log in as `user2`.
2. Go to **Admin → Configuration**.
3. Note values for configs 1–6 (approval times, lead times, role call cutoff).


**Expected:** Six configuration rows with day-based values (defaults: 2, 3, 10, 2, 190, 2).


### 0.3 Infrastructure readiness `[INFRA: *]`
- [ ] 0.3 Infrastructure readiness `[INFRA: *]`


Run once before Layer 2 tagged tests. Record PASS/FAIL/BLOCKED per row.

1. SparkPost API reachable — set `SPARKPOST_APIKEY`; restart `app`; submit event as `user1` `[INFRA: SparkPost]`
2. MailHog SMTP workaround — patch `EmailComponent` to use `default` transport; submit event `[INFRA: Email-dev]`
3. Braintree sandbox — set four `BRAINTREE_*` vars; open paid event registration `[INFRA: Braintree]`
4. Twilio credentials — cancel event with `send_text=1` registrant `[INFRA: Twilio]`
5. Keycloak SSO — add `127.0.0.1 keycloak` to host file; open `/users/sso-login` `[INFRA: OIDC]`
6. LDAP group edit — add/remove `user1` from a test group in phpLDAPadmin `[INFRA: LDAP-setup]`
7. Manual cron — `curl http://localhost:8000/events/cron` returns 200 `[INFRA: Time-manual]`


---


