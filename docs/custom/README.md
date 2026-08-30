# Custom Features — Issue Tracker

This folder documents **project-specific** features for the **Issue Tracker**
(an internal Jira-like product-management / issue-tracking app) built on top of
the Laravel Base Project.

It is **separate from `docs/`**, which documents the shared base platform
(auth, RBAC, audit, i18n, feature-flags, etc.).

## Rule

- `docs/` → shared base (do not edit for project work unless the change
  belongs to the base itself).
- `docs/custom/` → features for THIS project (projects, issues, kanban,
  comments, notifications).
- Each custom feature = one file here, copy `TEMPLATE.md` as a base.
- When a custom feature needs base-level changes (new base migration, new
  shared helper), discuss first, then update `docs/` for the base part only.

## Index

| Feature | Doc | Status |
|---------|-----|--------|
| Product / PRD & phases | [PRD-issue-tracker.md](PRD-issue-tracker.md) | active |
| Projects + project-level membership/roles | [projects.md](projects.md) | phase 1 |
| Issues + Kanban board + list view | [issues.md](issues.md) | phase 1 |
| Comments (rich text + image upload) | [comments.md](comments.md) | phase 1 |
| Notifications (assign / mention / status) | [notifications.md](notifications.md) | phase 1 |
| Development phases roadmap (2-3) | [phases.md](phases.md) | reference |

## Shared conventions (read before coding)

1. Authorization = **route-level** `can:{perm}` + `feature:{slug}` middleware.
   Never gate inside a controller constructor (see `docs/coding-standard.md`).
2. Validation lives in **Form Requests** under `app/Http/Requests/<Domain>/`.
3. Controllers stay **thin** — call `validated()` and dispatch to a service/action.
4. Audit: models that must be logged use the activitylog trait + observers
   (pattern already in base `app/Observers`).
5. i18n: page copy in `lang/{en,id}/ui.php` via `ui()`; domain/API messages in
   `messages.php` via `__('messages.*')`; both overridable at runtime via
   `language_lines` (Settings → Translations).

## Permission taxonomy (custom)

Global (spatie, seeded in `DatabaseSeeder`):
- `project.manage` — create/edit/delete any project, manage members & roles
- `issue.view`, `issue.create`, `issue.edit`, `issue.delete`
- `comment.create`, `comment.edit`, `comment.delete`

Project-level (dynamic, answered per project):
- Stored in `project_members.role`: `lead` | `member` | `viewer`
- A user may belong to many projects with **different roles**.
- Effective access = global `issue.*` permission AND a `project_members` row
  with sufficient role for the target project.
- The PM / "assign users to project" capability is a **project-level role**
  (`lead`), not a single global switch — it is dynamic per project.
