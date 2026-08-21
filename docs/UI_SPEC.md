# WorkHub — UI Specification

Reference: `PRD.md` for feature scope. This document defines how it should look, how it's structured, and which components to use.

## 1. Design Principles

- **Modern enterprise SaaS**, not a generic Bootstrap-y admin template — think in the direction of Linear/Notion/Vercel-style dashboards: generous whitespace, subtle borders instead of heavy shadows/boxes, a neutral base palette (zinc/slate) plus a single accent color reserved for primary actions.
- Color is used *purposefully* — mainly for status and role badges — not decoratively.
- Desktop-first (this is an internal tool) but usable down to tablet width (~768px). Mobile-perfect polish is not required for v1.
- Dark mode supported out of the box via Flux's built-in appearance handling.
- Every list/table view has a real empty state (icon + short text + primary CTA), not a blank space.

## 2. Stack & Frontend Architecture

**Frontend/UI:**
- Laravel Blade
- Livewire 3
- Flux UI
- Tailwind CSS v4

**UI Architecture rules:**
- Use Livewire for all interactive components (anything that reads/writes server state: forms, tables with filters, modals with actions, live search).
- Use Flux components whenever an equivalent exists (button, input, select, textarea, checkbox, switch, radio, dropdown, modal, table, tabs, badge, avatar, tooltip, toast/heading/separator, navbar/sidebar primitives).
- Follow Flux conventions and props before reaching for a custom component — check Flux's component set first.
- Use Tailwind utility classes for layout/spacing/custom one-off styling; never fight or override Flux's internal styling.
- Alpine.js is only for small, purely-client interactions Livewire doesn't need to know about (e.g. a local disclosure toggle). Anything touching server data goes through Livewire.
- Keep components small and reusable — one Livewire component per meaningful unit of interactivity, not one giant component per page.

**Component organization:**
```
app/Livewire/
  Auth/                 (Login, Register, ForgotPassword, ResetPassword)
  Dashboard/            (Dashboard)
  Projects/             (ProjectList, ProjectForm, ProjectDetail, ProjectSettings)
  Projects/Members/     (MembersTable, InviteMemberForm, ChangeRoleModal)
  Documents/            (DocumentList, DocumentUploadForm, DocumentPreview, DocumentShareModal)
  Comments/             (CommentThread, CommentForm)
  Search/                (GlobalSearch)
  Notifications/        (NotificationBell, NotificationList)
  Profile/               (ProfileForm, AvatarForm, SecurityForm)
  Admin/                 (UserTable, ProjectTable, DocumentTable, AuditLogTable, SystemSettingsForm)

resources/views/components/   (shared Blade components: status-badge, role-badge, avatar, empty-state, page-header)
```

## 3. Information Architecture / Sitemap

```
Public
├── /login
├── /register
├── /forgot-password
└── /share/{token}                 → read-only public document view, no login required

User (authenticated)
├── /dashboard
├── /projects                      → list, scoped to membership
├── /projects/create
├── /projects/{project}            → tabs: Documents | Members | Activity | Settings
├── /projects/{project}/documents/{document}
├── /search?q=
├── /notifications
└── /profile
    └── /profile/security          → change password

Admin
├── /admin
├── /admin/users
├── /admin/projects
├── /admin/documents
├── /admin/audit-logs
└── /admin/settings                → avatar URL allow-list, max upload size, etc.
```

## 4. Layout Shells

- **Authenticated shell:** fixed left sidebar (Dashboard, Projects, Search, Notifications, Profile at the bottom) + topbar (global search input, notification bell with unread badge, avatar/profile dropdown).
- **Admin shell:** same shell shape, different nav items, plus a small persistent visual marker (e.g. a thin colored top bar or an "Admin" label in the sidebar header) so it's never ambiguous that you're in the admin area.
- **Public shell:** centered card on a plain background, no sidebar/topbar — used for login/register/forgot-password and the public share view.

## 5. Page Specs

### Login / Register / Forgot Password
- Centered Flux card, logo/wordmark, form (Flux input + button), link to the other auth pages.

### Dashboard
- Greeting header, a few summary cards (my projects, pending invites, recent activity, unread notifications), a "recent projects" list.

### Projects List (`/projects`)
- Flux table or card grid, columns: name, role badge, status badge (active/archived), member count, last activity. Filter by status. Empty state with "Create project" CTA.

