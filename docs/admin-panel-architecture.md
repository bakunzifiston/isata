# ISATA Admin Panel — Architecture & Module Map

> **Purpose of this document:** Describe how the authenticated admin panel works today — routes, modules, data relationships, validation rules, and cross-module flows. Intended for external review (e.g. Claude) to identify gaps, inconsistencies, and recommended adjustments before further UI/product work.
>
> **Stack:** Laravel (latest) · Blade · Tailwind v4 · Vite · Alpine.js (npm in `app.js`; dashboard layout also loads Alpine via CDN) · Queue workers for async delivery

---

## 1. High-level architecture

```
┌─────────────────────────────────────────────────────────────────────────┐
│  PUBLIC (no auth)                                                       │
│  /  marketing home · /login · /register                                 │
│  /events/{event}/rsvp · /rsvp/* · /feedback/* · POST /webhook/sms/rsvp │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│  AUTH MIDDLEWARE (auth)                                                   │
│  /dashboard · /notifications · POST /logout                             │
│  /super-admin/*  (system_admin middleware)                              │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│  ORGANIZATION MIDDLEWARE (organization)                                   │
│  All organizer tools: events, messages, attendees, analytics, etc.      │
└─────────────────────────────────────────────────────────────────────────┘
```

**Layout shell:** `resources/views/layouts/dashboard.blade.php`  
- Fixed left sidebar (slate-900 / indigo active states — **not yet migrated** to marketing design tokens)  
- Top bar: notifications, user name, org name, sign out  
- `DashboardComposer` injects `usageMeter`, `notifications`, `unreadNotificationsCount` into every dashboard view

**Tenant model:** Multi-tenant by `Organization`. Almost all data is scoped via `auth()->user()->organization_id`. Controllers manually check `event->organization_id === $organization->id` — there are **no Laravel Policy classes** and **no FormRequest classes**; validation lives inline in controllers.

---

## 2. Users, roles & access control

| Role | `organization_id` | `role` | Access |
|------|-------------------|--------|--------|
| System admin | `null` | `admin` | `/super-admin/*` only; redirected from `/dashboard` |
| Organization admin | set | `admin` | Full org panel + user management + profile update + plan upgrade |
| Organization staff | set | `staff` | Org panel except: cannot manage users, cannot update org profile, cannot upgrade plan |

### Middleware

| Alias | Class | Effect |
|-------|-------|--------|
| `auth` | Laravel default | Must be logged in |
| `guest` | Laravel default | Login/register only |
| `organization` | `EnsureUserBelongsToOrganization` | User must belong to an org; blocks if `organizations.is_active === false` |
| `system_admin` | `EnsureUserIsSystemAdmin` | `organization_id === null && role === admin` |
| `admin` | `EnsureUserIsAdmin` | `role === admin` (registered but rarely used on routes) |

### Registration flow

1. `POST /register` → `RegisterOrganizationController@store`
2. Creates `Organization` (unique slug from name) + first `User` as org admin
3. Assigns `subscription_plan_id` to **freemium** plan if `subscription_plans` table exists
4. Logs user in → redirects to `/dashboard`

---

## 3. Core data model (module connections)

```
Organization
├── users[]
├── subscriptionPlan
├── events[]
│   ├── attendees[]
│   │   ├── rsvps[]
│   │   └── feedback[]
│   ├── messages[]          → channel, senderIdentity, sentBy (User)
│   ├── reminderSettings    → 24hr/1hr MessageTemplate refs
│   ├── beepCalls[]
│   ├── surveys[]
│   └── rsvps[] (denormalized via attendee)
├── messageTemplates[]      → channel
├── emailSenderIdentities[]
├── socialAccounts[]
├── socialPosts[]           → optional event, socialAccount
├── beepCalls[]             → event, attendee
├── surveys[]               → event, feedback[]
└── usage[] (OrganizationUsage per Y-m period)

Channel (global, not per-org)
  slug: email | sms | beep_call | social_media
  flags: supports_subject, supports_audio, supports_attachment

CommunicationLog
  organization_id, event_id, message_id, attendee_id, channel_id
  status, sent_at, delivered_at, opened_at
```

### Key status enums

