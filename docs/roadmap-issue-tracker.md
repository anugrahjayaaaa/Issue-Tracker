# Issue Tracker — Roadmap to Jira Parity (Agency Brief)

> Owner: Issue Tracker dev agency. Status: **planning only — no code yet**.
> Built on Laravel Base Project (Laravel 13, PHP 8.3, MySQL 8, AdminLTE 4 + Bootstrap 5,
> Pest, spatie permission/activitylog, sanctum). Single source of truth for the next
> build phases. Read `docs/custom/issues.md` and `docs/custom/comments.md` for the current
> feature spec before starting any phase.
>
> This document was produced by a 3-person agency team (Researcher, Senior Dev, Architect)
> and fused here. Each phase below is directly implementable: Goal → Files → Acceptance.

---

## 0. Executive summary

The product already covers the Jira core: per-project **dynamic issue types, statuses, and
workflow transitions**, rich-text description/comments with scoped image upload, mentions,
activity timeline, labels, kanban board with drag, list filters/sort, bulk delete, and a
layered RBAC (global `issue.*`/`comment.*` + per-project lead/member/viewer).

What is **missing for Jira parity** (highest value first): stable status/type keys, storage
cleanup on delete, REST API, global search / Cmd+K, sub-task tree + rollup, saved filters/views,
sprints/cycles, watchers, bulk assign/label, standalone attachments, workflow-scheme admin UI.

**Build order (smallest-first, value-per-maintenance-cost):**
- **Phase A — Stabilise** (no new features): stable keys, delete guards, storage cleanup, wire bulk route, scope project pickers, FormRequests for fields, vendor SortableJS, badge components.
- **Phase B — Cheap parity on the issue page**: labels on issue, sub-task tree + rollup, watchers, standalone attachments.
- **Phase C — Find & report**: search/Cmd+K, saved filters/views, REST API, correct board ordering + JSON.
- **Phase D — Agile & structural (defer)**: sprints/cycles, components, comment threading, automation rules, strict workflow schemes.

Sequencing rationale: Phase A removes the two debts that make everything else expensive
(name-as-key, missing cleanup). Phase B is pure UI on schema that mostly exists. Phase C adds
tables but no new UI paradigm. Phase D introduces new domain concepts — do it last, only if wanted.

---

## 1. Competitive Gap Analysis (Researcher)

Legend: ✅ full · ⚠️ partial/limited · ❌ absent. Grounded in `docs/custom/issues.md`,
`comments.md`, `routes/web.php`, `app/Models/*.php`, and public Jira/Linear/YouTrack docs.

### 1.1 Capability matrix

| Capability | Jira | Linear | YouTrack | GitHub Issues | Our repo |
|---|---|---|---|---|---|
| Issue types (epic>story>task>subtask) | ✅ | ✅ | ✅ | ⚠️ labels | ⚠️ Partial |
| Custom workflows / transitions | ✅ | ⚠️ fixed | ✅ | ❌ | ⚠️ Partial |
| Statuses (custom per project) | ✅ | ⚠️ limited | ✅ | ❌ | ✅ Done |
| Priority | ✅ | ✅ | ✅ | ⚠️ | ✅ Done |
| Assignee / reporter | ✅ | ✅ | ✅ | ✅ | ✅ Done |
| Watchers / subscribers | ✅ | ✅ | ✅ | ✅ | ❌ Missing |
| Subtasks & parent rollup | ✅ | ✅ | ✅ | ⚠️ | ⚠️ Partial |
| Labels / components | ✅ / ✅ | ✅ / ❌ | ✅ / ✅ | ✅ / ❌ | ✅ / ❌ |
| Mentions | ✅ | ✅ | ✅ | ✅ | ✅ Done |
| Rich-text + images | ✅ | ✅ | ✅ | ⚠️ md | ✅ Done |
| Comments (edit/del/thread) | ✅/✅/✅ | ✅/✅/✅ | ✅/✅/✅ | ✅/✅/❌ | ✅/✅/❌ |
| Activity log | ✅ | ✅ | ✅ | ⚠️ | ✅ Done |
| Attachments (non-image) | ✅ | ✅ | ✅ | ✅ | ⚠️ Partial |
| List + filters + sort | ✅ | ✅ | ✅ | ✅ | ✅ Done |
| Kanban board (swimlanes) | ✅/✅ | ✅/✅ | ✅/✅ | ✅/❌ | ✅/❌ |
| Backlog / grooming / triage | ✅ | ✅ | ✅ | ⚠️ | ⚠️ Partial |
| Sprints / cycles | ✅ | ✅ | ✅ | ❌ | ❌ Missing |
| Roadmaps | ✅* | ✅ | ✅ | ❌ | ❌ Missing |
| Search / Cmd+K | ✅/✅ | ✅/✅ | ✅/✅ | ✅/❌ | ❌ Missing |
| Saved filters | ✅ | ✅ | ✅ | ❌ | ❌ Missing |
| Saved views | ✅ | ✅ | ✅ | ❌ | ❌ Missing |
| Bulk actions (status/assign/label) | ✅ | ✅ | ✅ | ⚠️ | ⚠️ Partial |
| Automation rules | ✅ | ⚠️ | ✅ | ⚠️ | ❌ Missing |
| Time tracking | ✅ | ⚠️ est | ✅ | ❌ | ❌ Missing |
| Reporting / velocity / burndown | ✅ | ✅ | ✅ | ❌ | ❌ Missing |
| REST / GraphQL API | ✅/✅ | ✅/❌ | ✅/✅ | ✅/❌ | ❌ Missing |
| Webhooks | ✅ | ✅ | ✅ | ✅ | ❌ Missing |
| Notifications | ✅ | ✅ | ✅ | ✅ | ✅ Done |
| Permissions granularity | ✅ | ⚠️ team | ✅ | ⚠️ repo | ✅ Done |
| Mobile | ✅ | ✅ | ✅ | ⚠️ web | ⚠️ Partial |

