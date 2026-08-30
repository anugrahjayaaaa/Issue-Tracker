# Custom Features (per derived project)

This folder holds **project-specific** features built on top of the Laravel Base
Project. It is **separate from `docs/`**, which documents the shared base
platform (auth, RBAC, audit, i18n, feature-flags, etc.).

## Rule

- `docs/` → BASIC features (shared across every fork). Do not edit for
  project-specific work unless the change belongs to the base itself.
- `docs/custom/` → features added for THIS project (e.g. a Jira-clone:
  boards, issues, sprints, workflows).
- Each custom feature = one file here, named after the feature
  (copy `TEMPLATE.md` as a starting point).
- When a custom feature needs base-level changes (new migration in base, new
  shared helper), discuss first, then update `docs/` only for the base part.

## Index

| Feature | Doc | Status |
|---------|-----|--------|
| _(none yet)_ | — | — |

> Add a row here when a new custom feature doc is created.