**Event:** `draft` · `scheduled` · `cancelled` · `completed`  
**Event format:** `physical` · `online` (venue vs meeting_link)  
**Attendee RSVP:** `pending` · `confirmed` · `declined` · `attended`  
**Message:** `draft` · `scheduled` · `queued` · `sent` · `failed`  
**Message content_type:** `text` · `image` · `structured` (JSON blocks via `StructuredMessageDocument`)  
**BeepCall:** `pending` · `queued` · `completed` · `failed` (see `BeepCall` model)  
**SocialPost:** `draft` · `scheduled` · `published` · `failed`

---

## 4. Subscription & usage

### Plans (from migration `2025_02_16_200003_refresh_subscription_plans_for_phase3.php`)

| Slug | Price | events/month | contacts | Beep calls |
|------|-------|--------------|----------|------------|
| freemium | $0 | 1 | 50 | No |
| basic | $19 | 5 | 300 | No |
| pro | $49 | 20 | 2,000 | No |
| premium | $99 | unlimited | unlimited | **Yes** |

> **Note:** Marketing landing page shows different prices ($0 / $75 / $150 / $249) — **not synced with DB**.

### Usage tracking

- Table: `organization_usage` (per org, per `period` = `Y-m`)
- Counters: `events_count`, `contacts_count`, `beep_calls_count`
- **Incremented when:**
  - Event status becomes `scheduled` (create or update) → `events_count++`
  - Attendee created (manual, CSV, bulk) → `contacts_count += n`
- **Displayed in:** sidebar usage meter, `/usage` page
- **Not enforced:** Controllers do **not** block creation when limits are exceeded — tracking only

### Plan gating

- **Beep calls:** `BeepCallController::ensurePremium()` → requires `subscriptionPlan->hasBeepCalls()` (Premium only); returns 403 otherwise
- **Other channels:** No plan gate in code today

### Upgrade flow

- `GET /subscription/plans` — comparison view
- `GET /subscription/upgrade` — admin-only upgrade picker
- `POST /subscription/upgrade` — sets `organization.subscription_plan_id` directly (no Stripe/Cashier yet)

---

## 5. Module-by-module reference

### 5.1 Dashboard

| | |
|---|---|
| **Route** | `GET /dashboard` → `DashboardController` |
| **View** | `resources/views/dashboard.blade.php` |
| **Shows** | Event count, attendee count, messages sent, upcoming events, 7-day comms chart |
| **Connected to** | `Event`, `Attendee`, `Message`, `CommunicationLog` |
| **Validation** | None (read-only) |
| **Notes** | System admins redirect to `super-admin.dashboard` |

---

### 5.2 Events

| | |
|---|---|
| **Routes** | `events.index` · `create` · `store` · `show` · `edit` · `update` · `destroy` · `calendar` · `calendar/data` |
| **Controller** | `EventController` |
| **Hub for** | Attendees, messages, reminders, beep calls, surveys, analytics, RSVP public link |

#### Validation (`rulesForEventRequest`)

| Field | Rules |
|-------|-------|
| `name` | required, string, max:255 |
| `description` | nullable, string, max:5000 |
| `date` | required, date |
| `time` | nullable, regex `HH:MM` (stored with `:00` seconds) |
| `event_format` | required, `physical\|online` |
| `venue` | required if physical + scheduled; else nullable, max:255 |
| `meeting_link` | required if online + scheduled; else nullable, url, max:500 |
| `status` | create: `draft\|scheduled` · update: `draft\|scheduled\|cancelled\|completed` |

#### Cross-module connections

- **→ Attendees:** `events/{event}/attendees`
- **→ Messages:** nested resource `events/{event}/messages`
- **→ Reminders:** On update, saves `reminder_24hr_template_id` / `reminder_1hr_template_id` on `EventReminderSettings`
- **→ Public RSVP:** `route('events.rsvp', $event)` — no auth
- **→ Notifications:** `EventCreatedNotification` to org admins on create
- **→ Usage:** increments `events_count` when status becomes `scheduled`

#### Reminder automation

- Artisan: `php artisan reminders:process`
- Finds scheduled events within 5-min window of 24h / 1h before `date_time`
- Clones selected `MessageTemplate` into a new `Message` and dispatches `SendMessageJob`

---

### 5.3 Attendees (contacts)

| | |
|---|---|
| **Routes** | `events/{event}/attendees` (index, store, update, destroy) · `import/csv` · `import/bulk` |
| **Controller** | `AttendeeController` |
| **View** | `attendees/index.blade.php` (per event, not org-wide contacts page) |

#### Validation

**Manual create/update:**

