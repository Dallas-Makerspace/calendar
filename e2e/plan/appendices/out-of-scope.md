# Appendix D — Out of scope & dead code

[← E2E plan index](../README.md)

---


Not required for manual QA; listed so auditors know gaps are intentional.

| Item | Location | Reason |
|---|---|---|
| `PagesController::display` | `config/routes.php` disabled | No static pages routed |
| `W9sController` / `/w9s` | Header nav commented / missing controller | W-9 upload UI not wired; `w9_on_file` manual on contact |
| `__add_with_intuit.ctp` | Template alternate | Not used by current add action |
| Reports nav link | Header commented | No ReportsController |
| `EventsController::reject` TODO comment | Line ~1160 | Stale — `sendEventRejected` **is** called in `afterSave` |
| `RegistrationsController` `$$registration` | Line ~164 | Bug — race on full free pool |
| `OpenIDConnectService::getGroupsFromUserInfo` | Missing return | SSO groups broken |
| PHPUnit `*Test.php` in `src/Controller` | Automated only | Run via `bin/cake test` |
| `bin/cake.php` console | No custom commands | No shell UX to test |
| Room id `58` in `_applyAddress` | Hardcoded | May not exist in Docker seed (26 rooms); Offsite typically id 23 |

---

