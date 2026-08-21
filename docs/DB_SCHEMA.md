# WorkHub — Database Schema (MySQL 8)

Reference: `PRD.md` §6 (Entities), `TECH_SPEC.md` §9 (Intentional Vulnerability Map — some columns exist specifically to support a documented weakness; do not "improve" them beyond spec).

All tables use `bigint unsigned` auto-increment primary keys unless noted, plus standard Laravel `created_at`/`updated_at` timestamps unless noted otherwise. Soft deletes (`deleted_at`) are used where noted so deleted content can still appear in admin/audit views.

## 1. Entity Relationship Overview

```
User
 ├── owns many Project (Project.owner_id)
 ├── has many ProjectMember (membership rows)
 ├── owns many Document (Document.owner_id)
 ├── has many DocumentShare (as recipient)
 ├── creates many DocumentShareLink (as creator)
 ├── writes many Comment
 ├── receives many Notification
 └── appears in many Activity / AuditLog (as actor)

Project
 ├── has many ProjectMember (→ User, with role + status)
 ├── has many Document
 └── has many Activity

Document
 ├── belongs to Project
 ├── belongs to User (owner/uploader)
 ├── has many DocumentShare (→ User, with permission)
 ├── has many DocumentShareLink (token-based public access)
 └── has many Comment
```

## 2. Tables

### `users`
| Column | Type | Notes |
|---|---|---|
| id | bigint unsigned PK | |
| name | varchar(255) | not null |
| email | varchar(255) | not null, unique |
| email_verified_at | timestamp | nullable |
| password | varchar(255) | not null, hashed |
| avatar_url | varchar(2048) | nullable |
| system_role | enum('admin','user') | not null, default `user` |
| status | enum('active','suspended') | not null, default `active` |
| remember_token | varchar(100) | nullable |
| created_at / updated_at | timestamp | |

### `projects`
| Column | Type | Notes |
|---|---|---|
| id | bigint unsigned PK | |
| name | varchar(255) | not null |
| description | text | nullable |
| owner_id | bigint unsigned FK → users.id | not null |
| status | enum('active','archived') | not null, default `active` |
| created_at / updated_at | timestamp | |
| deleted_at | timestamp | nullable (soft delete) |

### `project_members`
| Column | Type | Notes |
|---|---|---|
| id | bigint unsigned PK | |
| project_id | bigint unsigned FK → projects.id | not null |
| user_id | bigint unsigned FK → users.id | not null |
| role | enum('owner','editor','viewer') | not null |
| status | enum('active','pending') | not null, default `pending` |
| invited_by | bigint unsigned FK → users.id | nullable |
| invited_at | timestamp | nullable |
| joined_at | timestamp | nullable, set when status flips to `active` |
| created_at / updated_at | timestamp | |

Unique constraint: `(project_id, user_id)`.

### `documents`
| Column | Type | Notes |
|---|---|---|
| id | bigint unsigned PK | |
| project_id | bigint unsigned FK → projects.id | not null |
| owner_id | bigint unsigned FK → users.id | not null (uploader) |
| name | varchar(255) | not null |
| description | text | nullable |
| file_path | varchar(2048) | not null — internal storage path, never the original filename |
| original_filename | varchar(255) | not null — for display/download only |
| mime_type | varchar(150) | not null |
| size_bytes | bigint unsigned | not null |
| visibility | enum('project','private') | not null, default `project` |
| created_at / updated_at | timestamp | |
| deleted_at | timestamp | nullable (soft delete) |

### `document_shares`
| Column | Type | Notes |
|---|---|---|
| id | bigint unsigned PK | |
| document_id | bigint unsigned FK → documents.id | not null |
| user_id | bigint unsigned FK → users.id | not null (recipient) |
| permission | enum('viewer','editor') | not null |
| shared_by | bigint unsigned FK → users.id | not null |
| created_at / updated_at | timestamp | |

Unique constraint: `(document_id, user_id)`.

### `document_share_links`
| Column | Type | Notes |
|---|---|---|
| id | bigint unsigned PK | |
| document_id | bigint unsigned FK → documents.id | not null |
| token | varchar(64) | not null, unique — see `TECH_SPEC.md` §9 for token-generation spec |
| created_by | bigint unsigned FK → users.id | not null |
| expires_at | timestamp | nullable (no expiry if null) |
| revoked_at | timestamp | nullable |
| access_count | int unsigned | not null, default 0 |
| last_accessed_at | timestamp | nullable |
| created_at / updated_at | timestamp | |