| Field | Rules |
|-------|-------|
| `name` | required, max:255 |
| `email` | required, email, max:255 |
| `phone` | nullable, max:50 |
| `organization` | nullable, max:255 |
| `rsvp_status` | required, `pending\|confirmed\|declined\|attended` |

**CSV import:**

| Field | Rules |
|-------|-------|
| `csv_file` | required, file, mimes:csv,txt, max:2048 KB |
| Row logic | Must have `name` + `email` columns (case-insensitive aliases); invalid rows skipped with count |

**Bulk paste:**

| Field | Rules |
|-------|-------|
| `bulk_data` | required, string, max:10000 |
| Row logic | CSV lines: name, email, optional phone, org — skip invalid |

#### Cross-module connections

- **→ RSVP:** Attendee is the subject of signed RSVP links and SMS webhook matching
- **→ Messages:** `SendMessageJob` iterates `event->attendees()` (email required for send loop)
- **→ Beep calls:** Requires phone or email on attendee
- **→ Feedback/Surveys:** Feedback tied to attendee + survey
- **→ Usage:** `contacts_count` incremented on each new attendee

---

### 5.4 Messages

| | |
|---|---|
| **Routes** | `messages.hub` (org-wide) · `events/{event}/messages` (resource, no show) · `send-now` |
| **Controllers** | `MessageHubController`, `MessageController` |
| **Job** | `SendMessageJob` |

#### Message hub vs event messages

- **Hub** (`/messages`): All messages across org events, paginated; links to per-event CRUD
- **Per-event** (`/events/{id}/messages`): Create, edit, delete, send now

#### Validation (`messageFormRules` + channel conditionals)

| Field | Rules |
|-------|-------|
| `channel_id` | required, exists:channels |
| `content_type` | required, `text\|image\|structured` |
| `status` | create: `draft\|scheduled` · update: +`queued\|sent\|failed` |
| `scheduled_at` | nullable, date |
| **text** | `content` required, max:10000 |
| **image** | `content` nullable max:2000; `content_image` file image max:5120 KB |
| **structured** | `content_document` required JSON string max:131072; optional `structure_images.*` |
| **email channel** | `email_sender_type` saved\|custom; identity or custom name/email |
| **audio channel** | `audio_file` nullable, mp3/wav/m4a, max:10240 KB |
| **attachment** | pdf/images/docs, max:10240 KB |

#### Send flow

```
User clicks "Send now"
  → ConnectionService::isOnline()?
      NO  → message.status = queued
      YES → message.status = scheduled, dispatch SendMessageJob
                → foreach attendee (with email):
                      personalize content tokens
                      match channel slug → log / simulate send
                      write CommunicationLog
                → message.status = sent | failed
```

#### Merge / personalization tokens (messages)

`{name}` · `{event_name}` · `{event_time}` · `{venue}` · `{meeting_link}` · `{rsvp_link}` · `{feedback_link}`

> Organizers type single-brace tokens in the UI. These are **not** Blade variables — safe at render time.

#### Cross-module connections

- **← Events:** Every message belongs to one event
- **← Channels:** Global channel catalog drives form fields
- **← Templates:** Can be loaded in create/edit UI (manual apply)
- **← Email senders:** Saved identities or per-message custom from
- **→ Queue monitor:** Shows scheduled/due messages
- **→ Analytics:** Via `CommunicationLog`
- **→ Offline:** `ConnectionService` + `APP_SIMULATE_OFFLINE` / cache flag

---

### 5.5 Message templates

| | |
|---|---|
| **Routes** | `templates` resource (no show) |
| **Controller** | `MessageTemplateController` |

#### Validation

| Field | Rules |
|-------|-------|
| `name` | required, max:255 |
| `channel_id` | required, exists |
| `content` | required, max:10000 |
| `subject` | nullable, max:255 (if channel supports subject) |

#### Connections

- Org-scoped, linked to `Channel`
- Referenced by `EventReminderSettings` for automated 24h/1h reminders
- Used as starting point in message create/edit forms

---

### 5.6 Email sender identities

| | |
|---|---|
| **Routes** | `email-senders` CRUD |
| **Controller** | `EmailSenderIdentityController` |

#### Validation

| Field | Rules |
|-------|-------|
| `label` | nullable, max:100 |
| `from_name` | required, max:255 |
| `from_email` | required, email, max:255 |

#### Connections

- Org-scoped saved From addresses for email messages
- Selected in message form when `email_sender_type = saved`

