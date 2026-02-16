# ISATA - Event Management SaaS

A full SaaS web system for organizations to create events, manage attendees, and communicate through multiple channels (Email, SMS, Calendar, Social Media, Beep Calls).

## Phase 1 - Project Setup ✓

- [x] Laravel project
- [x] MySQL database
- [x] Authentication (Admin + Organizations)
- [x] Multi-tenant structure (organizations manage their own events)
- [x] Login page
- [x] Register organization page
- [x] Dashboard landing page

## Phase 2 - Organization & User Management ✓

- [x] Organizations: id, name, logo, email, phone, subscription_plan_id
- [x] Users: id, organization_id, name, email, phone, role (admin, staff), password
- [x] Subscription plans table
- [x] Organization profile page (edit, logo upload)
- [x] User management (CRUD)
- [x] Role assignment (admin, staff)

## Phase 3 - Subscription & Plans ✓

- [x] subscription_plans: Freemium, Basic, Pro, Premium
- [x] subscriptions table (organization, plan, status, dates)
- [x] organization_usage table (events, contacts, beep_calls per period)
- [x] Plan comparison page
- [x] Upgrade subscription page
- [x] Usage tracking dashboard

## Phase 4 - Event Management ✓

- [x] events table (organization_id, name, description, date, time, venue, meeting_link, status, created_by)
- [x] Create / Edit / Delete event
- [x] Draft save (offline-ready logic placeholder)
- [x] Event list table with status filter
- [x] Create event form
- [x] Event detail view
- [x] Calendar view (FullCalendar)

## Phase 5 - Attendee Management ✓

- [x] attendees table (event_id, name, email, phone, organization, rsvp_status)
- [x] CSV upload import
- [x] Manual add
- [x] Bulk import (paste data)
- [x] Attendee table with RSVP badges
- [x] Import modal

## Requirements

- PHP 8.2+
- Composer
- Node.js & npm
- MySQL 8.0+

## Setup

1. **Create MySQL database:**
   ```bash
   mysql -u root -e "CREATE DATABASE isata;"
   ```

2. **Configure environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Update `.env` with your MySQL credentials if needed (DB_USERNAME, DB_PASSWORD).

3. **Run migrations:**
   ```bash
   php artisan migrate
   ```

4. **Seed admin user (optional):**
   ```bash
   php artisan db:seed
   ```
   Admin login: `admin@isata.test` / `password`

5. **Install frontend dependencies & build:**
   ```bash
   npm install
   npm run build
   ```

6. **Start the server:**
   ```bash
   php artisan serve
   ```
   Visit http://127.0.0.1:8000

## Cron & Queue

For scheduled tasks (messages, reminders, sync) and background jobs:

**1. Add to crontab** (run every minute):
```bash
* * * * * cd /Users/cepsa/Desktop/ISATA && php artisan schedule:run >> /dev/null 2>&1
```

To edit crontab: `crontab -e`

**2. Run queue worker** (for SendMessageJob, notifications, etc.):
```bash
php artisan queue:work
```

Keep the worker running in a separate terminal or use a process manager (Supervisor, systemd).

## User Roles

- **Admin**: Full system access (organization_id = null, role = admin)
- **Organization Admin**: First user when registering an organization
- **Organization Member**: Additional users in an organization

## Default Credentials

- **Admin**: admin@isata.test / password
- **Organization**: Register at /register to create your organization
