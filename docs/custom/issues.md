# Issues — Feature & Behaviour Spec (Jira / Linear / GitHub-style)

> Custom feature for Issue Tracker, built on Laravel Base Project.
> Replaces the older MVP `issues.md`. Last revised: 2026-08-31.

## 1. Purpose

An **issue** is the unit of work in a project. This spec covers the full
lifecycle and the behaviours users expect from modern trackers (Jira, Linear,
GitHub Issues, YouTrack): create → triage → collaborate → resolve → close,
with rich context, workflow, and auditability.

## 2. Competitive feature matrix

| Capability | Jira | Linear | GitHub Issues | Our target |
|------------|------|--------|---------------|------------|
| Issue types (bug/feature/task/epic) | ✅ | ✅ (type) | ⚠️ labels | ✅ |
| Status / workflow | ✅ custom WF | ✅ states | ✅ open/closed | ✅ 4-status |
| Priority | ✅ | ✅ | ⚠️ labels | ✅ |
| Assignee / Reporter | ✅ | ✅ | ✅ | ✅ |
| Due date | ✅ | ✅ | ✅ milestone | ✅ |
| Labels / tags | ✅ | ✅ | ✅ | ✅ |
| Components / modules | ✅ | ❌ | ❌ | ⏳ Phase 2 |
| Sub-tasks / parent | ✅ | ✅ | ⚠️ | ⏳ (parent_id ready) |
| Watchers / participants | ✅ | ✅ (subscribers) | ✅ | ⏳ Phase 2 |
| @mention in comments | ✅ | ✅ | ✅ | ✅ |
| Rich-text description + images | ✅ | ✅ | ✅ (md) | ✅ TinyMCE |
| Comments (rich, edit/del) | ✅ | ✅ | ✅ | ✅ (see comments.md) |
| Activity timeline | ✅ | ✅ | partial | ✅ |
| Attachments | ✅ | ✅ | ✅ | ⚠️ comment-img only |
| List view + filters + sort | ✅ | ✅ | ✅ | ✅ |
| Kanban board (drag) | ✅ | ✅ | ✅ projects | ✅ |
| Bulk actions | ✅ | ✅ | ⚠️ | ✅ delete/sort |
| Search (global/JQL) | ✅ | ✅ (Cmd+K) | ✅ | ⏳ Phase 2 |
| Saved filters | ✅ | ✅ | ❌ | ⏳ Phase 2 |
| Sprints / Cycles | ✅ | ✅ | ❌ | ⏳ Phase 2 |
| REST/GraphQL API | ✅ | ✅ | ✅ | ⏳ Phase 2 |
| Notifications | ✅ | ✅ | ✅ | ✅ (notifications.md) |

Legend: ✅ done · ⚠️ partial · ⏳ planned · ❌ out of scope.

## 3. Data model (fields)

| Field | Type | Notes |
|-------|------|-------|
| `id` | PK | |
| `project_id` | FK | scoping; every issue belongs to one project |
| `code` | string | `KEY-NN`, auto from project key + per-project sequence |
| `title` | string | required, ≤255 |
| `description` | HTML (sanitized) | TinyMCE rich text; inline images scoped |
| `type` | enum | bug / feature / task / epic |
| `status` | enum | open / in_progress / blocked / done |
| `priority` | enum | low / medium / high / urgent |
| `reporter_id` | FK users | set on create = actor |
| `assignee_id` | FK users nullable | project member; editable by members |
| `parent_id` | FK issues nullable | sub-task ready (UI Phase 2) |
| `due_date` | date nullable | editable by members |
| `order` | int | kanban sort order |
| `SoftDeletes` | | audit retained |

## 4. Views & flows

### 4.1 List view (`/issues?project_id=`)
- Table: code, title, type, status, priority, assignee, due_date.
- Filters: project, status, assignee, priority, label.
- Sortable columns (server-side, reusable `Sortable` concern).
- Bulk: delete, re-status (member/lead gated).
- Progressive reveal: project must be selected first.

### 4.2 Kanban board (`/issues/board`)
- Columns = status enum.
- Cards drag → `changeStatus` (updates status + order).
- Reuses SortableJS; member-gated writes.

### 4.3 Detail (`/issues/{issue}`)
Layout (2-col, Jira-like):
```
Left (8):  Description (rich)
           Comments (rich composer, scrollable, see comments.md)
           Activity timeline (issue + comment events)
Right (4): Details card
           - Project (ro)
           - Type / Status / Priority (ro, change via Edit page)
           - Assignee (inline editable, member-scoped search)
           - Reporter (ro)
           - Due date (inline editable, members)
           - Labels (ro, manage on project page)
```

### 4.4 Create / Edit
- Create: progressive reveal (pick project → form). Assignee searchable.
- Edit: full form (`issues.edit`, gated `issue.edit`).
- Inline meta edits (assignee/due_date) also possible from detail (member-gated,
  `IssueUpdateRequest` is `sometimes` + member-authorized).