---

### 5.7 Beep calls

| | |
|---|---|
| **Routes** | `beep-calls` index/create/store/destroy · `call-now` · `upload-audio` |
| **Controller** | `BeepCallController` |
| **Job** | `PlaceBeepCallJob` |
| **Gate** | Premium plan only (`ensurePremium`) |

#### Validation

| Field | Rules |
|-------|-------|
| `event_id` | required, exists, must belong to org |
| `attendee_ids` | required array, each exists |
| `audio_file` OR `audio_path` | required (upload or prior recording) |
| `call_schedule` | required, date, after_or_equal:now |
| `upload-audio` | file mp3/wav/m4a/ogg/webm, max:10240 |

#### Connections

- **← Event + Attendees:** One beep call row per attendee
- **← Messages:** Separate from message system; own audio storage
- **→ Queue:** `ProcessScheduledBeepCallsCommand` (scheduled artisan)
- **Plan:** Only Premium (`limits.beep_calls = true`)

---

### 5.8 Social media

| | |
|---|---|
| **Routes** | `social` posts CRUD · `publish-now` · `social/accounts` CRUD |
| **Controllers** | `SocialPostController`, `SocialAccountController` |
| **Job** | `PublishSocialPostJob` |

#### Post validation

| Field | Rules |
|-------|-------|
| `platform` | required, `facebook\|linkedin\|twitter\|whatsapp` |
| `content` | required, max:10000 |
| `event_id` | nullable, exists |
| `social_account_id` | nullable, exists |
| `scheduled_at` | nullable, date |
| `status` | draft/scheduled (create) · +published/failed (update) |
| `media.*` | image, max:10240 |

#### Tokens in social content

`{rsvp_link}` · `{event_link}` · `{event_name}` · `{event_time}` — replaced on save if `event_id` set

#### Connections

- Optional link to `Event`
- Uses `SocialAccount` for connected platforms (manual store, no OAuth flow documented in controller)
- Separate from `Message` model / `social_media` channel messages

---

### 5.9 RSVP (admin + public)

#### Admin

| | |
|---|---|
| **Route** | `GET /rsvp/dashboard` |
| **Controller** | `RsvpDashboardController` |
| **Shows** | Per-event RSVP breakdown chart (attended/pending/no-show/declined) |

#### Public (no auth)

| Route | Purpose |
|-------|---------|
| `GET /events/{event}/rsvp` | Landing — email lookup |
| `GET /rsvp/lookup` | Find attendee by email → redirect to signed RSVP |
| `GET /rsvp/{event}/{attendee}` | Respond form (signed URL, 30 days) |
| `POST /rsvp/.../respond` | Store response |
| `POST /webhook/sms/rsvp` | SMS reply parser (CSRF exempt) |

#### RSVP validation

| Field | Rules |
|-------|-------|
| `response` | required, `Yes\|No\|Maybe` |
| `response_channel` | nullable, `email\|sms\|web` |

#### Flow

```
RSVP created → attendee.rsvp_status updated via Rsvp::mapToAttendeeStatus()
  Yes → confirmed
  No  → declined
  Maybe → pending
```

#### Connections

- **← Attendees:** Required parent
- **→ Analytics:** RSVP rate metrics
- **→ Messages:** `{rsvp_link}` token in outbound comms

---

### 5.10 Surveys & feedback

#### Admin surveys

| | |
|---|---|
| **Routes** | `surveys` CRUD · `responses` · `report` |
| **Controller** | `SurveyController` |

#### Survey validation

| Field | Rules |
|-------|-------|
| `event_id` | required, exists, org-scoped |
| `name` | required, max:255 |
| `description` | nullable, max:2000 |
| `questions` | required array; each: id, type (text/rating/select/multiple), label, options, required |
| `thank_you_message` | nullable, max:1000 |
| `is_active` | nullable boolean |

#### Public feedback

| Route | Purpose |
|-------|---------|
| `GET /feedback/{event}/{attendee}` | Signed survey form |
| `POST /feedback/...` | Store responses |
| `GET /certificates/{feedback}` | Certificate view (auth) |

#### Connections

- Survey belongs to Event + Organization
- Feedback links attendee responses to survey
- `{feedback_link}` in messages points to signed feedback URL

---

### 5.11 Analytics

| | |
|---|---|
| **Routes** | `analytics.index` · `analytics/events/{event}` |
| **Controller** | `AnalyticsController` |

