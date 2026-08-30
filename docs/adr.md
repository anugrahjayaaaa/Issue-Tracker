# ADR — Architecture Decision Records

## ADR-0001: Template AdminLTE 4.9.1 (zip)
- Context: free template, modern, dark default, responsive, familiar.
- Decision: AdminLTE 4.9.1, take `adminlte-4.9.1.zip` (dist only) →
  `public/vendor/adminlte/`. Load locally.
- Alt: Tabler (REJECTED).
- Consequences: + "Template" section in sidebar (zip demo, copy-paste).

## ADR-0002: RBAC via spatie/laravel-permission
- Decision: use spatie (standard, maintained); manage via UI.

## ADR-0003: Mobile auth = Sanctum token
- Decision: Sanctum; web=session guard, mobile=token guard.
- Consequences: lockout/audit shared via same service.

## ADR-0004: Soft delete + automatic audit
- Decision: `SoftDeletes` on core models; `activitylog` via observer.
- Consequences: restore possible; log permanent (retention job for purge).

## ADR-0005: Dark mode default
- Decision: `data-bs-theme="dark"` default; choice in localStorage + DB user.

## ADR-0006: Single-tenant v1
- Decision: single-tenant; schema ready for `tenant_id` later. (YAGNI multi-tenant)

## ADR-0007: Email verification only, no MFA in v1
- Decision: verify email; MFA/2FA & SMS OTP OUT of v1.

## ADR-0008: Sidebar "Template" section
- Decision: below main menu, a "Template" group with zip demos (read-only).

## ADR-0009: Feature flags above RBAC
- Context: need to disable whole modules without touching code or redeploy, and let
  a `feature.manage` holder keep access while a flag is off.
- Decision: **Laravel Pennant** (`laravel/pennant`) — `config/pennant.php` declares flags,
  `AppServiceProvider::boot()` defines them, `Feature::active()` + `feature:{slug}` route
  middleware (stacked with `can:{perm}`) gate access; sidebar visibility via `@feature()` Blade directive.
  A flag off 404s the route and hides its menu item for everyone (kill-switch,
  including `feature.manage` holders) — they re-enable from `/features`. Storage = Pennant DB store.
- Consequences: closest path to change the enabled state is the `/features` UI (under
  Settings); fails closed when a feature row is missing.

## ADR-0010: Authorization gate lives on the route, not the controller
- Context: PlanController put `$this->middleware('can:feature.manage')` in its
  `__construct()`. It crashed with `Call to undefined method ...PlanController::middleware()`
  because the base `Controller` (empty abstract) did not extend `Illuminate\Routing\Controller`,
  so the framework `middleware()` helper was absent. Every sibling controller escaped the bug
  only because they gate at the route level (`routes/web.php`) instead.
- Decision: **all permission + feature-flag gates are declared on the route** via
  `->middleware(['can:{perm}', 'feature:{slug}'])`. Controllers must NOT call
  `$this->middleware()` in a constructor. The base `Controller` extends
  `Illuminate\Routing\Controller` so the helper exists if ever needed, but routes are the
  single place authorization is wired.
- Consequences: one visible place for authz; no dependency on controller base wiring;
  new modules follow `docs/coding-standard.md` §Authorization.
