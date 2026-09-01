# Projects + Project Membership

> Custom feature for Issue Tracker. Built on Laravel Base Project.

## Goal
Let an admin/PM create multiple projects (e.g. `HEL`, `INFRA`) and assign
users to each with a **dynamic project-level role**. A user can belong to many
projects with different roles. This is the "PM can assign users" requirement.

## Scope
- In scope: Project CRUD, member assignment with role (lead/member/viewer),
  remove member, change member role.
- Out of scope: global SSO, cross-org sharing, archived-project workflow.

## Models / Tables (new)
| Table | Purpose | Notes |
|-------|---------|-------|
| `projects` | id, key, name, description, owner_id, timestamps, SoftDeletes | migration `create_projects_table` |
| `project_members` | project_id, user_id, role(enum lead/member/viewer), timestamps | migration `create_project_members_table`; unique(project_id,user_id) |

## Routes (auth group, `feature:issues`)
| Method | URI | Controller@action | Gate |
|--------|-----|-------------------|------|
| GET | `/projects` | `ProjectController@index` | `can:project.manage` |
| GET | `/projects/create` | `ProjectController@create` | `can:project.manage` |
| POST | `/projects` | `ProjectController@store` | `can:project.manage` |
| GET | `/projects/{project}` | `ProjectController@show` | project-scope (viewer+) |
| GET | `/projects/{project}/edit` | `ProjectController@edit` | `can:project.manage` |
| PUT | `/projects/{project}` | `ProjectController@update` | `can:project.manage` |
| DELETE | `/projects/{project}` | `ProjectController@destroy` | `can:project.manage` |
| POST | `/projects/{project}/members` | `ProjectMemberController@store` | `can:project.manage` (or lead) |
| PUT | `/projects/{project}/members/{user}` | `ProjectMemberController@update` | `can:project.manage` (or lead) |
| DELETE | `/projects/{project}/members/{user}` | `ProjectMemberController@destroy` | `can:project.manage` (or lead) |

## How it works
- Project model: `owner()` → User; `members()` → `project_members` (with role);
  `users()` → User via members.
- A `ProjectMember` helper (or Policy) centralizes the scope gate:
  `ProjectMember::hasRole(user, project, ['lead','member'])`.
  Reused by Issue/Comment/SavedFilter controllers via the shared
  `AuthorizesProject` trait so the check lives in ONE place.
- Audit: Project + ProjectMember mutations logged via activitylog observer
  (reuse base `app/Observers` pattern).

## UI / API surface
- Web: `resources/views/projects/*` (AdminLTE), member manager in show/edit.
- API: `/api/v1/projects` (Phase 2 — not in MVP).

## i18n
New keys: `ui.projects.*`, `ui.project.members.*`, `ui.project.role.lead/
member/viewer`. Add to `lang/{en,id}/ui.php`; reseed `LanguageLineSeeder`.

## Tests
`tests/Feature/ProjectTest.php`: create, assign member with role, scope gate
(block non-member), remove member. SQLite `:memory:`.
