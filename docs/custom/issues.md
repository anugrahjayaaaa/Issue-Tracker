# Issues + Kanban + List View

> Custom feature for Issue Tracker. Built on Laravel Base Project.

## Goal
Track work items inside a project: create/edit issues, move them on a Kanban
board, triage in a list view with filters + bulk actions. Auto issue code
`KEY-NN`.

## Scope
- In scope: Issue CRUD, types (bug/feature/task/epic), status
  (open/in_progress/blocked/done), priority (low/med/high/urgent), assignee,
  reporter, due_date, `parent_id` (nullable — sub-task ready, UI in Phase 2),
  rich-text `description` (HTML, sanitized), `order` (kanban sort), project_id.
- Out of scope (Phase 2): epic/sub-task tree UI, sprints, saved filters/JQL.

## Models / Tables (new)
| Table | Purpose | Notes |
|-------|---------|-------|
| `issues` | id, project_id, code, title, description(html), type, status, priority, reporter_id, assignee_id, parent_id(nullable), due_date, `order`(int), timestamps, SoftDeletes | migration `create_issues_table` |

Issue code: generated on create as `{project.key}-{nextSeq}` (per project
counter via `projects.issue_seq` or `max(code)`). Resolve in a service.

## Routes (auth group, `feature:issues`)
| Method | URI | Controller@action | Gate |
|--------|-----|-------------------|------|
| GET | `/issues` | `IssueController@index` | `can:issue.view` + project-scope |
| GET | `/issues/board` | `IssueController@board` | `can:issue.view` + project-scope |
| GET | `/issues/create` | `IssueController@create` | `can:issue.create` |
| POST | `/issues` | `IssueController@store` | `can:issue.create` |
| GET | `/issues/{issue}` | `IssueController@show` | `can:issue.view` + scope |
| GET | `/issues/{issue}/edit` | `IssueController@edit` | `can:issue.edit` + scope |
| PUT | `/issues/{issue}` | `IssueController@update` | `can:issue.edit` + scope |
| DELETE | `/issues/{issue}` | `IssueController@destroy` | `can:issue.delete` + scope |
| POST | `/issues/{issue}/status` | `IssueController@changeStatus` | `can:issue.edit` + scope (drag) |
| POST | `/issues/bulk` | `IssueController@bulk` | `can:issue.delete` + scope |

## How it works
- Kanban: columns = status enum; cards sorted by `order`. Drag → `changeStatus`
  updates `status` + `order` (reuse SortableJS, already AdminLTE-friendly).
- List view: table with filters (project/status/assignee/priority) + bulk
  action. Reuse base `app/Http/Controllers/Concerns/Sortable.php` + bulk
  pattern from `UserController`.
- Project-scope gate: reuse `ProjectMember::hasRole(...)` from projects.md.
- Reporter = auth user on create; assignee nullable.
- Rich text: store HTML; sanitize on save (see comments.md for sanitizer).
- Audit: issue create/update/delete/status via activitylog observer.

## UI / API surface
- Web: `resources/views/issues/*` (board.blade, index.blade, show.blade,
  form partial). Reuse AdminLTE Kanban template.
- API: `/api/v1/issues` (Phase 2).

## i18n
New keys: `ui.issues.*`, `ui.issue.type.*`, `ui.issue.status.*`,
`ui.issue.priority.*`. Add to `lang/{en,id}/ui.php`; reseed.

## Tests
`tests/Feature/IssueTest.php`: create (code generated), status change via drag,
scope gate (non-member blocked), bulk delete. SQLite `:memory:`.
