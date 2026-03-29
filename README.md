# EventBooking

Laravel-based event and ticket booking API. Authentication uses **Laravel Sanctum** (Bearer token).

## Requirements

- PHP 8.2+
- Composer
- MySQL / MariaDB (or SQLite for local development)
- `ext-pdo` and your database driver

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configure the database in `.env` (see the example below), then:

```bash
php artisan migrate
php artisan db:seed
```

If you use the queue (e.g. for notifications):

```bash
php artisan queue:work
```

Development server:

```bash
php artisan serve
```

API base URL: `http://localhost:8000/api`

## Sanctum

- For mobile / stateless clients: the `Authorization: Bearer {token}` header is enough (no cookie required).
- For SPA + same-domain cookie sessions, configure `config/sanctum.php` and `SANCTUM_STATEFUL_DOMAINS` in `.env` per the official [Sanctum documentation](https://laravel.com/docs/sanctum).

## `.env` summary

The variables below are typically important for this project. See `.env.example` in the repo for the full template.

```env
APP_NAME=EventBooking
APP_URL=http://localhost:8000
APP_KEY=   # php artisan key:generate

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=event_booking
DB_USERNAME=root
DB_PASSWORD=

# Cache / session (tables created by migrations)
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

## Seed data

When you run `php artisan db:seed` (`DatabaseSeeder` → `EventBookingSeeder`):

| Entity    | Count |
|-----------|-------|
| Admin     | 2     |
| Organizer | 3     |
| Customer  | 10    |
| Event     | 5     |
| Ticket    | 15    |
| Booking   | 20 (confirmed) |
| Payment   | 20 (one successful payment per booking) |

**Password for all seeded users:** `password`

Fixed email addresses:

- `admin1@eventbooking.test`, `admin2@eventbooking.test`
- `organizer1@eventbooking.test` … `organizer3@eventbooking.test`

## API response format

All JSON responses are wrapped by `App\Http\Responses\ApiResponse`:

**Success:**

```json
{
  "success": true,
  "message": "OK",
  "data": { },
  "errors": null
}
```

**Error:**

```json
{
  "success": false,
  "message": "Description",
  "data": null,
  "errors": { "field": ["message"] }
}
```

**Paginated list (`data`):** `items`, `meta` (current_page, last_page, per_page, total, …), `links`.

Records are returned via `EventResource`, `BookingResource`, etc.

## Authentication

| Action   | Endpoint              | Body |
|----------|-----------------------|------|
| Register | `POST /api/register`  | `name`, `email`, `password`, `password_confirmation`, `phone?` |
| Login    | `POST /api/login`     | `email`, `password` |
| Logout   | `POST /api/logout`    | Bearer token |
| Profile  | `GET /api/me`         | Bearer token |

Use the `data.token` field from login/register responses as `Authorization: Bearer ...` on subsequent requests.

## Roles and endpoints (summary)

- **Public:** `GET /api/events`, `GET /api/events/{event}`
- **Organizer or Admin:** create events; update/delete own events; related ticket CRUD (where applicable)
- **Customer or Admin:** own bookings, book tickets, cancel, pay, view own payment

Import `documentation/EventBooking.postman_collection.json` into Postman for full paths and example requests.

## Tests

```bash
cp .env.testing.example .env.testing
# Configure the test database in .env.testing

php artisan test
```

## Postman

Collection: [documentation/EventBooking.postman_collection.json](documentation/EventBooking.postman_collection.json)

Import: Postman → Import → select this file. Set collection variables `base_url` (e.g. `http://localhost:8000/api`) and `token` (after login).
