# Phase D — Implementation Plan

## Status: ✅ Complete (feature/phase-d)

## Scope
Phase D = "Agile & structural" dari roadmap. Fokus: **sprints, components, comment threading, automation rules, strict workflow**.

## Analysis — What exists vs. what's missing

### ✅ Foundation sudah ada (committed)
| Fitur | Files | Status |
|-------|-------|--------|
| Sprints | migration, `Sprint` model, `SprintController`, routes, FormRequests | API-only (JSON), no Blade view |
| Components | migration, `Component` model, `ComponentController`, routes, FormRequests | No management view, issue form wiring partial |
| Comment threading | migration `add_parent_id_to_comments`, `Comment.parent()/replies()` relasi | Model OK, no reply UI, controller already accepts parent_id |

### ❌ Missing
| Fitur | What to build |
|-------|--------------|
| Sprint UI | backlog view, drag antar sprint |
| Component UI | project components CRUD view, issue form checkboxes |
| Threaded comment UI | Reply button per komentar, nested rendering |
| Automation rules | migration + model + listener |
| Strict workflow | role-aware transitions on StatusTransition |
| Tests | coverage untuk semua di atas |

## Task Breakdown (T1–T6)

### T1: Sprint backlog view + route ✅
- `resources/views/issues/backlog.blade.php` — list issues per project, grouped by sprint
- `IssueController.php` — method `backlog(Project $project)` + `updateSprint()`
- `routes/web.php` — route `projects.backlog` + `issues.sprint`
- `tests/Feature/PhaseDTest.php` — auth renders, non-member 403, sprint assignment
- **Skip**: drag UI (SortableJS) — manual via issue edit form. ponytail: simple list.

### T2: Component management view ✅
- `resources/views/partials/components.blade.php` — card on project show, list + inline-add
- `ProjectController.php` — eager load `components.lead` di `show()`
- `routes/web.php` — route `projects.components.store` (already exists in ComponentController)
- Lang keys di `ui.php`/`messages.php` (en+id)
- `tests/Feature/PhaseDTest.php` — component renders di project show, create

### T3: Threaded comment reply UI ✅
- `resources/views/issues/partials/comment.blade.php` — recursive partial, reply button per comment
- `resources/views/issues/show.blade.php` — replace loop dengan partial, render top-level + replies
- `IssueController.php` show() — eager load `comments.replies.user`
- Lang keys: `reply`, `post_reply` (en+id)
- `tests/Feature/PhaseDTest.php` — reply creates threaded comment

### T4: Automation rules (minimal) ✅
- `database/migrations/2026_09_02_030000_create_automation_rules_table.php`
- `app/Models/AutomationRule.php` + `AutomationRuleLog.php`
- `app/Observers/IssueObserver.php` — fire rules on status change
- `app/Models/Issue.php` — `fireAutomationRules()` method
- **Skip**: admin UI. ponytail: only "assign" action.
- `tests/Feature/PhaseDTest.php` — rule fires on status change, assigns, logs

### T5: Strict workflow (role-aware) ✅
- `database/migrations/2026_09_02_030100_add_strict_workflow_to_status_transitions.php`
- `app/Models/StatusTransition.php` — add `required_role` to fillable
- `app/Models/Issue.php` — update `canTransitionTo()` to accept User, check role
- `IssueController.php` — pass `request->user()` ke `canTransitionTo()` di update + changeStatus
- `tests/Feature/PhaseDTest.php` — viewer blocked, lead allowed

### T6: Tests consolidation ✅
- `tests/Feature/PhaseDTest.php` — 8 tests, all passing
- Full suite green

## Verify ✅
- `php artisan test --filter=PhaseDTest` → 8/8 passed, 18 assertions
- Full suite → green (no regressions)