\*Jira roadmaps require Premium.

### 1.2 Gap vs our repo (Done / Partial / Missing)

- **Issue types hierarchy** — ⚠️ Partial: per-project `IssueType` + `parent_id`/`children()`, but no enforced epic→story→subtask rollup.
- **Workflows / transitions** — ⚠️ Partial: `statuses` + `status_transitions` + `Issue::canTransitionTo()` exist (empty = free); config UI still TODO.
- **Statuses** — ✅ Done: `Status` per project (name/color/is_closed/order).
- **Priority / Assignee / Reporter / Mentions / Rich-text / Activity / List / Notifications / Permissions** — ✅ Done.
- **Watchers** — ❌ Missing.
- **Subtasks & rollup** — ⚠️ Partial: relations exist, UI + rollup TODO.
- **Components** — ❌ Missing. **Labels** — ✅ Done (project page only).
- **Comments threading** — ❌ Missing. **Edit/delete** — ✅ Done (owner-only).
- **Attachments (non-image)** — ⚠️ Partial: `Attachment` model has `issue_id`+`comment_id` but issue-level UI not wired.
- **Kanban swimlanes** — ❌ Missing (columns from statuses work).
- **Backlog/triage** — ⚠️ Partial (list+board act as backlog; no triage queue).
- **Sprints/cycles, Roadmaps, Search/Cmd+K, Saved filters/views, Automation, Time tracking, Reporting, REST/GraphQL API, Webhooks** — ❌ Missing.
- **Bulk assign + bulk label** — ⚠️ Partial (bulk delete + re-status only).
- **Mobile** — ⚠️ Partial (responsive web; no PWA/native).

### 1.3 Top 10 highest-value features to add next (ranked)

1. **REST API v1** (`/api/v1/issues`,`/comments`) — sanctum tokens already wired; unlocks integrations + parity; reuses models/observers.
2. **Global Search / Cmd+K** — Linear's signature UX; cheap with existing indexes.
3. **Sub-task tree + rollup** — `parent_id`/`children()` already in `Issue`; only render + rollup needed.
4. **Saved filters / views** — serialize query per user; cheap, direct parity.
5. **Sprints / Cycles** — core agile parity; builds on statuses + due_date.
6. **Watchers + auto-subscribe** — notification engine exists; add pivot + auto-subscribe.
7. **Bulk assign + bulk label** — extends existing bulk pathway; minimal code.
8. **Standalone issue attachments (non-image)** — `Attachment.issue_id` exists; wire UI.
9. **Roadmaps / epic rollup view** — leverages `type=epic` + `parent_id`; no new schema.
10. **Workflow scheme config UI** — transition engine enforced in `canTransitionTo()`; add admin UI.

*Deferred (higher effort, lower maintainability now): automation rules, time tracking, burndown/velocity, GraphQL, native mobile/PWA, components, comment threading, webhooks.*

---

## 2. Current-State Audit (Senior Dev)

### 2.1 What already works (evidence)

