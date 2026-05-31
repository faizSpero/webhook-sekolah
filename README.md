# webhook-sekolah

A Laravel 11 application for receiving and processing school (*sekolah*) webhook events from external systems such as Dapodik and SiMAK.

## Features

- **Secure webhook ingestion** — HMAC-SHA256 signature validation, timestamp window check, and idempotency/replay protection per event ID.
- **Async processing** — events are persisted immediately and dispatched to a database-backed queue for reliable, retry-enabled processing.
- **Domain models** — `WebhookEvent`, `Student`, `Teacher`, `SchoolClass` with soft-delete and upsert-by-external-ID semantics.
- **Admin panel** — browse, filter, and replay webhook events at `/admin/events`.
- **Test suite** — feature and unit tests covering signature validation, controller behaviour, job lifecycle, and domain logic.
- **Optional to-do widget** — client-side localStorage task list at `/todo` (no database required).

---

## Requirements

- PHP 8.2+
- Composer
- SQLite (default) **or** MySQL/PostgreSQL

---

## Setup

```bash
# 1. Install PHP dependencies
composer install

# 2. Copy environment file and generate key
cp .env.example .env
php artisan key:generate

# 3. Create the SQLite database (or configure DB_* vars in .env for MySQL/Postgres)
touch database/database.sqlite

# 4. Run migrations
php artisan migrate

# 5. Start the development server
php artisan serve
```

Open **http://localhost:8000/admin/events** and log in with the credentials from `.env` (`ADMIN_USER` / `ADMIN_PASSWORD`).

---

## Environment Variables

| Variable | Default | Description |
|---|---|---|
| `WEBHOOK_SECRETS` | *(empty)* | Comma-separated `source:secret` pairs, e.g. `dapodik:mysecret,simak:othersecret` |
| `WEBHOOK_MAX_AGE` | `300` | Max request age in seconds (replay protection). Set `0` to disable. |
| `WEBHOOK_QUEUE` | `webhooks` | Queue name for `ProcessWebhookEvent` jobs |
| `ADMIN_USER` | `admin` | HTTP Basic username for the admin panel |
| `ADMIN_PASSWORD` | `changeme` | HTTP Basic password for the admin panel |
| `QUEUE_CONNECTION` | `database` | Laravel queue driver (`database`, `redis`, `sync`) |
| `DB_CONNECTION` | `sqlite` | Database driver |

---

## Webhook Contract

### Endpoint

```
POST /api/webhook/{source}
```

`{source}` must match a key in `WEBHOOK_SECRETS` (e.g. `dapodik`, `simak`).

### Required headers

| Header | Description |
|---|---|
| `X-Webhook-Signature` | `sha256=<HMAC-SHA256 hex of the raw body>` |
| `X-Webhook-Timestamp` | Unix timestamp (integer) of when the request was sent |
| `X-Webhook-Event-Id` | Unique identifier for this event (used for deduplication) |
| `Content-Type` | `application/json` |

### Payload shape

```json
{
  "event_type": "student.enrolled",
  "data": {
    "id":         "EXT-123",
    "nisn":       "9876543210",
    "name":       "Budi Santoso",
    "class_code": "X-IPA-1"
  }
}
```

### Supported event types

| `event_type` | Action |
|---|---|
| `student.enrolled` | Upsert student; assign to class if `class_code` provided |
| `student.updated` | Update student fields |
| `student.withdrawn` | Soft-delete student |
| `teacher.created` | Upsert teacher |
| `teacher.updated` | Update teacher fields |
| `teacher.removed` | Soft-delete teacher |
| `class.created` | Upsert school class |
| `class.updated` | Update class fields |

---

## Running the Queue Worker

```bash
php artisan queue:work --queue=webhooks
```

Failed events can be replayed from the admin panel or via:

```bash
php artisan webhook:retry-failed
```

---

## Running Tests

```bash
php artisan test
# or
./vendor/bin/phpunit
```

---

## Admin Panel

| URL | Description |
|---|---|
| `/admin/events` | Paginated list of all webhook events (filter by status/source/type) |
| `/admin/events/{id}` | Full event detail with raw payload and headers |
| `/admin/events/{id}/replay` | Re-queue the event for processing |
| `/admin/agendas` | Agenda CRUD management |
| `/admin/scores` | Student score CRUD management |
| `/admin/scores/import` | CSV import for student scores |
| `/admin/suggestions` | Suggestions list and detail from bot submissions |

Authentication uses HTTP Basic (credentials from `.env`).

---

## To-Do Widget (optional)

A client-side task list with localStorage persistence is available at `/todo`. It requires no database and is independent of the webhook features.
