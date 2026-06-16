# Appendix C — Model validation branches

[← E2E plan index](../README.md)

---


Use with §2.3 negative tests. Each row is a distinct logical branch in `src/Model`.

### `EventsTable::validationDefault`

| Field / rule | Valid test input | Invalid / branch |
|---|---|---|
| `event_start` + honorarium | Start ≥ config 3 days | §2.3 honorarium lead |
| `event_start` no honorarium | Start ≥ config 4 days | §2.3 start too soon |
| `event_start` max horizon | Within config 5 days | §2.3 max lead |
| `event_end` | End after start | §2.3 end before start |
| `eventbrite_link` | Optional URL on Eventbrite type | §2.4 Eventbrite variant |
| `free_spaces` / `paid_spaces` | `0` = unlimited pool | §2.16 |
| `age_restriction` | 0, 13, 16, 18, 21 | §2.3, §2.14 |
| `extend_registration` | 0, 15, 20, 25, 30 only | §2.3, §2.17 |

### `EventsTable::buildRules`

| Rule | Test section |
|---|---|
| Exclusive room overlap | §2.3 |
| Tool overlap | §2.3 |
| Multipart session 2 room conflict | §2.3 |

### `EventsTable` space helpers

| Method | Branch | Test section |
|---|---|---|
| `getTotalSpaces` | Returns `true` when both pools 0 | §2.16 |
| `hasFreeSpaces` / `hasPaidSpaces` | Separate pool exhaustion | §2.16 |
| `getFilledSpaces` | Excludes cancelled/rejected | §2.16, §3.4 |

### `RegistrationsTable`

| Rule | Test section |
|---|---|
| Email unique per `event_id` | §2.16 |
| `ad_username` unique per `event_id` | §2.10 |
| `refund()` void vs settle vs no-op | §2.18, §2.19 |

### Behaviors

| Behavior | Test section |
|---|---|
| `RelationalTimeBehavior` booking/cancellation offsets | §2.7 |
| `FriendlyTimeBehavior` US-format → UTC on save | §2.7 (admin edit round-trip) |
| Multipart continued events (`_afterCreate/_afterUpdate`) | §2.4, §2.7, §2.8 |
| `FilesTable` multipart file delete cascade | §2.9 |

---

