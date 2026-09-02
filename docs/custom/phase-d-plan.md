# Phase D — Implementation Plan

## Status: ✅ COMPLETE (feature/phase-d) — D.1 + D.2 + D.3

## Scope
Phase D = "Agile & structural" dari roadmap. Fokus: sprints, components, comment threading, automation rules, strict workflow, epic linkage.

## Analysis
### ✅ Foundation (sudah ada sebelum Phase D)
| Fitur | Files | Status |
|-------|-------|--------|
| Sprints | migration, Sprint model, SprintController (JSON API), routes, FormRequests | API-only, no Blade view |
| Components | migration, Component model, ComponentController, routes, FormRequests | No management view, issue form partial exists |
| Comment threading | migration, Comment model (parent/replies) | Model OK, controller accepts parent_id |

### ❌ Built in Phase D
| Fitur | What |
|-------|-----|
| Sprint UI | backlog view, drag-drop via SortableJS, sprint complete action |
| Component UI | project management card + index list filter |
| Threaded comment UI | nested reply partial, recursive rendering, eager-load replies |
| Automation rules | migration + model + logs + observer + admin CRUD UI |
| Strict workflow | role-aware transitions (required_role column) |
| Sprint state | state (planning/active/completed) + velocity columns |
| Tests | 11 tests in PhaseDTest.php |

## Task Breakdown

### D.1 Core (✅ committed at 904d144)
- T1: Sprint backlog view, route, controller (`backlog(Project $project)`, `updateSprint()`)
- T2: Component management card on project show
- T3: Threaded comment reply UI (`comment.blade.php` partial)
- T4: Automation rules (migration, model, observer fire on status change)
- T5: Strict workflow (`required_role`, `canTransitionTo(User)`, controller updates)

### D.2 Polish (✅ committed at b22ad21, cd73572, 35fe163)
- Drag-drop sprint assignment in backlog (SortableJS, `sortable.min.js` already vendored)
- Component filter di issue list index (`component_id` query param, `whereHas`)
- Full automation action types: assign, change_status, add_label, add_component, notify_watchers

### D.3 Sprint complete (✅ committed at efccf9d)
- Migration `add_state_to_sprints` (state + velocity columns)
- `Sprint::complete()` — moves unfinished issues to backlog
- `SprintController@complete()` + route `projects.sprints.complete`
- Complete button di backlog view

## Skipped (YAGNI — not built, add when needed)
| Item | Reason | Add when |
|------|-------|---------|
| Board WIP limits | JS board refactor needed | product wants WIP on columns |
| Epic linkage UI | issue type model needs epic type | epic feature scoped in |
| Burndown-lite chart | needs chart.js dep (not vendored) | reporting phase |

## Verify ✅
- `php artisan test --filter=PhaseDTest` → 11/11 passed, 29 assertions
- Full suite → 208 tests, 540 assertions, green (no regressions)
