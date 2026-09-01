# Development Phases — Issue Tracker

Roadmap beyond Phase 1 (MVP). Phase 1 is the only one being built now;
Phase 2-3 are planned so AI/docs stay aligned but are NOT implemented yet.

## Phase 1 — Basic (MVP, current)
- P1.1 Projects CRUD + dynamic project-level membership/roles
- P1.2 Issues CRUD + Kanban board + List view (filters + bulk)
- P1.3 Comments (rich text + image upload + edit/delete)
- P1.4 Notifications (assign / mention / status)
- P1.5 Custom docs (this folder) + Pest tests

## Phase 2 — Agile depth
- Epic / Sub-task hierarchy UI (parent_id already in schema)
- Sprint / Iteration + burndown chart
- Saved filters + lightweight JQL-like query
- Labels / tags per project
- Watch / unwatch an issue
- Issue activity timeline (aggregate audit entries)

## Phase 3 — Scale & polish
- Dashboards + reporting (per project / per user velocity)
- Public REST API `/api/v1/issues`, `/projects`, `/comments` (Sanctum)
- Automation rules (on status change → assign / notify)
- External integrations (GitHub/GitLab link issues to commits/PRs)
- Multi-tenant readiness (add `tenant_id`) — only if product goes public
- Custom workflows (validators/conditions/post-functions)
- Custom fields + field config + screens
- Permission/visibility schemes (issue-level, workflow-step-level)
- Fix versions / affects versions / milestones tracking
- Issue linking (blocks/relates/duplicates)
- Time tracking / work logs
- Service management / SLA (optional)
- Advanced automation UI (rules builder beyond on-status-change)

### Breakdown: flow, architecture, plan, best practice

#### 1. Dashboards + reporting
- **Goal**: show project health and user velocity without building a BI stack.
- **Flow**: controller reads indexed aggregates → JSON/Blade charts.
- **Architecture**: keep it read-only; pre-aggregate nightly if needed.
- **Plan**: add 3 summary widgets (status breakdown, assignee load, overdue); add export CSV next.
- **Best practice**: index `issues.status`, `issues.assignee_id`, `issues.due_date`; avoid counting in Blade.

#### 2. Public REST API v1
- **Goal**: machine-readable access for integrations and future frontends.
- **Flow**: Sanctum token auth → route → resource controller → JSON resource.
- **Architecture**: versioned namespace `/api/v1`, consistent envelope `{data, meta, links}`.
- **Plan**: issue CRUD + project list + comment list; add throttling per token; document with Scribe.
- **Best practice**: never expose internal IDs only; use `key` slug for issues; rate limit; validate via Form Request.

#### 3. Automation rules
- **Goal**: replace one-off event listeners with user-defined rules.
- **Flow**: event fired → rule evaluator checks conditions → actions execute.
- **Architecture**: `automation_rules` table with JSON conditions/actions; dispatcher listens to domain events.
- **Plan**: support 3 triggers (status change, assignee change, create); 2 actions (assign, notify); admin UI toggle.
- **Best practice**: keep rules declarative; evaluate in a service, not controller; disable rule if execution errors > threshold.

#### 4. External integrations
- **Goal**: link commits/PRs to issues without tight coupling.
- **Flow**: repo webhook → verify signature → enqueue job → match commit msg to issue key → attach.
- **Architecture**: lightweight webhook handler + job queue; store `commit_sha`, `url`, `author`.
- **Plan**: GitHub first; store only reference; never auto-close unless explicit action mapped.
- **Best practice**: verify HMAC signature; idempotent by `commit_sha`; fallback to queue if webhook delayed.

#### 5. Multi-tenant readiness
- **Goal**: isolate data per customer/org when product goes public.
- **Flow**: incoming request → resolve tenant from host/subdomain → global scope applies.
- **Architecture**: add `tenant_id` FK on `projects`, `users`, core tables; use `TenantAware` global scope.
- **Plan**: schema migration only; keep single-DB for now; queue per tenant later.
- **Best practice**: unique `tenant_id + key` for projects; never rely on DB-level tenant isolation before tenancy is enforced in middleware.

