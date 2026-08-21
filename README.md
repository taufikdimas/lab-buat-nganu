# WorkHub

WorkHub is an enterprise-style project and document collaboration app built with Laravel, Livewire, Flux UI, Tailwind CSS, MySQL, and Sanctum. It includes project membership, role-aware dashboards, document upload and sharing, comments, search, notifications, administration, and a JSON API.

> **Training environment only.** This repository intentionally contains the bounded security weaknesses documented in [`docs/TECH_SPEC.md`](docs/TECH_SPEC.md). Run it only on a private lab network with fictional data. Never expose it to the public internet or use it for real users.

## Stack

- PHP 8.3+
- Laravel 13
- Livewire 4 + Flux UI 2
- Tailwind CSS 4 + Vite
- MySQL 8
- Laravel Sanctum API tokens
- Database-backed sessions, cache, queues, notifications, and activity

The product specification originally names Livewire 3. The implementation uses Livewire 4 because it is the current stable version supported by the official Laravel 13 Livewire starter kit; the component architecture remains the same.

## Local setup

Requirements: PHP 8.3+, Composer, Node.js 20+, NPM, and MySQL 8.

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Create the database, then configure `DB_*` in `.env`:

```sql
CREATE DATABASE workhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Build and seed the application:

```bash
php artisan migrate --seed
npm install
npm run build
php artisan storage:link
```

Start local development:

```bash
composer run dev
```

Or run only the web server:

```bash
php artisan serve
```

Open `http://localhost:8000`.

## Demo accounts

All seeded accounts use password `password`.

| Persona   | Email                    | Notes                       |
| --------- | ------------------------ | --------------------------- |
| Admin     | `admin@workhub.test`     | Full administration area    |
| Owner     | `arif@workhub.test`      | Owns Website Redesign       |
| Owner     | `nadia@workhub.test`     | Owns Mobile Banking 2.0     |
| Member    | `bima@workhub.test`      | Mixed roles across projects |
| Suspended | `suspended@workhub.test` | Login must be rejected      |

The seed contains 14 users, 6 projects, 24 documents, mixed membership roles, a pending invitation, private documents, valid/expired/revoked links, comments, notifications, activity, and audit logs.

Reset the demo at any time:

```bash
php artisan migrate:fresh --seed
```

## API

The API is available under `/api/v1`. Authenticate first:

```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@workhub.test","password":"password","device_name":"local"}'
```

Send the returned token as `Authorization: Bearer <token>`. Main resources include projects, members, documents, shares, share links, comments, search, and notifications. Run `php artisan route:list --path=api/v1` for the exact routes.

## Quality checks

```bash
php artisan test
vendor/bin/pint --test
npm run build
php artisan view:cache
```

## Deployment

An nginx lab-server configuration is provided at [`deploy/nginx/workhub.conf`](deploy/nginx/workhub.conf). For Ubuntu deployment:

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Run `php artisan queue:work` under Supervisor. Point nginx only to `public/`; documents remain behind Laravel-controlled download routes.

## Documentation

- [`docs/PRD.md`](docs/PRD.md) — product requirements and permissions
- [`docs/UI_SPEC.md`](docs/UI_SPEC.md) — interface architecture and visual system
- [`docs/DB_SCHEMA.md`](docs/DB_SCHEMA.md) — schema and seed scenarios
- [`docs/TECH_SPEC.md`](docs/TECH_SPEC.md) — architecture, API, deployment, and vulnerability map
- [`docs/AGENTS.md`](docs/AGENTS.md) — implementation constraints

Email: admin@workhub.test
Password: password

Email: arif@workhub.test
Password: password
