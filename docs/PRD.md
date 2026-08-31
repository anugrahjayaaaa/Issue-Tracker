# PRD — Laravel Base Project

> Single source of truth for what this system is, who it serves, and the
> constraints every feature must respect. Implementation standards live in
> `docs/coding-standard.md` (read it before writing code).

## 1. Purpose
A reusable Laravel 13 admin base: auth, RBAC, audit, feature-flagged modules,
and (v1.x) licensing + billing scaffolding. Built so new modules drop in by
following one convention — no per-module bespoke wiring.

## 2. Users & roles
- **super-admin** — bypasses RBAC (special guard), holds everything incl. `feature.manage`.
- **admin** — manages users/roles/permissions within granted permissions.
- **staff** — operational access (e.g. translation edits), gated by permissions.
- **end user (self-service)** — own profile, sessions, api-tokens; never other users.

## 3. Functional scope (v1)
1. Auth: login (username|phone), logout, register, reset password, failed-login
   lockout (5 fails → 15m), email verify, profile self-service, session mgmt.
2. RBAC: dynamic roles/permissions via UI (spatie/laravel-permission, custom
   `Role`/`Permission` models with `SoftDeletes`).
3. Audit Trail: read-only, filter by user/action/date (spatie/activitylog).
4. User Management: CRUD + soft delete + restore + force-delete + lock.
5. Feature flags (Laravel Pennant) above RBAC — kill-switch per module.
6. Dashboard home.
7. API: Sanctum `/api/v1` (token guard), shared lockout/audit with web.
8. Licensing + Billing (v1.x): plans CRUD, signed license service, entitlement
   gate, dummy payment (`billing.fake` flag) + webhook hookpoint for real PG.

## 4. Non-functional requirements
- **Stack (locked):** PHP 8.3+, Laravel 13, MySQL 8 (utf8mb4_0900_ai_ci),
  Bootstrap 5.3 + AdminLTE 4.9.1 (Blade), Pest.
- **Security:** every route/action gated by a permission; every module wrapped
  in its feature flag; CSP/HSTS/X-Frame-Options headers; no secrets in repo.
- **Consistency:** one authorization pattern repo-wide (see §5). The
  PlanController constructor-middleware incident (crashed because base
  `Controller` lacked the framework `middleware()` helper) is the canonical
  "do not do this" example — fixed by routing gate to the route, not the
  constructor. See ADR-0010.
- **Maintainability:** thin controllers, business logic in services/actions,
  validation in Form Requests, docs match code.

## 5. Authorization model (mandatory)
Access to any protected action requires **all** of:
1. Authenticated session (`auth` middleware group).
2. Permission gate (`can:{perm}` middleware on the route).
3. Module enabled (`feature:{slug}` middleware on the route) — except
   `feature.manage` holders operating `/features`.

**Where the gate lives:** on the **route**, never in a controller
`__construct()`. See `docs/coding-standard.md` §Authorization.

## 6. Out of scope (v1)
MFA/2FA, SMS OTP, in-app notifications, file-upload module, multi-tenant.

## 7. Success metrics
- New module added by: (a) route + `can:`/`feature:` middleware, (b) controller
  thin + Form Request, (c) doc entry — no framework base hacks.
- Zero "undefined method" from missing base-class wiring.
- Pest green on `memory_limit=2G` (env OOM at default; not a code bug).
