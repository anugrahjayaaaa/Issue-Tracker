# Contributing & Conventions

## Dev setup
```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
npm install && npm run dev   # or build
```
- PHP 8.3+, Node 20+, MySQL 8.
- AdminLTE lives in `public/vendor/adminlte/` (from dist zip 4.9.1).

## Code conventions
- PHP: PSR-12, `declare(strict_types=1)`, type hints required.
- Naming: `snake_case` DB, `camelCase` JS, `PascalCase` class.
- **Thin controllers:** NO inline `$request->validate()` in controllers.
  - Every request with validation MUST use a dedicated **Form Request**
    (`app/Http/Requests/<Domain>/<XxxRequest.php`) that extends
    `Illuminate\Foundation\Http\FormRequest`.
  - Form Request holds: `rules()`, `messages()` (optional), `authorize()`
    (gate/permission check), and `prepareForValidation()` if needed.
  - Controllers stay thin: type-hint the Form Request, use `$request->validated()`.
- Group Form Requests by domain subfolder: `Auth/`, `User/`, `Rbac/`, `Profile/`, `Api/`, `Locale/`, `Session/`, `Translation/`.
- Core models use `SoftDeletes`; mutations logged via observer/activitylog.
- Don't reinvent: use spatie/permission, activitylog, sanctum.

### Form Request convention (must-follow)
- File: `app/Http/Requests/<Domain>/<Action><Resource>Request.php`
  (e.g. `Auth/LoginRequest`, `User/UserStoreRequest`).
- `authorize()`: return the policy/permission gate (e.g. `$this->user()->can('user.create')`),
  NOT `true` for protected resources. Public endpoints (login, forgot/reset password)
  may return `true`. Self-service (own profile) returns `true` because the route
  is already `auth`-gated and scoped to the authenticated user.
- `rules()`: return the validation array — single source of truth for input shape.
- Keep rules readable: group related keys, use `sometimes`/`required` correctly.
- In controller: `public function store(UserStoreRequest $request)` → `$data = $request->validated();`

## Definition of Done (per PR)
- [ ] Scope + acceptance met
- [ ] Tests green & cover the change (Pest)
- [ ] Security checklist green (boundary validation, authz, SQL param, no secret)
- [ ] **Validation lives in a Form Request (no inline validate in controllers)**
- [ ] Code review passed (not just "LGTM")
- [ ] Docs match code (update OpenAPI/ADR if needed)
- [ ] CI green + staging smoke OK

## Git
- Branches: `feature/*`, `fix/*`, `chore/*`. PR into `main`.
- Commits imperative: "Add login lockout", not "fixing".
- Separate refactor from feature.

## Open items (not v1)
- MFA/2FA, SMS OTP, in-app notifications, file-upload module.
- Subscriber-side RBAC enforcement: `can_create_roles` + `allowed_permissions` gate at `RoleController`/`PermissionController` (doc licensing §11.4). Currently open — plan limits are NOT enforced when subscribers create roles/permissions. Add when subscriber RBAC is needed.
- Midtrans integration: swap `billing.fake=true` dummy checkout for real PG (Midtrans Snap + webhook). See `licensing-and-billing.md` §6.
