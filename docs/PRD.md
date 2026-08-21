# WorkHub — Product Requirements Document (PRD)

## 0. Purpose of this document set

This PRD, together with `UI_SPEC.md`, `DB_SCHEMA.md`, `TECH_SPEC.md`, and `AGENTS.md`, is the single source of truth for an AI coding agent (e.g. Claude Code) to build **WorkHub** end-to-end, with minimal further clarification needed.

**What WorkHub is:** a project & document management SaaS application, built to look, feel, and function like a real enterprise product — used as an **intentionally vulnerable training environment** for practicing web application penetration testing (OWASP Top 10 2021 and OWASP WSTG). It is meant to be deployed on a private/lab server for security training purposes, not for handling real user data.

**Agent framing note:** every feature described here must be built fully and correctly. Only the specific, documented weaknesses listed in `TECH_SPEC.md` §9 ("Intentional Vulnerability Map") should exist. Do not add unrelated shortcuts, and do not silently hardened away a documented weakness — see `AGENTS.md` for the full ground rules.

---

## 1. Goals

- Deliver a fully functional project + document collaboration platform (create/manage projects, invite members, upload/share/comment on documents).
- Reproduce a modern, enterprise-SaaS-grade UI/UX — not a generic admin-panel look.
- Provide a rich enough resource/relationship graph (membership status, document visibility, project status, share tokens) that access-control bugs emerge naturally from feature interactions, matching real-world development mistakes.
- Ship with seed data that already exercises every vulnerability surface, so testing can start immediately after `migrate --seed`.

## 2. Non-goals (out of scope for v1)

- Real-time features (WebSocket push) — notifications are polling-based only.
- Rich-text WYSIWYG comment editor — a hand-rolled markdown-lite renderer is used instead (see rationale in `TECH_SPEC.md`).
- Multi-tenancy (multiple companies/orgs in one instance).
- Billing/payments.
- Native mobile app.
- Two-factor authentication (may be added later as a separate exercise).

## 3. Personas

| Persona | Description                                                                                                      |
| ------- | ---------------------------------------------------------------------------------------------------------------- |
| Admin   | Manages the whole instance: users, all projects, all documents, audit logs, system settings.                     |
| Owner   | Creates projects, manages membership, has full control within their project(s).                                  |
| Editor  | Works inside a project: uploads/manages documents, comments. Cannot manage membership.                           |
| Viewer  | Read-only participant. May also access a single document externally via a public share link, without an account. |

## 4. Roles & Permissions

### 4.1 System roles

| Role    | Description                                                |
| ------- | ---------------------------------------------------------- |
| `admin` | Full control over the entire system and all users.         |
| `user`  | Regular user, scoped to the projects they are a member of. |

### 4.2 Project roles

One user can hold a **different** project role on each project they belong to.

| Role     | Description                                                                       |
| -------- | --------------------------------------------------------------------------------- |
| `owner`  | Full control of the project, including membership and deletion.                   |
| `editor` | Manages document content. Cannot manage membership or delete/archive the project. |
| `viewer` | Read-only access to project content.                                              |

### 4.3 Permission matrix

| Action                                          |         Admin         |          Owner          |      Editor      |         Viewer          |
| ----------------------------------------------- | :-------------------: | :---------------------: | :--------------: | :---------------------: |
| View project                                    |          ✅           |           ✅            |        ✅        |           ✅            |
| Edit project                                    |          ✅           |           ✅            |        ❌        |           ❌            |
| Archive project                                 |          ✅           |           ✅            |        ❌        |           ❌            |
| Delete project                                  |          ✅           |           ✅            |        ❌        |           ❌            |
| View members                                    |          ✅           |           ✅            |        ✅        |           ✅            |
| Invite member                                   |          ✅           |           ✅            |        ❌        |           ❌            |
| Remove member                                   |          ✅           |           ✅            |        ❌        |           ❌            |
| Change member role                              |          ✅           |           ✅            |        ❌        |           ❌            |
| Transfer ownership                              |          ✅           | ✅ (only current owner) |        ❌        |           ❌            |
| Upload document                                 |          ✅           |           ✅            |        ✅        |           ❌            |
| Upload document to an **archived** project      |          ✅           |           ❌            |        ❌        |           ❌            |
| View document (project-visibility)              |          ✅           |           ✅            |        ✅        |           ✅            |
| View document (private visibility)              |          ✅           |  ✅ (if project owner)  | ❌ unless shared |    ❌ unless shared     |
| Preview / download document                     |          ✅           |           ✅            |        ✅        | ✅ (if visible to them) |
| Rename / delete document                        |          ✅           |           ✅            |        ✅        |           ❌            |
| Share document with a user                      |          ✅           |           ✅            |        ✅        |           ❌            |
| Generate public share-link for a document       |          ✅           |           ✅            |        ✅        |           ❌            |
| Access a document via a valid public share-link | N/A (no login needed) |            —            |        —         |            —            |
| Comment on a visible document                   |          ✅           |           ✅            |        ✅        |           ✅            |
| Edit/delete own comment                         |          ✅           |           ✅            |        ✅        |           ✅            |
| Delete any comment                              |          ✅           |           ❌            |        ❌        |           ❌            |
| Access admin panel                              |          ✅           |           ❌            |        ❌        |           ❌            |