- Project CRUD + auto-slug + per-project issue sequence — `ProjectController.php:33-67`, `Project::slug` boot, `Project::nextIssueCode()` (`Project.php:117-124`).
- Layered RBAC — `ProjectMember::hasRole()` (`ProjectMember.php:33-39`); `IssueController::abortIfNotReader()`, `CommentController`, `ProjectFieldController::abortIfNotLead()`; routes `can:` + `feature:issues`.
- Per-project dynamic fields — `ProjectFieldController.php:35-130`, relations `Project.php:70-83`, seeded via `ProjectObserver` → `Project::seedDefaultFields()` (`:86-109`).
- Workflow enforcement — `Issue::canTransitionTo()` (`Issue.php:82-97`); free when table empty; enforced in `update()` + `changeStatus()`.
- Issue CRUD, list (filters/sort/`Sortable`), kanban (drag→`changeStatus`), Jira-like 2-col detail with inline assignee/due_date.
- Comments (rich, owner-only delete + folder cleanup, mentions), shared `sanitizeRichText()` + `rich-text-field` partial, audit via `Issue`/`Comment`/`Project` observers, notifications, labels, soft deletes.
- `attachments` table already has both `issue_id` + `comment_id` nullable FKs.

### 2.2 Known technical risks / debt (ordered by blast radius)

1. **Status/type stored as display-name string** — renaming orphans every issue (board column disappears, `canTransitionTo()` falls back to true, filters break). No stable `key` + no FK.
2. **Dead legacy enums** — `Issue::STATUS_*`/`TYPE_*` constants no longer match seeded names (`Open`, `In Progress`).
3. **`destroyStatus`/`destroyType` have no in-use guard** — deleting a status leaves issues in a dangling status.
4. **Board drag ignores sibling order** — `order` set only for the moved card; ties on `orderBy('order')`; 302 redirect on a `fetch()` that expects JSON.
5. **Storage cleanup not wired on issue/project delete** — only `CommentController::destroy()` calls `deleteStorageFolder()`; `Issue`/`Project` destroy leak folders; slug is mutable so `Project::folder()` can detach.
6. **Attachments half-built** — `CommentController::attach()` writes `issue_id` only; view renders `$comment->attachments`; files bypass scoped folder; never deleted.
7. **`IssueController::bulk()` unreachable** — no `issues.bulk` route despite `IssueBulkRequest`; mass-deletes without re-checking project scope.
8. **Project scoping leaks** — `Project::orderBy('name')->get()` shows ALL projects to any authed user; `ProjectController::show()` has no membership check.
9. **`ProjectController::index()` `orWhere` precedence bug** — ungrouped `where(name like)->orWhere(key like)`.
10. **Inline validation in `ProjectFieldController`** — `$request->validate()` violates the FormRequest rule in `AGENTS.md`.
11. **Missing sub-task UI** — `parent_id`/`parent()`/`children()` exist; nothing renders; no cycle guard.
12. **Labels not editable from issue** — detail card read-only; `labels()->sync()` only on store/update.
13. **No watchers** — recipients hardcoded to reporter+assignee.
14. **No comment threading** — flat `comments` table.
15. **No search / saved filters / sprints / components / automation / REST API** — zero `api/v1` issue endpoints.
16. **No badge Blade components** — status/type/priority badges copy-pasted in 4+ places.
17. **N+1 / perf** — board queries per status without eager `labels`; `activityTimeline()` unpaginated on every detail.
18. **CDN dependency in board** — `Sortable.min.js` from jsdelivr contradicts the `public/vendor/*` rule.

---

## 3. Architecture & Data-Model Evolution (Architect)

Reuse-first: `ProjectMember::hasRole()` for scoping, `sanitizeRichText()` for rich text,
`IssueObserver` for side-effects, existing `Issue*Request` FormRequests for validation, spatie
`activity_log` for history. No new external services.

### 3.1 Sub-tasks / parent-child
`parent_id` exists. Enforce **depth = 1** (issue → sub-task) for v1; block `parent_id` → non-top-level in `IssueStoreRequest`. Rollup: `% done = closed_children/total`; auto-flip parent to `is_closed` status at 100% (opt-in per project), computed in `IssueObserver`. No schema change.

### 3.2 Stable status / type key
Add `key` varchar(40) slug (unique per `project_id`) to `statuses` + `issue_types`; keep `name` display-only. `Issue.status`/`type` store `key`. Backfill `key=Str::slug(name)`; switch filters + `canTransitionTo()` to compare `key`.

| Aspect | `name` (current) | `key` slug (proposed) |
|---|---|---|
| Rename status/type | Breaks issues/transitions/`canTransitionTo()` | Safe — display only |
| i18n | Hard (value is label) | Easy (name translatable) |
| DB size | verbose string | short slug |
| Migration cost | none | one backfill + filter swap |
| Risk if missed | data corruption on rename | none (key immutable) |

