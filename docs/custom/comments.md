# Comments (rich text + image upload)

> Custom feature for Issue Tracker. Built on Laravel Base Project.

## Goal
Discuss an issue in rich-text comments, with inline image upload, edit, and
delete. Supports `@mention` (drives notifications, see notifications.md).

## Scope
- In scope: Comment CRUD on an issue, rich-text (HTML) body, image attachment
  upload (drag/paste), edit, delete (owner or lead), `@mention` parsing.
- Out of scope: threaded replies (Phase 2), emoji reactions.

## Models / Tables (new)
| Table | Purpose | Notes |
|-------|---------|-------|
| `comments` | id, issue_id, user_id, body(html), created/updated, SoftDeletes | migration `create_comments_table` |
| `attachments` | id, comment_id(nullable), issue_id(nullable), user_id, path, mime, size, created | migration `create_attachments_table` |

## Routes (auth group, `feature:issues`)
| Method | URI | Controller@action | Gate |
|--------|-----|-------------------|------|
| POST | `/issues/{issue}/comments` | `CommentController@store` | `can:comment.create` + issue-scope |
| PUT | `/comments/{comment}` | `CommentController@update` | `can:comment.edit` + owner/lead |
| DELETE | `/comments/{comment}` | `CommentController@destroy` | `can:comment.delete` + owner/lead |
| POST | `/comments/attach` | `CommentController@attach` | `can:comment.create` (image upload) |

## How it works
- Rich text: store HTML; **sanitize on save** with
  `spatie/laravel-html` or `mews/purifier` if present in base — else a minimal
  allowlist strip. Ponytail: use base-installed sanitizer if any; add
  `mews/purifier` only if none exists. (`# ponytail: minimal allowlist until
  purifier confirmed`)
- Image upload: store under `storage/app/public/comments`; serve via
  `storage:link`. Validate mime (image/*) + size (<=2MB).
- `@mention`: parse `@username` from body; fire notification to mentioned user.
- Audit: comment create/edit/delete via observer.

## UI / API surface
- Web: comment box in `issues/show.blade` using **TipTap** (ProseMirror) —
  extensible for @mention / image / slash later. Image paste/drag via a custom
  TipTap upload adapter → `attachments`.
- API: `/api/v1/comments` (Phase 2).

## i18n
New keys: `ui.comments.*`. Add to `lang/{en,id}/ui.php`; reseed.

## Tests
`tests/Feature/CommentTest.php`: create rich text, image attach, edit/delete
owner, mention triggers notification. SQLite `:memory:`.
