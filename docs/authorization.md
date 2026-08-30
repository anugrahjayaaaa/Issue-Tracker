# Authorization (Dynamic RBAC)

Uses `spatie/laravel-permission`. Roles & permissions are created/edited via UI (dynamic).

## Model
- `permissions`: granular actions (`user.view`, `user.create`, `role.edit`).
- `roles`: a set of permissions; a user may have multiple roles.
- Super-admin: bypass (special guard).
- Role & Permission are **custom models** (`App\Models\Role`, `App\Models\Permission`)
  extending spatie with `SoftDeletes`; `config/permission.php` points spatie to them.
  Always import the custom models, never `Spatie\Permission\Models\*` directly.

## Actions & permissions
- Each resource has: `*.view`, `*.create`, `*.edit`, `*.delete`,
  `*.restore` (soft-deleted → active), `*.force-delete` (permanent, trashed only).
- Restore/force-delete buttons appear only when the row is trashed AND the caller
  holds `*.restore` / `*.force-delete`.

## Management pages (required)
1. Roles: list, create, edit, delete, attach/detach permissions.
2. Permissions: list, create, edit, delete (or seed only).
3. User ↔ Role: assign from user management.

## Enforcement
- **Route-level middleware**, never in a controller constructor:
  ```php
  Route::resource('users', UserController::class)
      ->middleware(['can:user.view', 'feature:users']);
  ```
- `can:{perm}` is Laravel's built-in authorization middleware (spatie
  permission registered as the gate). Do **not** use a custom `permission:`
  middleware, and do **not** call `$this->middleware()` inside a controller
  `__construct()` — see `docs/coding-standard.md` §Authorization.
- Ownership checks (if needed) go in a Form Request `authorize()` or a Policy,
  not in the controller body. Current modules gate purely by permission; no
  per-resource Policy is registered.
- Every role/permission change → audit trail.
- **Feature flags** sit above RBAC: a module route is also wrapped in
  `feature:{slug}` (see `docs/feature-flags.md`). Permission alone is not enough —
  the feature must be enabled, or the route 404s and its sidebar item hides.

## Initial seed
- Roles `super-admin`, `admin`, `staff` + default permissions.
- First seeded user = super-admin.

## Translations (Settings)
- Permissions: `translation.view`, `translation.edit` (edit-only module — no create/delete routes).
- Also gated by the `translations` feature flag (`feature:translations`).

## Gate
- Every route/action is protected by a permission (`can:` middleware). RED if
  any action lacks authz. Authorization lives on the route, not in a controller
  constructor (see `docs/coding-standard.md` §Authorization).