#### 6. Custom workflows
- **Goal**: replace hardcoded status enum with per-project state machine.
- **Flow**: project picks workflow → issue transitions validated against transitions table.
- **Architecture**: `workflows`, `workflow_statuses`, `workflow_transitions`; validator service called before status change.
- **Plan**: Jira-like simple workflow (To Do → In Progress → Done); add conditions (required fields) later.
- **Best practice**: keep transition logic in service; controller only calls `canTransition($from, $to)`; log invalid attempts.

#### 7. Custom fields + screens
- **Goal**: allow projects to add fields beyond core title/status/priority.
- **Flow**: admin defines field → assigns to issue type + screen → form renders dynamically.
- **Architecture**: `custom_fields`, `custom_field_values` (JSON); screen config stored as mapping.
- **Plan**: support text/number/date/select types; render in create/edit/detail.
- **Best practice**: validate via dynamic rules based on field type; never trust client-side config alone.

#### 8. Permission/visibility schemes
- **Goal**: restrict issue visibility beyond simple project membership.
- **Flow**: issue rendered → check scheme → filter users/roles allowed to view.
- **Architecture**: `issue_visibility_schemes` + `issue_visibility_entries`; cached per project/user.
- **Plan**: start with "public to project" vs "private to assignee/reporter/lead"; expand later.
- **Best practice**: cache visibility checks; reject at query level, not just hide UI.

#### 9. Fix versions / affects versions / milestones
- **Goal**: track releases and target versions per issue.
- **Flow**: project creates version → issues mark fix/affects version → board/list show version badge.
- **Architecture**: `versions` table; pivot `issue_versions` with type (fix/affects); release date + archived flag.
- **Plan**: add to issue detail + filter; overdue version shown red.
- **Best practice**: archive instead of delete; use `released_at` to mark shipped.

#### 10. Issue linking
- **Goal**: express relationships beyond parent-child.
- **Flow**: user selects issue → choose link type → bidirectional reference stored.
- **Architecture**: `issue_links` (source_id, target_id, type); enum/blocks/relates/duplicates/clones.
- **Plan**: show linked issues in detail sidebar; prevent duplicate links; validate no cycle.
- **Best practice**: treat duplicates as symmetric; keep one canonical link pair to avoid doubles.

#### 11. Time tracking / work logs
- **Goal**: log time spent per issue for estimation and billing.
- **Flow**: user starts timer or enters time → saved to `worklogs` → aggregated in issue.
- **Architecture**: `worklogs` (issue_id, user_id, started_at, time_spent, comment); aggregate via query, not denormalized column.
- **Plan**: simple manual entry + list; add timer button later.
- **Best practice**: store seconds; render humanized; aggregate in service layer.

#### 12. Service management / SLA
- **Goal**: optional module for support/ops teams to track response/resolve time.
- **Flow**: SLA scheme defines target → events tracked → breached SLA flagged.
- **Architecture**: `sla_schemes`, `sla_events`; business hours table for working-time calculation.
- **Plan**: P1 breach notification; simple priority-based target (e.g. 4h for urgent).
- **Best practice**: compute elapsed time using working hours, not calendar time; store milestone events, not just final state.

#### 13. Advanced automation UI
- **Goal**: non-technical users to create automation rules.
- **Flow**: rule builder UI → JSON stored → evaluator runs on events.
- **Architecture**: lightweight PHP rule evaluator; no external engine until >100 rules.
- **Plan**: visual builder for trigger + condition + action; test-run button.
- **Best practice**: validate rule syntax before save; disable rule on loop detection; keep evaluator pure PHP for portability.

## Decision log
- Single-org first → multi-tenant deferred (schema-ready only).
- Kanban + List both in MVP (user requested Kanban directly).
- Notifications in MVP (user confirmed Q4).
- Comments with image + rich text in MVP (user confirmed Q3).