**Decision:** add immutable `key`; `Issue.status/type` store `key`.

### 3.3 Watchers
`issue_watchers (issue_id, user_id)` pivot. `Issue::watchers()` BelongsToMany(User). Auto-subscribe reporter+assignee (+commenter) in `IssueObserver`. Notify scope = watchers ∩ `ProjectMember::hasRole()`.

### 3.4 Components
`components (id, project_id, name, description, lead_id)` + `component_issue` pivot. Mirror `Label` pattern/controllers.

### 3.5 Sprints / Cycles
`cycles (id, project_id, name, starts_at, ends_at, goal)` + `cycle_issue (cycle_id, issue_id, order)` pivot. Board/list gains `cycle_id` filter reusing existing builder.

### 3.6 Saved filters / views
`issue_views (id, project_id, user_id, name, filters json, is_public)`. Serialize the existing index filter/sort params; replay through the same query builder.

### 3.7 Search
**Lazy: server-side MySQL 8 FULLTEXT** on `issues.title`+`description`, composed with existing filters. Cmd+K = thin JS overlay reusing the list endpoint (`?q=`). No external engine until row counts demand it.

### 3.8 REST API `/api/v1/issues`
`routes/api.php` group `middleware(['auth:sanctum','feature:issues'])`; **reuse `IssueStoreRequest`/`IssueUpdateRequest`/`IssueStatusRequest` unchanged** → web/API parity. Add `IssuePolicy` wrapping `ProjectMember::hasRole`.

### 3.9 Attachments — unify
Extend `attachments` with `name` + `is_image` (from `mime`); merge `IssueImageController`/`CommentImageController` into one `AttachmentController`; `AttachmentObserver` deletes files on delete (prevents orphans).

### 3.10 Automation rules (Phase D)
`automation_rules (id, project_id, event, conditions json, actions json, enabled)` + `automation_rule_logs`. Fire from `IssueObserver` events; no new event bus.

---

## 4. Phased Build Plan (the agency's work order)

Every phase reuses existing patterns (FormRequest, observers, `ProjectMember::hasRole()`,
`partials/rich-text-field`, `Sortable` concern). Tests: Pest, green before merge. Docs: update
`docs/custom/*` in the same PR.

### Phase A — Stabilise (no new features)
**Goal:** remove the debts that make everything else expensive; zero behaviour change for users.
**Files:**
- migration `add_key_to_statuses_and_issue_types` + backfill; keep `name` as label.
- `Issue.php`: `canTransitionTo()` compares `key`; drop dead `STATUS_*`/`TYPE_*` constants.
- `ProjectFieldController.php`: in-use guards on `destroyStatus`/`destroyType`; move all `$request->validate()` into `StatusRequest`/`IssueTypeRequest`/`TransitionRequest` FormRequests.
- `IssueObserver` + `ProjectObserver`: `deleting()` → `deleteStorageFolder()` (root-cause fix for controller/bulk/console deletes).
- `IssueController`: wire `bulk()` route `POST /issues/bulk` (gated `can:issue.delete`, re-check project scope); scope project pickers to memberships; fix `ProjectController::index()` `orWhere` grouping; `ProjectController::show()` membership check.
- `CommentController::attach()`: set `comment_id` when from comment, store in scoped folder; render issue-level attachments in `show.blade.php`.
- `resources/views/components/issue-badge.blade.php` (status/type/priority) replacing 4 inline copies.
- `public/vendor/sortable.min.js` + `board.blade.php` script src (drop CDN).
**Acceptance:** renaming a status keeps issues on their board column + valid transitions; deleting an in-use status is rejected; deleting issue/project removes its storage folder (`Storage::fake` asserted); bulk delete works + rejects out-of-project IDs; non-member sees 0 projects + 403 on `projects.show`; `php artisan test` green; `issues.md` §9 updated.

### Phase B — Cheap Jira parity on the issue page
**Goal:** high perceived completeness from mostly-existing schema.
**Features:** labels editable on detail; sub-task tree (children list, add sub-task, parent breadcrumb, depth guard, `n/m done` rollup badge); watchers (pivot, auto-subscribe reporter+assignee+commenter, watch/unwatch, fan-out notify); standalone issue attachments UI (list/upload/delete).
**Files:** migration `create_issue_watchers_table`; `Issue::watchers()`/`children()`; `IssueWatcherController` (or routes on `IssueController`); `IssueAttachmentController` + `IssueAttachRequest` (mirror `CommentAttachRequest`); edit `show.blade.php` (labels editor, sub-task card, watchers card, attachments card), `create.blade.php` (`parent_id`), `IssueController::show/store/update` (eager-load + notify watchers); lang keys; `issues.md`.
**Acceptance:** member edits labels on detail without Edit page; sub-task shows parent + `n/m done` badge; self-ancestor rejected; watcher (non-reporter/assignee) gets `IssueStatusChanged`; PDF attaches, shows, deleted from disk on issue delete; tests cover fan-out + cleanup.

