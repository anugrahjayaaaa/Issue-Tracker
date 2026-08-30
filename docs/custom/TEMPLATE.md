# <Feature Name>

> Custom feature for <Project Name>. Built on Laravel Base Project.
> Copy this file to `docs/custom/<feature>.md` and fill in.

## Goal

What problem this feature solves and why it belongs in this project (not the base).

## Scope

- In scope: …
- Out of scope: …

## Models / Tables (new or changed)

| Table | Purpose | Notes |
|-------|---------|-------|
| `…` | … | new migration `…` |

## Routes

| Method | URI | Controller@action | Gate |
|--------|-----|-------------------|------|
| GET | `/…` | `…Controller@index` | `can:…` |

## How it works

Short walkthrough: request flow, key logic, base features reused
(SoftDeletes, RBAC `can:*`, audit observer, i18n `ui()`/`__()`).

## UI / API surface

- Web: `resources/views/…`
- API: `/api/v1/…` (if exposed)

## i18n

New keys added (en/id), group (`ui` or `messages`), reseed note:
`php artisan db:seed --class=LanguageLineSeeder`

## Tests

`tests/Feature/…Test.php` — covers: …

## Open questions / follow-ups

-