### `comments`
| Column | Type | Notes |
|---|---|---|
| id | bigint unsigned PK | |
| document_id | bigint unsigned FK → documents.id | not null |
| user_id | bigint unsigned FK → users.id | not null |
| body_raw | text | not null — original markdown-lite input as typed |
| body_rendered | text | not null — rendered output; see `TECH_SPEC.md` §9 for rendering spec |
| created_at / updated_at | timestamp | |
| deleted_at | timestamp | nullable (soft delete) |

### `notifications`
| Column | Type | Notes |
|---|---|---|
| id | bigint unsigned PK | |
| user_id | bigint unsigned FK → users.id | not null (recipient) |
| type | varchar(150) | not null, e.g. `project.invited`, `document.shared`, `comment.created`, `member.role_changed` |
| data | json | not null — payload for rendering (project/document id, actor, etc.) |
| read_at | timestamp | nullable |
| created_at | timestamp | |

### `activities`
| Column | Type | Notes |
|---|---|---|
| id | bigint unsigned PK | |
| project_id | bigint unsigned FK → projects.id | nullable |
| document_id | bigint unsigned FK → documents.id | nullable |
| user_id | bigint unsigned FK → users.id | not null (actor) |
| action | varchar(150) | e.g. `project.created`, `document.uploaded`, `member.invited` |
| description | varchar(500) | human-readable summary |
| meta | json | nullable |
| created_at | timestamp | |

### `audit_logs`
| Column | Type | Notes |
|---|---|---|
| id | bigint unsigned PK | |
| actor_id | bigint unsigned FK → users.id | nullable (admin/system actor) |
| action | varchar(150) | e.g. `user.suspended`, `content.deleted`, `settings.updated` |
| target_type | varchar(150) | e.g. `User`, `Project`, `Document` |
| target_id | bigint unsigned | nullable |
| meta | json | nullable |
| ip_address | varchar(45) | nullable |
| user_agent | varchar(255) | nullable |
| created_at | timestamp | |

### `system_settings`
| Column | Type | Notes |
|---|---|---|
| id | bigint unsigned PK | |
| key | varchar(150) | unique, e.g. `avatar_allowed_domains`, `max_upload_size_mb` |
| value | text | |
| updated_by | bigint unsigned FK → users.id | nullable |
| updated_at | timestamp | |

### Standard Laravel tables (no custom changes)
`password_reset_tokens`, `sessions`, `personal_access_tokens` (Sanctum, for `/api/v1`), `jobs`, `failed_jobs`, `cache`.

## 3. Recommended Indexes

- `projects(owner_id)`, `projects(status)`
- `project_members(project_id, status)`, `project_members(user_id)`
- `documents(project_id, visibility)`, `documents(owner_id)`
- `document_shares(document_id)`, `document_shares(user_id)`
- `document_share_links(token)` unique, `document_share_links(document_id)`
- `comments(document_id)`
- `notifications(user_id, read_at)`
- `activities(project_id)`, `activities(document_id)`
- `audit_logs(target_type, target_id)`

## 4. Dummy / Seed Data Spec

Seeders must produce a working demo **and** already exercise every access-control edge case, so testing can start immediately after `php artisan migrate --seed`.

- **Users:** 1 admin (`admin@workhub.test`), ~12–15 regular users with realistic names/emails, all `status = active` except **1 suspended user** (for the "suspended can't log in" rule).
- **Projects:** 5–6 projects, each with an `owner` and 2–5 members with mixed roles (`editor`, `viewer`).
  - At least **1 archived project**.
  - At least **1 project with a `pending` (not-yet-accepted) member** — used to verify pending members have zero access.
- **Documents:** 3–8 per project, mixed `visibility`.
  - At least **1 private document owned by a non-owner project member, not shared with anyone** — only the uploader and the project owner should see it.
  - At least **1 private document explicitly shared with one specific viewer** via `document_shares`.
- **Document share links:**
  - At least **1 valid, non-expiring link**.
  - At least **1 expired link** (`expires_at` in the past).
  - At least **1 revoked link** (`revoked_at` set).
- **Comments:** several plain-text comments, plus **at least one comment using markdown-lite syntax** (bold/italic/link) so the renderer is visibly exercised in the seeded UI.
- **Notifications:** a mix of read and unread, across a few users.
- **Activities / Audit logs:** enough entries to populate the Activity tab and Admin → Audit Logs table with realistic history (project created, member invited, document uploaded, role changed, etc.).
- **System settings:** seed `avatar_allowed_domains` with a couple of example domains and `max_upload_size_mb` with a reasonable default (e.g. `20`).

Use Laravel model factories + a `DatabaseSeeder` that composes feature-specific seeders (`UserSeeder`, `ProjectSeeder`, `DocumentSeeder`, `CommentSeeder`, `SystemSettingsSeeder`) so individual pieces can be re-run independently during development.