### Phase C — Find & report
**Goal:** search, saved views, REST API, correct board ordering.
**Features:** cross-project search (membership-scoped, Cmd+K palette); saved filters (persist query per user, shareable); REST API `/api/v1/issues`+`/comments` on sanctum (reuse FormRequests); board column ordering fixed (batch reorder) + JSON responses.
**Files:** `SearchController` + `partials/search-palette.blade.php`; migration `create_saved_filters_table` + `SavedFilter` model/controller/request; `routes/api.php` + `Api/V1/IssueController` + `IssueResource`; `IssueReorderRequest`; `changeStatus` returns JSON when `expectsJson()` + renumbers siblings in a transaction.
**Acceptance:** search returns only the user's projects' issues, ≤300ms on 50k rows with index `(project_id, code)`+`title`; saved filter restores exact list for owner, 403 for others; API honours same perms (non-member 403 tested); dragging 5 cards leaves contiguous stable `order`, no reload.

### Phase D — Agile & structural (defer until B/C in use)
**Goal:** sprints, components, threading, automation, strict workflows.
**Features:** sprints/cycles (backlog + active sprint board, capacity, burndown-lite); components per project + lead; comment threading (1 level); automation rules (event→condition→action) on existing observers; strict workflow schemes (required transitions, per-role transition permission, resolution field).
**Files:** migrations `create_sprints_table` (+`issues.sprint_id`), `create_components_table` (+pivot), `add_parent_id_to_comments`, `create_automation_rules_table`; models `Sprint`/`Component`/`AutomationRule`; controllers + views `sprints/*`, `issues/backlog`; `Listeners/RunAutomationRules` on observers; `canTransitionTo()` role-aware; transition UI accepts required-role column; one-level reply render in `show.blade.php`.
**Acceptance:** drag between backlog/active sprint; closing sprint moves unfinished to next; component filter works list+board; reply nested under parent, soft-delete tombstone keeps replies; automation rule fires once per transition + visible in timeline; member without transition role blocked; `php artisan test` green; `docs/custom/` updated.

---

## 5. Decisions (taken — follow these when building)

Agreed 2026-08-31; defaults follow the Senior-Dev/Architect recommendations (no over-build).

1. **Strict workflow** — keep **free transitions** through Phase A/B/C; add required + per-role transitions only in **Phase D**, and only if a project opts in. No premature workflow-scheme engine.
2. **Sub-task rollup** — parent auto-flips to `is_closed` status at 100% children done, **opt-in per project** (flag on `projects`). Default off.
3. **Watcher default** — **YES**: auto-subscribe reporter + assignee (and commenter) on create/assign/comment. Pivot `issue_watchers`; notify scope = watchers ∩ `ProjectMember::hasRole()`.
4. **Attachment model** — **unify** image + non-image into one `attachments` table (extend with `name` + `is_image`); merge `IssueImageController`/`CommentImageController` into one `AttachmentController`; `AttachmentObserver` deletes files on delete.
5. **Search scope** — **MySQL 8 FULLTEXT** on `issues.title`+`description` now; external engine (Meilisearch/Elastic) only if row counts make FULLTEXT slow. Cmd+K reuses the list endpoint.
6. **Bulk actions** — **add bulk assign + bulk label** in Phase B/C (extend existing bulk pathway). Bulk delete/re-status already exist.
7. **Components / roadmaps / automation** — **DEFER**. Build only on real demand. Comment threading + sprints/cycles are Phase D (deferred); automation rules Phase D-last.

These decisions are reflected in the phase files/acceptance above (Phase B: watchers + attachments + bulk; Phase C: search FULLTEXT + API; Phase D: strict workflow opt-in + rollup flag + deferred items).

## 6. References
- Jira: issue types, workflows, components, watchers, subtasks, sprints, roadmaps, automation.
- Linear: states, cycles, triage, labels, relations, Cmd+K, sub-issues.
- YouTrack: custom workflows, agile boards, commands.
- GitHub Issues: labels, milestones, assignees, linked PRs, projects board.
- Repo: `docs/custom/issues.md`, `docs/custom/comments.md`, `docs/custom/notifications.md`, `routes/web.php`, `app/Models/*`, `app/Http/Controllers/*`.