## 5. Workflow & state

- New issue → `open`.
- Drag/status change → any status; `issue_status_changed` activity logged.
- Resolve → `done`. Delete → soft delete + `issue_deleted` activity.
- No mandatory transition rules yet (Jira-style WF schemes are Phase 3).

## 6. Collaboration layer

- **Comments**: rich text, mentions, edit/delete, audit — full spec in
  `comments.md`.
- **Mentions**: `@username` → notification; only project members mentionable.
- **Activity timeline**: aggregated issue + child-comment events with causer +
  relative time.
- **Watchers** (planned): auto-subscribe reporter + assignee; notify on change.
- **Attachments** (partial): images inline in description/comments; standalone
  file attachments (Jira-style) planned Phase 2.

## 7. Permissions model

| Action | Gate |
|--------|------|
| View issue | project member (any role) |
| Create | `issue.create` + member |
| Edit (full) | `issue.edit` + member/lead |
| Edit meta (assignee/due_date) | project member (lead/member) |
| Delete | `issue.delete` + member/lead |
| Comment | `comment.*` (see comments.md) |
| Bulk | `issue.delete` + member/lead |

Scoping enforced by `ProjectMember::hasRole(...)`; route-level `can:` middleware
for the broad permission, finer checks in FormRequest/controller.

## 8. Best practices we adopt (popular consensus)

1. **Auto issue keys** — stable `KEY-NN` reference everywhere (Jira/GitHub #).
2. **One editor, everywhere** — description + comments share `rich-text-field`.
3. **Progressive disclosure** — don't show project-scoped data until chosen.
4. **Inline meta edits** — assignee/due_date editable without full edit page.
5. **Activity as truth** — every mutation is logged and visible.
6. **Member-scoped assignment** — only people in the project are assignable.
7. **Soft delete + audit** — never lose history.
8. **Reusable sort/filter/bulk** — list & board share concerns.
9. **Theme-aware UI** — dark/light follows app toggle (editor too).
10. **Quota-scoped uploads** — images per entity, plan-quota enforced.

## 9. Implementation status (this repo)

### Done
- [x] Issue CRUD + auto `KEY-NN` code (`nextIssueCode()`)
- [x] Types/status/priority/assignee/reporter/due_date/parent/order
- [x] Rich-text description (TinyMCE, theme-aware, scoped image upload)
- [x] List view (filters, sortable, bulk delete/sort) + progressive reveal
- [x] Kanban board (drag → changeStatus)
- [x] Detail: inline assignee (searchable) + due_date edit (member-gated)
- [x] `IssueUpdateRequest` `sometimes` + member authorize; redirect to show
- [x] Comments full (rich/edit/del/mention/activity) — `comments.md`
- [x] `IssueObserver` → created/updated/deleted/status in timeline
- [x] **Per-project dynamic issue types & statuses + workflow transitions** (Phase A)
- [x] **Stable `key` slug** on `issue_types`/`statuses`; `Issue.type`/`status` store the key
      (rename a label keeps issues attached — migration backfills existing rows)
- [x] **In-use guards** on status/type delete (`destroyStatus`/`destroyType` abort 409)
- [x] **Storage cleanup** on issue/project delete (observer `deleting()` → `deleteStorageFolder`)
- [x] **Bulk route wired** (`POST /issues/bulk`, scoped to project + membership re-check)
- [x] **Project pickers scoped** to memberships; `ProjectController::show` 403 for non-members
- [x] **Issue attachments** (scoped folder, list + upload on detail page)
- [x] **`issue-badge` component** (status/type) replacing inline copies
- [x] **SortableJS vendored** to `public/vendor/sortable.min.js` (no CDN)
- [x] Tests: `IssueTest` + `CommentTest` + `NotificationTest` + `LabelTest` + `IssueTimelineTest` green

### TODO (phased — see `roadmap-issue-tracker.md`)
- [ ] **Phase B** — labels on issue, sub-task tree + rollup, watchers, standalone attachments UI
- [ ] **Phase C** — search/Cmd+K, saved filters/views, REST API, correct board ordering + JSON
- [ ] **Phase D (deferred)** — sprints/cycles, components, comment threading, automation, strict workflow

## 10. Open decisions (discuss)

1. **Workflow strictness** — free transitions now; add required rules later?
2. **Sub-task rollup** — should parent status derive from children?
3. **Watcher default** — reporter + assignee auto-subscribe (recommend yes).
4. **Attachment model** — comment-img only vs separate `attachments` table
   extended to issues (recommend: extend, unify cleanup).
5. **Search scope** — client filter vs server index (Elastic/DB) at scale.
6. **Bulk actions** — add move-status / assign / label in bulk?

## 11. References
- Jira: issue types, workflows, components, watchers, subtasks, sprints.
- Linear: states, cycles, priorities, labels, relations, Cmd+K.
- GitHub Issues: labels, milestones, assignees, linked PRs, projects board.
- YouTrack: custom workflows, Agile boards, commands.
