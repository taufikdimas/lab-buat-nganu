# WorkHub — Technical Specification

Reference: `PRD.md` (features), `UI_SPEC.md` (frontend), `DB_SCHEMA.md` (data). This document covers architecture, deployment, and — because this app exists to be tested — an explicit map of the intentional weaknesses it should contain.

## 1. Stack

| Layer | Choice |
|---|---|
| Language/runtime | PHP 8.3+ |
| Framework | Laravel (latest stable release at setup time) |
| Frontend | Blade + Livewire 3 + Flux UI + Tailwind CSS v4 |
| Database | MySQL 8 |
| Cache / Queue | Database driver (no Redis dependency required) |
| Auth (web) | Laravel session auth via the Livewire starter kit (Breeze-style) |
| Auth (API) | Laravel Sanctum, personal access tokens, for the `/api/v1` surface |
| File storage | Local disk (`storage/app/documents`) via the `Storage` facade, so it can move to S3-compatible storage later without code changes |
| Web server | nginx (reverse proxy) + PHP-FPM |
| OS | Ubuntu 22.04/24.04 LTS |

## 2. Architecture

- Standard Laravel MVC, with Livewire components handling all interactivity per `UI_SPEC.md`.
- **Authorization:** Laravel Policies (`ProjectPolicy`, `ProjectMemberPolicy`, `DocumentPolicy`, `CommentPolicy`) registered in `AuthServiceProvider`, invoked via `$this->authorize()` in Livewire components/controllers and `@can` in Blade. Every action in the permission matrix (`PRD.md` §4.3) maps to a policy method — **except** the specific gaps documented in §9 below, which must be implemented exactly as specified.
- **Validation:** Form Request classes for all state-changing input.
- **Service layer:** thin, only where logic is shared between the web UI and the `/api/v1` surface (e.g. `DocumentService`, `SearchService`, `ShareLinkService`).
- **Dual surface:** the Blade/Livewire web app, plus a separate JSON API under `/api/v1/*` authenticated with Sanctum tokens — giving a pure-API testing surface (Burp/Postman-friendly) independent of the HTML forms.

## 3. Folder Structure (proposal)

```
app/
  Livewire/            (per UI_SPEC.md §2)
  Models/
  Policies/
  Services/
  Http/
    Controllers/Api/V1/   (thin controllers delegating to Services)
    Requests/
  Notifications/
database/
  migrations/
  factories/
  seeders/
resources/
  views/
    components/         (shared Blade components)
    livewire/
routes/
  web.php
  api.php
```

## 4. API Surface (high level, `/api/v1`)

| Resource | Endpoints |
|---|---|
| Auth | `POST /login`, `POST /logout`, `POST /register` |
| Projects | `GET/POST /projects`, `GET/PATCH/DELETE /projects/{id}` |
| Members | `GET/POST /projects/{id}/members`, `PATCH/DELETE /projects/{id}/members/{userId}` |
| Documents | `GET/POST /projects/{id}/documents`, `GET/PATCH/DELETE /documents/{id}`, `GET /documents/{id}/download` |
| Sharing | `POST/DELETE /documents/{id}/shares`, `POST /documents/{id}/share-links`, `DELETE /share-links/{id}` |
| Comments | `GET/POST /documents/{id}/comments`, `PATCH/DELETE /comments/{id}` |
| Search | `GET /search?q=` |
| Notifications | `GET /notifications`, `POST /notifications/{id}/read` |
| Admin | `/admin/*` mirrors above scoped to admin-only policies |
| Public | `GET /share/{token}` — no auth required, single-document read-only |

## 5. Notifications & Activity

Database-backed only. The topbar bell polls `GET /notifications` on a short interval via Livewire's polling (`wire:poll`) — no WebSocket/broadcasting infrastructure required.

## 6. Deployment (Ubuntu + nginx)

