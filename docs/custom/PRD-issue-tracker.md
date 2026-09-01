# PRD — Issue Tracker (custom project)

> Built on Laravel Base Project. Single source of truth for the custom
> issue-tracking product. AI agents MUST read this + the per-feature docs
> under `docs/custom/` before coding.

## 1. Purpose

Internal (single-organization) issue tracker / product-management tool, similar
to Jira, but scoped small for internal kantor use first. Teams create
projects, track issues on a Kanban board and a list view, discuss in rich-text
comments with images, and get automatic notifications.

Not public, not multi-tenant (schema-ready only). One Laravel app, one DB.

## 2. Users & roles

- Inherits base RBAC: `super-admin` (bypasses), `admin`, `staff` (spatie).
- Global issue permissions: `project.manage`, `issue.view/create/edit/delete`,
  `comment.create/edit/delete`.
- **Project-level dynamic roles** (the "PM can assign users" requirement):
  - `project_members` links `user_id ↔ project_id` with a `role`:
    - `lead`    — full project control + manage members/roles (the PM role)
    - `member`  — create/edit issues, comment, change status
    - `viewer`  — read-only
  - One user → many projects, each with its own role.
  - Effective access check = global `issue.*` permission
    **AND** a `project_members` row with sufficient role on that project.

## 3. Functional scope — Phase 1 (MVP, build first)

1. **Projects**: CRUD; key (e.g. `HEL`), name, description, owner.
2. **Project membership**: assign users with a project-level role (dynamic).
3. **Issues**: CRUD with type/status/priority/assignee/reporter/due/parent
   (sub-task), rich-text description. Auto issue code `KEY-NN`.
4. **Board (Kanban)**: columns = status; drag card → update status + order.
   AdminLTE ships a Kanban template; reuse it.
5. **List view**: table + filters (project/status/assignee/priority) + bulk
   action (reuse base User-management bulk pattern).
6. **Comments**: rich text + image upload + edit + delete on an issue.
7. **Notifications**: auto "assigned to you" / "mentioned" / "status changed"
   via base DB notification channel (bell already exists).
8. **Docs + tests** for all of the above.

## 4. Authorization model (mandatory)

Every protected action requires **all** of:

1. Authenticated session (`auth`).
2. Global permission gate (`can:{perm}` on the route).
3. Module enabled (`feature:issues` on the route) — except `project.manage`
   holders operating `/features`.
4. **Project scope gate** — for issue/comment/project-member actions, the
   acting user must have a `project_members` row on the target project with a
   role granting the action (`lead`/`member` for write, `viewer`+ for read).
   Implement centrally via the shared `AuthorizesProject` trait
   (`app/Http/Controllers/Concerns/AuthorizesProject.php`), never inline in
   every controller.

See `docs/coding-standard.md` §Authorization — gate on the **route**, not the
controller constructor.

## 5. Non-functional

- Reuse base stack: Laravel 13, AdminLTE 4.9.1, Bootstrap 5.3, Pest (no npm/Vite build — assets from public/vendor/*),
  SQLite for dev (MySQL in prod).
- Thin controllers, Form Requests, audit observer, i18n en/id.
- Feature flag `issues` wraps the whole module (sidebar hides + route 404s
  when off).

## 6. Out of scope — Phase 1

Epic/Sub-task hierarchy UI, sprints/iterations, burndown, saved filters / JQL,
labels, watch, dashboards/reporting, public API v1, automation, external
integrations (GitHub/GitLab). These are Phase 2-3 — see `phases.md`.

## 7. Success metrics

- New issue module added by: route(`can:`+`feature:issues`+project-scope gate)
  + thin controller + Form Request + doc entry. No base hacks.
- Pest green on `:memory:` sqlite.
- Custom docs under `docs/custom/` stay the single source of truth for AI.