Read-only aggregation from `Message`, `CommunicationLog`, `Rsvp`, `Attendee`, `Channel`.

**KPIs:** messages sent, delivery rate, open rate, RSVP rate, attendance rate, social engagement  
**Charts:** bar by channel, 7-day line, pie (delivered/opened/RSVP/attended)  
**Filter:** optional `event_id` query param

#### Connections

- Reads from almost all comms + attendee modules
- No write validation

---

### 5.12 Queue monitor

| | |
|---|---|
| **Route** | `GET /queue/monitor` |
| **Controller** | `QueueMonitorController` |

Shows org messages with status `scheduled` (pending vs due), plus global `jobs` / `failed_jobs` table counts.

#### Connections

- **← Messages:** Primary data source
- **← Jobs:** Laravel queue tables

---

### 5.13 Organization profile

| | |
|---|---|
| **Routes** | `organization/profile` GET + PUT/POST |
| **Controller** | `OrganizationProfileController` |
| **Auth** | Update requires org admin |

#### Validation

| Field | Rules |
|-------|-------|
| `name` | required, max:255 |
| `email` | nullable, email |
| `phone` | nullable, max:50 |
| `address` | nullable, max:500 |
| `logo` | nullable, image, max:2048 KB |

---

### 5.14 Users (org team)

| | |
|---|---|
| **Routes** | `users` CRUD |
| **Controller** | `UserController` |
| **Auth** | Create/update/delete requires org admin |

#### Validation

| Field | Rules |
|-------|-------|
| `name` | required, max:255 |
| `email` | required, lowercase, email, unique (scoped on update) |
| `phone` | nullable, max:50 |
| `password` | required on create; nullable on update; confirmed; `Password::defaults()` |
| `role` | required, `admin\|staff` |

---

### 5.15 Notifications

| | |
|---|---|
| **Routes** | `notifications.index` · `notifications.mark-read` |
| **UI** | Partial in dashboard header |

Uses Laravel database notifications (e.g. `EventCreatedNotification`).

---

### 5.16 Super admin (platform operator)

| | |
|---|---|
| **Prefix** | `/super-admin` |
| **Middleware** | `system_admin` |
| **Controller** | `SuperAdminController` |

Screens: dashboard, organizations (toggle active, assign plan), events, activity log, system health, users.

Not part of the organizer panel — separate layout (`layouts/super-admin.blade.php`).

---

## 6. Background jobs & artisan commands

| Command | Purpose |
|---------|---------|
| `reminders:process` | 24h / 1h event reminders → creates Message → `SendMessageJob` |
| `messages:process-scheduled` | Dispatch due scheduled messages |
| `messages:process-queued` | Retry queued (offline) messages |
| `social:process-scheduled` | Publish due social posts |
| `beep-calls:process-scheduled` | Place due beep calls |
| `offline:simulate` | Toggle `ConnectionService` simulated offline |

| Job | Trigger |
|-----|---------|
| `SendMessageJob` | Message send-now, reminder command, scheduled processor |
| `PlaceBeepCallJob` | Beep call now / scheduled |
| `PublishSocialPostJob` | Social publish-now / scheduled |

---

## 7. Offline-ready behavior (current implementation)

| Layer | Behavior |
|-------|----------|
| `ConnectionService` | `APP_SIMULATE_OFFLINE` env or runtime cache → `isOnline() === false` |
| Message send-now | Offline → status `queued` + user message |
| `SendMessageJob` | Re-checks online; re-queues if offline |
| Event drafts | Comment placeholder for localStorage sync — **not implemented** |
| Analytics | Full metrics require logs; no explicit "partial offline" UI flag in admin |

---

## 8. End-to-end organizer journey (happy path)

```
1. Register org → freemium plan
2. Create event (draft or scheduled) → optional venue/link validation
3. Add attendees (manual / CSV / bulk) → usage contacts++
4. Create message templates (optional)
5. Configure email senders (optional)
6. Create message on event → pick channel, content, schedule
7. Send now OR wait for scheduler
8. Attendees receive links with {rsvp_link} / {feedback_link}
9. Public RSVP updates attendee status
10. RSVP dashboard + analytics reflect responses
11. Post-event: survey + feedback collection
12. (Premium) Schedule beep call reminders
13. Monitor queue + usage + upgrade plan if needed
```

---