### 4.4 Business rules

- A regular user can only access projects where they are a member with `status = active` (an invite with `status = pending` grants no access until accepted).
- `owner` has full control over their project; only `owner` or `admin` can delete a project.
- `editor` manages documents but never membership.
- `viewer` never writes, only reads.
- Only `owner` (or `admin`) can change project membership or transfer ownership.
- A user cannot access another project's documents without explicit permission (membership, share, or valid share-link).
- Document sharing can only be granted to a valid, existing user account.
- A suspended user (`status = suspended`) cannot use the application at all, even if their session is still technically valid.
- `admin` can access every resource in the system.
- A user can only edit their own profile.
- A user can only delete their own comments, except `admin` who can delete any comment.
- An **archived** project is read-only: no upload, no edit, no new members, regardless of role (except admin/owner per matrix above for admin-only override where noted).
- A **private** document is visible only to: the project owner, the document's own uploader, and any user it has been explicitly shared with. It is _not_ visible to other project members by default.
- A public share-link grants **read-only** access to exactly one document, never to the rest of the project, and never requires login.

## 5. Features

### 5.1 Authentication

- Register, Login/Logout, Forgot password, Change password, Session management.
- Acceptance: a suspended user cannot log in (or is immediately logged out if already authenticated); sessions expire per Laravel defaults.

### 5.2 User / Profile

- Edit profile (name, etc.), upload avatar (file), import avatar from a URL, view own account activity, notification list.
- Acceptance: avatar-from-URL is validated against an admin-configured allow-list of domains (see `system_settings`).
- Role-based Dashboard, Masing-masing role lihat dashboard berbeda.

### 5.3 Project

- Create, view, edit, archive, delete project; project dashboard; project activity feed.
- Acceptance: archived projects reject all write actions (upload, edit, invite) except admin override actions defined in the matrix.

### 5.4 Project Members

- Invite member (creates a `pending` membership), remove member, change project role, transfer ownership, view members list (with status badge: active/pending).
- Acceptance: a `pending` member has zero access to the project until they accept the invite (status flips to `active`).

### 5.5 Documents

- Upload, view/list, preview/read, download, rename, delete document; document metadata panel; per-document activity log.
- Acceptance: document list is filtered by the combination of (a) project membership, (b) document visibility (`project`/`private`), and (c) explicit shares — all three conditions apply together.

### 5.6 Document Sharing

- Share a document with a specific user (`viewer` or `editor` permission), revoke access, list users with access.
- Generate a public share-link (optional expiry), revoke a share-link, view share-link access count/last-accessed.
- Acceptance: a share-link, when valid, grants read-only access to exactly that one document without login, via `/share/{token}`.

### 5.7 Comments

- Add comment (markdown-lite: bold/italic/link only), edit own comment, delete own comment, view all comments on a document.
- Acceptance: only the comment author (or admin) can edit/delete a comment.

### 5.8 Search

- One global search box (`GET /search?q=`) querying Projects, Documents, and Users at once, scoped to what the requesting user is allowed to see.
- Acceptance: results always respect the requesting user's access (a user never sees a project/document they can't otherwise open, via search).

### 5.9 Notifications

- Polling-based list (invited to project, added as member, document shared, new comment, project role changed). Mark as read.

### 5.10 Admin

- User management (CRUD, change system role, suspend/activate), view all projects (including private), view all documents (global file manager), delete any content, audit log viewer, system settings (avatar URL allow-list, max upload size, etc.).

## 6. Entities (detail in `DB_SCHEMA.md`)

`User`, `Project`, `ProjectMember`, `Document`, `DocumentShare`, `DocumentShareLink`, `Comment`, `Notification`, `Activity`, `AuditLog`, `SystemSetting`.

## 7. Success Criteria

- Every feature in §5 works correctly for the "happy path" of every applicable role.
- Seed data matches the scenarios described in `DB_SCHEMA.md` §"Dummy / Seed Data" (archived project, pending invite, private document, expired/revoked/valid share-links, markdown-lite comment, etc.) so the app is testable immediately after seeding.
- The application deploys cleanly on Ubuntu + nginx + PHP-FPM + MySQL per `TECH_SPEC.md`.
- UI matches `UI_SPEC.md` (Flux-first, modern enterprise look).
