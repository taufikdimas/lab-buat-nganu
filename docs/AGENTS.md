# AGENTS.md — Instructions for the coding agent

## Context

WorkHub is an enterprise-style project & document management app, built as an **intentionally vulnerable training environment** for practicing web application penetration testing (OWASP Top 10 / WSTG). It must look, feel, and function like a real production SaaS product — the only thing setting it apart from a normal Laravel app is the bounded, documented set of weaknesses in `TECH_SPEC.md` §9.

**Read in this order before writing any code:**
1. `PRD.md` — what to build, roles, permissions, features, business rules.
2. `UI_SPEC.md` — how it should look and how the frontend is organized.
3. `DB_SCHEMA.md` — the exact schema and seed-data requirements.
4. `TECH_SPEC.md` — architecture, deployment, and the Intentional Vulnerability Map.

## Ground rules

1. **Build every feature completely and correctly.** This has to work and feel like a real product a company would actually use — not a stripped-down toy demo.
2. **The only security weaknesses that should exist are the ones explicitly listed in `TECH_SPEC.md` §9.** Do not invent additional ones. Do not "helpfully" harden anything beyond what's listed there — e.g. don't add rate limiting to login unless §9 asks for it, don't sanitize the one deliberately-raw search query, don't add the one omitted authorization check.
3. **Everything not on the vulnerability map follows normal Laravel best practice**: parameterized queries via Eloquent/query builder, escaped Blade output (`{{ }}`), enforced Policies, CSRF protection on all state-changing routes, validated Form Requests, hashed passwords, secure session config. The documented weaknesses need to stand out precisely because everything around them is solid — that's what makes them realistic and findable.
4. **No secrets in the repo.** Seed data uses fake/placeholder values only; nothing about the vulnerability set should ever involve leaking real credentials, API keys, or `.env` contents into version control.
5. **Keep the codebase readable.** A learner will eventually read the source to understand *why* a given weakness exists — no obfuscation, no cleverness for its own sake.
6. If a requirement in `PRD.md`/`UI_SPEC.md`/`DB_SCHEMA.md`/`TECH_SPEC.md` seems to conflict with another, `TECH_SPEC.md` §9 wins on anything security-behavior-related; `DB_SCHEMA.md` wins on anything data-shape-related.

## Suggested build order

1. Scaffold Laravel + Livewire + Flux + Tailwind, base auth (login/register/forgot-password).
2. Migrations from `DB_SCHEMA.md` §2, models with relationships and casts.
3. Seeders/factories producing the scenarios in `DB_SCHEMA.md` §4 (Dummy/Seed Data).
4. Policies — implemented per `PRD.md` §4.3, with the specific gaps from `TECH_SPEC.md` §9 where indicated.
5. Livewire components per `UI_SPEC.md` page-by-page, using Flux components first.
6. `/api/v1` endpoints per `TECH_SPEC.md` §4.
7. Admin panel.
8. Polish: empty states, loading states, dark mode, responsive check.

## Coding conventions

- PSR-12; run Laravel Pint before committing.
- Livewire components: PascalCase, organized by feature folder as in `UI_SPEC.md` §2 — not one giant component per page.
- Migrations: one table per migration file, named to match `DB_SCHEMA.md` exactly (table and column names are not to be improvised).
- Commits: conventional-commit style (`feat:`, `fix:`, `chore:`, `refactor:`).
- Never commit `.env`, `vendor/`, `node_modules/`, or `storage/app/documents/*` (except a `.gitignore` placeholder).

## Local development

```bash
composer install
cp .env.example .env
php artisan key:generate
# set DB_* in .env for a local MySQL instance
php artisan migrate --seed
npm install
npm run dev
php artisan serve
```

## Definition of done for a feature

- Matches the relevant acceptance criteria in `PRD.md` §5.
- UI matches `UI_SPEC.md` (correct Flux components, correct page structure, correct empty/loading states).
- Seed data (from `DB_SCHEMA.md` §4) actually exercises the feature end-to-end without manual setup.
- No security behavior introduced or removed beyond what `TECH_SPEC.md` §9 specifies.