## 9. Known gaps & inconsistencies (for review)

Please evaluate whether these should be fixed, by design, or deferred.

### Architecture / code quality

- [ ] **No FormRequest classes** — validation duplicated across controllers; harder to test and reuse
- [ ] **No Policy classes** — org scoping is manual `abort(403/404)` in every controller method
- [ ] **Usage limits tracked but not enforced** — org can exceed plan events/contacts without block or warning
- [ ] **Dual Alpine.js loading** — npm in `app.js` + CDN in dashboard layout
- [ ] **Admin UI not aligned with marketing design system** — dashboard still slate/indigo/DM Sans; marketing/auth use dusk/dawn/coral/Fraunces

### Product / data consistency

- [ ] **Marketing pricing ≠ database pricing** — landing page shows $75/$150/$249; DB has $19/$49/$99
- [ ] **SubscriptionPlanSeeder outdated** — still seeds free/starter/pro/enterprise with old limit keys; migration phase3 is source of truth
- [ ] **"Most popular" badge** — marketing says Pro; `subscription/plans` view marks Premium
- [ ] **Contacts vs attendees** — spec mentions org-wide `/contacts`; implementation is per-event `events/{event}/attendees` only
- [ ] **No unified event wizard** — spec describes 4-step wizard; current UI is separate create event + separate message flows
- [ ] **Social posts vs social_media messages** — two parallel systems (SocialPost model vs Message channel)
- [ ] **SendMessageJob requires email** — SMS/beep attendees without email are skipped in send loop
- [ ] **Channel delivery mostly stubbed** — jobs log to `CommunicationLog` but actual Mail/SMS/Twilio not wired

### Validation gaps

- [ ] **No duplicate attendee email check** per event on manual create
- [ ] **Subscription upgrade** — `plan_id` not validated with `exists:subscription_plans` rule (uses `findOrFail` only)
- [ ] **Social/event ownership** — `event_id` validated exists but org ownership checked inconsistently (some controllers check, social store does not explicitly verify event belongs to org before create — actually store doesn't check event org on create, only implicit via optional event_id)
- [ ] **Survey feedback** — minimal validation on `responses` array structure vs question schema
- [ ] **RSVP lookup** — no rate limiting or email validation beyond HTML `required`

### Security / ops

- [ ] **SMS webhook** — CSRF exempt, no signature verification documented
- [ ] **Signed URLs** — RSVP 30 days, feedback 90 days — confirm TTL policy
- [ ] **File uploads** — stored on `public` disk; confirm exposure model

---

## 10. Questions for reviewer

1. Should **usage limits** hard-block creates, soft-warn, or allow overage billing?
2. Should **contacts** be promoted to an org-level module with event assignment, or stay per-event?
3. Is the **dual social system** (SocialPost vs Message/social_media channel) intentional or should it merge?
4. Should validation move to **FormRequest** + **Policy** classes now or after UI redesign?
5. Does the **event creation flow** need a true multi-step wizard, or is the current split acceptable?
6. What is the correct **pricing source of truth** — marketing page or database migration?
7. Should **staff** role have narrower permissions (e.g. messages yes, billing no)?
8. For offline-first: priority of **localStorage draft sync** vs server-side queue only?
9. Are **merge field tokens** (`{name}`) the right format vs spec's `[[name]]` for editor safety?
10. Which admin screens are **highest priority** for design-system migration (dashboard shell first vs event detail first)?

---

## 11. File index (quick navigation)

| Area | Key files |
|------|-----------|
| Routes | `routes/web.php` |
| Dashboard layout | `resources/views/layouts/dashboard.blade.php` |
| Middleware | `app/Http/Middleware/EnsureUser*.php` |
| Models | `app/Models/{Organization,Event,Attendee,Message,Channel,Rsvp,BeepCall,SocialPost,Survey}.php` |
| Jobs | `app/Jobs/{SendMessageJob,PlaceBeepCallJob,PublishSocialPostJob}.php` |
| Offline | `app/Services/ConnectionService.php` |
| Structured email | `app/Support/StructuredMessageDocument.php` |
| Usage sidebar | `app/View/Composers/DashboardComposer.php` |
| Plans data | `database/migrations/2025_02_16_200003_refresh_subscription_plans_for_phase3.php` |

---

*Generated from codebase snapshot. Last reviewed modules: events, messages, attendees, RSVP, subscriptions, analytics, beep calls, social, surveys, auth.*
