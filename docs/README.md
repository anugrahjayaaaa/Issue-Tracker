# Laravel Base Project

Base core system built on Laravel (latest) — the foundation for a web admin
plus a mobile API integration. This document is the **single source of truth**.
AI agents and developers MUST read these docs before coding; if code conflicts
with docs, **docs win** (change via an ADR).

## How AI uses these docs
- Start every task with the `dev-lifecycle` skill, then read the relevant doc first.
- Each phase gate must be green before proceeding.
- New requirement → add to `CONTRIBUTING.md` (open items) or write a new ADR.

## Docs structure
| File | Contents |
|------|----------|
| README.md | This index, goal, usage |
| CONTRIBUTING.md | Dev setup, conventions, Definition of Done, open items |
| architecture.md | Stack, layered architecture, v1 modules, decisions |
| PRD.md | Product Requirements: scope, users, authz model, success metrics |
| coding-standard.md | **Mandatory** coding rules — route-level authz, base Controller, Form Requests |
| auth.md | Authentication (username/phone, strong pwd, lockout, verify) |
| authorization.md | Dynamic RBAC + management UI |
| feature-flags.md | Feature flags (layer above RBAC) |
| audit-trail.md | Logging all user actions |
| notifications.md | Native Laravel notifications (bell + page) |
| api-tokens.md | Sanctum personal tokens (web UI) + mobile |
| api.md | Full REST API `/api/v1` reference |
| log-viewer.md | Web log viewer (`/logs`) |
| frontend-theme.md | AdminLTE 4.9.1, dark default, responsive, sidebar |
| observability.md | Logger + Sentry + health check |
| api-mobile.md | Sanctum API /api/v1 for mobile |
| i18n.md | Multi-locale web UI + REST API (en/id), file→DB override |
| licensing-and-billing.md | Licensing + billing (Model 1/2, dummy PG). **§11 plan limit model** — `limits` keys, dynamic feature→permission map, server guards |
| packages.md | Verified packages (don't reinvent) |
| adr.md | Architecture Decision Records |
| custom/README.md | **Custom features** for derived projects (separate from base docs) |

## LOCKED DECISIONS (user confirmed, 27 Aug 2026)
1. All proposed extra features are in v1 (reset pwd, lockout, verify, self-service, session mgmt, seed, dashboard, /up).
2. Template **AdminLTE 4.9.1** (dist zip from GitHub release).
3. Verification is **email only**; MFA/2FA is not v1.
4. i18n: **English + Indonesian** (en first, id mirrored). Files are source of truth; `language_lines` DB rows override at runtime.
5. Single-tenant v1 (schema ready to add `tenant_id` later).
6. Sidebar: main menu + a "Template" section (demo from zip) below it.