**nginx server block (skeleton, adapt domain/paths):**
```nginx
server {
    listen 80;
    server_name workhub.local;
    root /var/www/workhub/public;

    index index.php;

    client_max_body_size 25m;   # keep aligned with system_settings.max_upload_size_mb

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

- Never point nginx's `root`/`alias` directly at the document storage directory — documents are served through a Laravel-controlled route (`Storage::download()` or a streamed response), not as static files.
- PHP-FPM pool: standard defaults are fine for a training instance.
- Queue worker: run `php artisan queue:work` under Supervisor so it restarts automatically.
- `.env`: standard `DB_*` MySQL credentials, `APP_URL`, `FILESYSTEM_DISK=local`, `QUEUE_CONNECTION=database`, `MAIL_MAILER=log` for local/dev.
- Deploy steps: `composer install --no-dev --optimize-autoloader`, `npm ci && npm run build`, `php artisan migrate --force`, `php artisan storage:link`, `php artisan config:cache`, restart PHP-FPM and the queue worker.

## 7. Testing

Pest/PHPUnit feature tests covering the happy path of each core flow in `PRD.md` §5 (auth, project CRUD, membership, document CRUD, sharing, comments, search, notifications). These tests validate that the *intended* behavior works — they are not expected to cover the items in §9, which are for a human tester to discover.

## 8. Intentional Vulnerability Map

This is the authoritative list of weaknesses the app should contain. Everything **not** on this list should follow normal Laravel best practice (parameterized queries, escaped output, enforced policies, CSRF protection, etc.) — see `AGENTS.md` for the ground rule that keeps this bounded and realistic.

| # | OWASP / WSTG category | Feature | Deliberate anti-pattern | Where |
|---|---|---|---|---|
| 1 | A01 Broken Access Control | Document preview/list | Filtering by visibility/membership/share must be implemented as three separate checks combined in the query/policy — during initial implementation, apply it correctly for the *list* endpoint but omit the `private`-visibility check specifically on the single-document *show* route, so a direct link to a private document's ID bypasses the list-level filtering (classic IDOR). | `DocumentPolicy@view`, `Documents/DocumentDetail` Livewire component |
| 2 | A01 Broken Access Control | Archived project | Only enforce the archived/read-only rule in the UI (hide the upload button) — do not also enforce it server-side in the upload handler, so a direct POST still succeeds. | Document upload Form Request / Livewire action |
| 3 | A01 Broken Access Control | Pending membership | Membership queries should check `role` but not always `status = active`; leave at least one access-check path (e.g. the Documents list) that forgets the `status` condition, so a `pending` invite grants premature access. | `ProjectMember` scope usage in `DocumentPolicy` |
| 4 | A03 Injection (SQLi) | Global search (`/search?q=`) | Implement Projects and Documents search with Eloquent/query builder (parameterized, safe). Implement the Users portion with a raw query built via string interpolation of `q` (justified in-code as "needs a complex join"), which is unsafe. | `SearchService@searchUsers` |
| 5 | A03 Injection (Stored XSS) | Comments (markdown-lite) | The hand-written markdown-lite parser's output is rendered with Blade's unescaped `{!! $comment->body_rendered !!}` instead of `{{ }}`, and the parser itself doesn't strip arbitrary HTML/attributes from links. | `Comments/CommentThread` Livewire component, comment renderer service |
| 6 | A04 Insecure Design (Mass Assignment) | Change member role | The "change role" endpoint accepts the full request body into an `update()` call rather than only the validated `role` field, so an extra `is_owner`/`role` override field in the payload can be applied unintentionally. | `ProjectMemberController@updateRole` / equivalent Livewire action |
| 7 | A02 Cryptographic Failures | Document share-link token | Generate the token with a weak, guessable scheme (e.g. `md5($documentId . time())` or a short sequential value) instead of Laravel's `Str::random(40)`. | `ShareLinkService@generate` |
| 8 | A07 Identification & Auth Failures | Public share-link expiry | The `GET /share/{token}` handler must check `revoked_at`, but the initial implementation should omit the `expires_at` check, so expired links keep working. | Public share controller |
| 9 | A07 Identification & Auth Failures | Login | No rate limiting/throttling on the login endpoint. | `routes/web.php` login route (omit Laravel's default throttle middleware) |
| 10 | A07 Identification & Auth Failures | Password reset | Reset tokens are created with Laravel's default mechanism but the expiry check is not enforced in the reset handler, so an old reset link still works. | Password reset controller |
| 11 | A10 SSRF | Avatar import from URL | Validate the URL's host against `system_settings.avatar_allowed_domains` using a naive substring check (`str_contains($url, $allowedDomain)`) rather than parsing the actual hostname, so it's bypassable via lookalike/subdomain tricks or an open redirect on an allowed domain. | `Profile/AvatarForm` Livewire component |
| 12 | A05 Security Misconfiguration | nginx | The example config in §6 above is the *safe* baseline (documents never served as static files, dotfiles denied). For the intentionally weaker build, omit the "deny dotfiles" block and skip adding standard security headers (CSP, `X-Content-Type-Options`, `X-Frame-Options`) at the nginx level. | nginx server block |
| 13 | A01 Broken Access Control / A05 | File upload path & type | Store uploads under a path derived from the original filename rather than a generated UUID, and validate file type only via the client-supplied MIME type header rather than server-side content inspection. | `DocumentService@store` |

Each row above should be implemented **exactly as described** — not more broadly, not more narrowly — and nowhere else. See `AGENTS.md` §"Ground rules" before implementing this section.