### Project Detail (`/projects/{project}`)
- Header: project name, status badge, owner avatar, primary actions (Edit, Archive, Delete — visibility per role).
- Tabs: **Documents** (default), **Members**, **Activity**, **Settings** (Settings tab only visible to owner/admin).

### Documents tab
- Table/grid of documents: name, visibility badge (project/private), owner, size, updated date. Upload button (hidden/disabled if project archived or role = viewer). Empty state.

### Document Detail / Preview (`/projects/{project}/documents/{document}`)
- Preview pane (type-aware: image/PDF inline, other types show metadata + download), metadata sidebar (owner, size, visibility, shared-with list), Share button (opens `DocumentShareModal`), Comment thread below.

### Members tab
- Table: user, role badge, status badge (active/pending), invited date. Invite form (owner/admin only). Row actions: change role, remove (owner/admin only).

### Activity tab
- Reverse-chronological feed of project `Activity` entries.

### Settings tab
- Project name/description edit form, Archive toggle, Danger zone (Delete project).

### Search (`/search?q=`)
- Grouped results in three sections: Projects, Documents, Users — each with its own empty state if no matches.

### Notifications (`/notifications`)
- List of notifications, unread visually distinct, "mark all as read" action. Bell in topbar polls for unread count.

### Profile / Security
- Profile form (name, avatar upload or avatar-from-URL field), Security sub-page (change password form).

### Admin Dashboard
- High-level counts (users, projects, documents, suspended users) + shortcuts to each admin sub-page.

### Admin Users / Projects / Documents / Audit Logs
- Full-width Flux tables with filters (role/status for users; status for projects) and row actions appropriate to each (suspend/activate, change system role, delete, view detail).

### Admin Settings
- Form for `system_settings`: avatar URL allowed domains (multi-value input), max upload size (MB).

### Public Share View (`/share/{token}`)
- Minimal shell: document name, preview/download only, no comments, no navigation into the rest of the project. A clear "invalid or expired link" state when the token is invalid/expired/revoked.

## 6. Component → Flux Mapping

| UI need | Flux component |
|---|---|
| Buttons (primary/secondary/danger/ghost) | `flux:button` |
| Text/email/password input | `flux:input` |
| Select / multi-select | `flux:select` |
| Textarea (comments, descriptions) | `flux:textarea` |
| Toggle (archive, switch settings) | `flux:switch` |
| Dropdown menu (row actions, profile menu) | `flux:dropdown` + `flux:menu` |
| Modal (invite member, share document, confirm delete) | `flux:modal` |
| Data tables (members, documents, admin lists) | `flux:table` |
| Tabs (project detail) | `flux:tabs` |
| Status/role indicators | `flux:badge` |
| Avatars | custom Blade component wrapping image or initials fallback |
| Notification toast (success/error after an action) | `flux:toast` |
| Section headers | `flux:heading` + `flux:subheading` |
| Dividers | `flux:separator` |

Build a custom Blade/Alpine component only when no Flux equivalent exists (e.g. the document preview pane, the markdown-lite comment renderer output, the itinerary-style activity feed).

## 7. Status & Role Badge Colors

| Value | Suggested color |
|---|---|
| Project status: active | emerald |
| Project status: archived | zinc/gray |
| Member status: active | emerald |
| Member status: pending | amber |
| User status: active | emerald |
| User status: suspended | red |
| Document visibility: project | blue |
| Document visibility: private | violet |
| Role: owner | indigo |
| Role: editor | blue |
| Role: viewer | zinc |
| System role: admin | red/rose (used sparingly, only in admin contexts) |

## 8. States

- **Loading:** Livewire's native loading states (`wire:loading`) on buttons/tables; skeleton rows for tables where practical.
- **Empty:** icon + one-line explanation + primary CTA where relevant (e.g. "No documents yet — Upload your first document").
- **403 / not authorized:** dedicated page, calm tone, link back to Dashboard.
- **404:** dedicated page for missing project/document.
- **Invalid share-link:** dedicated state on the public share page (see §5).

## 9. Accessibility

- Rely on Flux's built-in accessibility (keyboard navigation, ARIA roles, focus management) wherever Flux components are used.
- Icon-only buttons always get an `aria-label`.
- Maintain visible focus states; don't remove Tailwind's default focus rings without replacing them.
