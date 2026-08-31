# Issue Tracker — AI Agent Guide

A Jira-like issue-tracking app built on top of the **Laravel Base Project**
(auth, RBAC, audit, feature flags, i18n). This file is the **entry point for AI
agents**. Human devs start at `docs/README.md` + `docs/custom/README.md`.

## Before any task
1. Read `docs/README.md` (base single source of truth) AND `docs/custom/README.md`
   (this project's features: projects, issues, kanban, comments, notifications).
2. If code conflicts with docs → **docs win**; change via ADR (`docs/adr.md`).
3. Follow the dev-lifecycle skill if available; otherwise: branch → code → test → PR.

## Stack (current)
- PHP 8.3+, Laravel 13, MySQL 8.
- AdminLTE 4 + Bootstrap 5.3 + Bootstrap Icons, themed via `public/vendor/app-theme.css`
  (tokens `--lbp-*`), dark default. **No npm/Vite build step** — all CSS/JS ships from
  `public/vendor/*` (committed). Do NOT add a build pipeline or `package.json`.
- Pest for tests; spatie (permission, activitylog), sanctum, pennant.

## Hard rules (AI must obey)
- **Never merge to `main`.** Work on `feature/<name>` branch, push, open PR.
- Run tests before declaring done: `php artisan test` (or a filtered slice).
- Authorization: gate on the **route** (`can:` + `feature:` middleware), never in a
  controller `__construct()`. See `docs/coding-standard.md` + `docs/PRD.md` §5.
- Validation: dedicated Form Request, never inline `$request->validate()` in controllers.
- i18n: `lang/{en,id}/{messages,ui}.php` are source of truth; spatie `language_lines`
  override at runtime. `ui()` = ui.php (page text), `__('messages.*')` = messages.php
  (domain/API). Never hardcode English in Blade.
- Secrets: never commit; license/webhook secrets come from env and fail closed if missing.
- Docs match code. Base changes → `docs/`; project features → `docs/custom/`.

## Scope boundaries
- This repo IS the Issue Tracker product. The Laravel Base Project is the upstream
  foundation (separate repo) — do not confuse the two.
- Out of scope (v1): MFA/2FA, SMS OTP, multi-tenant, generic file-upload module.
- Custom permission taxonomy: global `project.manage`, `issue.*`, `comment.*`; plus
  project-level roles (`lead`/`member`/`viewer`) stored in `project_members`.
  Effective access = global permission AND a project_members row for the target project.
