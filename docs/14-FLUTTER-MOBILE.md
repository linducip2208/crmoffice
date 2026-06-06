# 14 — Flutter Mobile App (Phase 9 stub)

**Status:** 🟡 Spec only — implementation in Phase 9
**Target folder:** `D:\project flutter\crmoffice\` atau monorepo subfolder `mobile/` (TBD)
**Last updated:** 2026-05-14

---

## 1. Architecture

| Layer | Choice |
|---|---|
| Framework | **Flutter 3.24+** (Dart 3.5+) |
| State management | **Riverpod 2** (Provider/StateNotifier/AsyncNotifier) |
| HTTP | **Dio** + retrofit-like service classes |
| Storage | **Drift** (SQLite) for offline cache + outbox |
| Auth | **Sanctum personal access token** stored di `flutter_secure_storage` |
| Push notifications | **FCM** via generic provider adapter (server side) |
| Theming | **Material 3** with brand colors matching admin (indigo) |

## 2. App Structure

```
mobile/
├── lib/
│   ├── core/
│   │   ├── api/             # Dio client + interceptors
│   │   ├── auth/            # token storage, login flow
│   │   ├── theme/           # Material 3 theme
│   │   └── router/          # GoRouter config
│   ├── features/
│   │   ├── auth/            # login screen
│   │   ├── clients/         # list, detail, edit
│   │   ├── leads/           # kanban view native
│   │   ├── invoices/        # list, detail, pay
│   │   ├── projects/        # list, tasks, time tracking
│   │   ├── tasks/           # my tasks, start/stop timer
│   │   ├── tickets/         # list, reply
│   │   ├── calendar/        # combined view
│   │   └── profile/         # settings, 2FA, tokens
│   ├── shared/
│   │   ├── models/          # DTOs matching API resources
│   │   ├── widgets/         # Reusable components
│   │   └── utils/           # date/money/i18n
│   └── main.dart
├── pubspec.yaml
└── README.md
```

## 3. Two App Personas

### 3.1 Staff App
Untuk sales/support/PM/staff. Akses internal:
- Lihat & update leads (drag kanban native, swipe to update status)
- Tasks assigned: list + detail + start/stop timer
- Submit ticket reply
- Calendar: due dates, events
- Quick lookup client/contact
- Push notif: task assigned, ticket SLA, invoice paid

### 3.2 Customer App
Untuk pelanggan (customer portal native). Akses terbatas:
- Lihat invoice + tap to pay (gateway adapter)
- Submit + balas ticket
- Lihat project progress
- Lihat KB articles
- Push notif: invoice due, ticket reply, project update

**Decision:** kemungkinan dua build (build flavor) dari satu codebase — `staff` dan `customer` — share core/auth/api modules.

## 4. API Contract

Semua endpoint sudah documented di [06-API-DESIGN.md](./06-API-DESIGN.md). Key endpoints untuk mobile:
- `POST /api/v1/auth/login` → token
- `GET /api/v1/auth/me`
- `GET /api/v1/leads?include=source,assignedTo`
- `PATCH /api/v1/leads/{id}/status`
- `GET /api/v1/tasks/my`
- `POST /api/v1/time-entries/timer/start`
- `POST /api/v1/time-entries/timer/stop`
- `POST /api/v1/tickets/{id}/replies`
- `GET /api/v1/invoices?status=unpaid`
- `POST /api/v1/public/invoices/{token}/pay` (customer)

## 5. Offline-First Strategy

| Entity | Strategy |
|---|---|
| Leads/Clients/Contacts | Cache-and-refresh — read from Drift, sync in background |
| Tasks | Read-write offline; outbox queue for mutations |
| Time entries | Always writable offline (the most critical), sync when online |
| Invoices/Tickets | Read-only offline; mutations require connection |
| Calendar | Cache 60 days forward |

Conflict resolution: server wins for status transitions; client wins for free-text fields (notes).

## 6. Push Notifications

Server side (Laravel):
- Use generic Push provider adapter (FCM format)
- Device token registration: `POST /api/v1/push/register` with `{device_token, platform, app_persona}`
- Token stored in `devices` table (TODO migration in Phase 9)
- Notification class `App\Notifications\PushNotification` via Laravel notification with custom channel

Client side (Flutter):
- `firebase_messaging` + `flutter_local_notifications`
- Handle foreground / background / terminated states
- Tap action → deep link to entity

## 7. Build & Distribution

| Channel | Method |
|---|---|
| Internal dev | Flutter `flutter run` |
| QA testing | Firebase App Distribution |
| Production iOS | App Store via TestFlight |
| Production Android | Google Play / direct APK |

## 8. Estimated Timeline (Phase 9)

| Sub-phase | Effort | Output |
|---|---|---|
| 9.1 Project scaffold + auth flow | 5 days | Login screen, token storage, navigation |
| 9.2 Staff: Tasks + Time tracking | 7 days | My Tasks, timer, log time |
| 9.3 Staff: Leads kanban + activities | 7 days | Kanban drag, lead detail, activity log |
| 9.4 Staff: Tickets + Calendar | 5 days | Ticket reply, calendar view |
| 9.5 Customer: Invoices + pay | 5 days | View, download PDF, pay button |
| 9.6 Customer: Tickets + Projects | 4 days | Submit, reply, view project |
| 9.7 Offline-first + sync | 7 days | Drift cache, outbox, conflict resolution |
| 9.8 Push notifications | 4 days | FCM integration both sides |
| 9.9 Polish + i18n + accessibility | 5 days | Indonesian + English strings, screen reader |
| 9.10 Beta testing + fixes | 5 days | TestFlight + Play Internal |

**Total: ~54 days.** Two developers = ~27 days calendar.

## 9. Compatibility Matrix

| Server version | Mobile version | Notes |
|---|---|---|
| v0.1.x – v0.4.x | (no mobile) | Pre-mobile era |
| v0.5.0+ | v1.0+ | First mobile release; requires API v1 |
| v1.x | v1.x | Forward-compat: server keeps API v1 stable |

Breaking API changes → bump to `/api/v2/*` with minimum 12 months overlap.

## 10. Why Flutter (and not React Native)?

Per [11-TECH-STACK.md](./11-TECH-STACK.md) and global stack-lock decision:
- Single codebase iOS + Android (true cross-platform)
- Native performance (compiled to ARM)
- Strong type system (Dart)
- Material 3 + Cupertino built-in
- Active Google maintenance + ecosystem
- Smaller learning curve than React Native for backend-focused devs

Stack-lock rules out React Native, Ionic, Native (Kotlin/Swift).
