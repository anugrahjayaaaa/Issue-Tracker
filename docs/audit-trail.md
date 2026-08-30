# Audit Trail

Uses `spatie/laravel-activitylog`. Every user action is logged automatically.

## Must be logged
- Auth: successful login, logout, **failed login**, lockout, reset request,
  reset success, password change, account verification.
- User: create, update, delete (soft), restore, force-delete (permanent).
- RBAC: role create/edit/delete, role restore, role force-delete, permission create/edit/delete, permission restore, permission force-delete, permission attach/detach, user-role assign.
- Business modules: main CRUD (added as modules are built).

## Format
```
id | causer_id | causer_name | action | subject_type | subject_id
| description | properties(json) | ip | user_agent | created_at
```
- `properties` holds before/after delta for important updates.
- IP & user_agent required; never log password/token.

## Access
- "Audit Log" page (read-only) for specific roles.
- Filters: user, action, date range. Keyset pagination.
- **CSV export** — the Export button downloads the current filtered set (`/audit/export`).
- Log is permanent; purged by the daily `audit:purge` command (keep last
  `AUDIT_RETENTION_DAYS` days, default 365 in `.env.example`). Run manually with
  `php artisan audit:purge --days=90`.

## Gate
- Every state mutation is logged. RED if any write path lacks a log.

---

## Implementation Reference (functions)

Observers live in `app/Observers/` and are registered in `AppServiceProvider`.
All write a row to **DB table `activity_log`** (spatie/laravel-activitylog).

### `UserObserver`, `RoleObserver`, `PermissionObserver`
Each has: `created`, `updated`, `deleted` (soft), `restored`, `forceDeleted`.

**`updated($model)`** (before/after diff)
- Logs `old` (from `getOriginal()`) and `new` (the dirty fields) into `properties`.
- `password` and `remember_token` are always stripped from both maps — never logged.
- The Audit Log page renders these in the **Detail modal** (eye button per row): a
  `Field | Old | New` table, plus Time, Causer, IP, and User agent.
- Events without a field delta (login, delete, restore, …) show "No field changes recorded."

**`forceDeleted($model)`** (added for permanent-delete compliance)
- Purpose: log an unrecoverable (hard) delete to the audit trail.
- Input: the soft-deleted-then-force-deleted model.
- Output: `void`.
- State: writes one row to **DB table `activity_log`**:
  - `UserObserver` → action `user_force_deleted`
  - `RoleObserver` → action `role_force_deleted`
  - `PermissionObserver` → action `permission_force_deleted`
  - `properties` = `{ ip, user_agent }`; `causer` = currently auth'd user.
- Unlike `deleted()` (soft delete, recoverable), this is permanent.

## Subject & causer resolution
- Audit rows eager-load `subject` and `causer`. `subject` uses `withoutGlobalScopes()`
  so **soft-deleted** subjects (a deleted user/role/permission) still resolve to their
  name in the Subject column — otherwise only `#id` would show.
- Auth events (login/logout/reset) have no subject by design → Subject shows `#`.
