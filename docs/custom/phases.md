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

## Decision log
- Single-org first → multi-tenant deferred (schema-ready only).
- Kanban + List both in MVP (user requested Kanban directly).
- Notifications in MVP (user confirmed Q4).
- Comments with image + rich text in MVP (user confirmed Q3).
