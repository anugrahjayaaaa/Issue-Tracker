# Comments — Feature & Behaviour Spec (Jira / Linear / GitHub-style)

> Custom feature for Issue Tracker, built on Laravel Base Project.
> Replaces the older `comments.md` MVP notes. Last revised: 2026-08-31.

## 1. Purpose & principles

Comments are the conversation layer on an issue. They must feel like a chat
thread, not a form dump. Borrowing from Jira, Linear, GitHub Issues, YouTrack:

- **Fast to add**, rich by default (formatting, images, mentions).
- **Safe to edit/delete** — author + leads control their own, with full audit trail.
- **Always legible** — oldest→newest, scrollable, collapsible when long.
- **No data loss** — deletions are logged; inline images are cleaned up.

## 2. Competitive feature matrix (what the popular tools do)

| Capability | Jira | Linear | GitHub Issues | YouTrack | Our target |
|------------|------|--------|--------------|----------|------------|
| Rich text (bold/italic/code/quote/lists) | ✅ | ✅ (Markdown/ProseMirror) | ✅ (Markdown) | ✅ | ✅ TinyMCE |
| Inline image (paste/drag) | ✅ | ✅ | ✅ (drag to textarea) | ✅ | ✅ scoped upload |
| @mention (user) | ✅ | ✅ | ✅ | ✅ | ✅ parsed, notify |
| Emoji reactions | ⚠️ limited | ✅ | ✅ | ❌ | ⏳ Phase 2 |
| Threaded replies | ✅ (2025: show/hide replies) | ✅ (sub-issues/comments) | ❌ (flat + linked) | ✅ | ⏳ Phase 2 |
| Edit own (history kept) | ✅ | ✅ | ✅ | ✅ | ✅ |
| Delete (author/lead) | ✅ | ✅ | ✅ (author/maintainer) | ✅ | ✅ author/lead |
| Edit/delete audit in activity | ✅ | ✅ | partial | ✅ | ✅ spatie activity |
| Notifications on new/mention | ✅ (notification scheme) | ✅ | ✅ | ✅ | ✅ (see notifications.md) |
| Email → comment | ✅ (mail handler) | ❌ | ❌ | ✅ | ❌ out of scope |
| Load more / pagination | ✅ | ✅ (virtualized) | ✅ | ✅ | ✅ scroll + "load more" |
| REST/GraphQL API | ✅ | ✅ | ✅ | ✅ | ⏳ Phase 2 (`/api/v1/comments`) |
| Markdown shortcuts | ⚠️ | ✅ | ✅ | ✅ | ⏳ Phase 2 |

Legend: ✅ done · ⚠️ partial/limited · ⏳ planned · ❌ out of scope.

## 3. Required behaviours (spec)

### 3.1 Authoring
- Comment box uses the **same rich-text editor as description** (TinyMCE),
  theme-aware (dark/light), with inline image paste/drag.
- Empty submit blocked (`body` required, max 10 000 chars).
- Enter = newline; `Ctrl/Cmd+Enter` = submit (chat convention, Linear/GitHub).
- Image upload scoped: `projects/{slug}/issues/{code}/comments/{id}/description/`.
  Hardcap 5 MB/file, soft cap = plan storage quota.

### 3.2 Mentions
- `@username` parsed from HTML body → notification to mentioned user.
- Mentions render as a highlighted chip (not raw text) when re-rendered.
- Only project members are mentionable.

### 3.3 Edit
- Author, or project **lead**, may edit.
- Editor pre-fills current body (TinyMCE inline, lazy-init on show).
- On save: `pruneUnusedImages(old, new)` deletes images dropped from the body.
- Edit is **non-destructive**: old body is captured in `activity_log`
  (`comment_updated` stores `old`/`new` diff). Body itself is overwritten
  (no version history UI yet — see §6).

### 3.4 Delete
- Author or project lead may delete (native `confirm()` replaced by shared
  `#deleteModal`).
- On delete: `deleteStorageFolder(comment folder)` removes all inline images.
- Delete is **hard delete**; the `comment_deleted` activity row remains in the
  timeline (subject reference preserved by spatie even after row gone).

### 3.5 Permissions model
| Action | Allowed when |
|--------|-------------|
| Create | `comment.create` + project member (any role) |
| Edit   | `comment.edit` AND (author OR project lead) |
| Delete | `comment.delete` AND (author OR project lead) |
| View   | project member (any role) |

### 3.6 Activity & notifications
- Observer logs `comment_created` / `comment_updated` / `comment_deleted`.
- `Issue::activityTimeline()` aggregates issue + its comments → single feed.
- New comment + mention → notification (see `docs/custom/notifications.md`).

### 3.7 Ordering & scale
- Chronological oldest→newest; new-comment box pinned at the bottom.
- Comment list lives in a `max-height` scroll container.
- > N comments → "Load more" (cursor pagination) rather than one giant DOM.
- Each comment: avatar, author, relative time, body, attachments, action row.

## 4. UX flow

```
Issue detail
├─ Description (rich)
├─ Comments (card, scrollable)
│   ├─ comment (oldest)
│   │   ├─ avatar · name · "3m ago"
│   │   ├─ body (rich, images)
│   │   └─ [edit ✎] [delete 🗑]  (if permitted)
│   ├─ …
│   └─ comment (newest)
│   └─ [ Load more ]  (if overflow)
│   └─ ── comment composer ──
│       ├─ TinyMCE (label: "Comment")
│       └─ [ Post comment ]
└─ Activity timeline (issue + comment events)
```

Edit inline: click ✎ → body becomes TinyMCE → Save/Cancel. Original body
hidden during edit. Cancel restores it.

Delete: click 🗑 → `#deleteModal` → confirm → row removed, images purged,
`comment_deleted` appended to timeline.

## 5. Best practices we adopt (popular consensus)

1. **Same editor everywhere** — description and comments share one component
   (`partials.rich-text-field`). No divergent markup.
2. **Lazy-init hidden editors** — don't mount TinyMCE inside `display:none`;
   init on first show (avoids blank toolbars).
3. **Scoped image paths per entity** — never a shared `/uploads` bucket; quota
   and cleanup stay local to the issue/comment.
4. **Prune on edit, purge on delete** — orphaned images are a silent disk leak.
5. **Audit, don't ask** — every mutation writes an activity row; users trust
   the timeline.
6. **Mentions drive notifications** — the #1 reason comments matter.
7. **Member-scoped mentions** — only people in the project are mentionable.
8. **Scroll, don't sprawl** — cap rendered comments; load more on demand.
9. **Modal, not `confirm()`** — consistent delete UX across the app.
10. **Theme-aware editor** — follows the app dark/light toggle live.

## 6. Implementation status (this repo)

### Done
- [x] Rich-text comment (TinyMCE, theme-aware, lazy init) — `partials/rich-text-field`
- [x] Inline image upload scoped to `…/comments/{id}/description/`
- [x] Create / edit (owner+lead) / delete (owner+lead) with `#deleteModal`
- [x] `pruneUnusedImages()` on edit, `deleteStorageFolder()` on delete
- [x] `CommentObserver` → `comment_created/updated/deleted` in timeline
- [x] Mention parse (`parseMentions()`) + notification hook
- [x] Comment label fixed to "Comment"; list scrollable
- [x] Tests: `CommentTest` (scoped upload, dropped-image cleanup)

### TODO (phased)
- [ ] **Threaded replies** — `parent_comment_id`, show/hide (Jira 2025 pattern)
- [ ] **Emoji reactions** — `comment_reactions` pivot; cheap, high signal
- [ ] **Load more** — cursor pagination when comment count > threshold
- [ ] **Markdown shortcuts** in composer (GitHub/Linear feel)
- [ ] **Edit history UI** — diff viewer over `activity_log` old/new
- [ ] **REST API** `/api/v1/comments` (Phase 2)
- [ ] **Ctrl/Cmd+Enter to submit** composer shortcut
- [ ] **Mention chip rendering** (replace raw `@name` with styled span)

## 7. Open decisions (discuss)

1. **Threading model** — flat-with-replies (Jira) vs strict tree (Reddit).
   Recommend: one level of replies (Jira-style), no deep nesting.
2. **Edit history** — keep full versions or just last+activity diff?
   Recommend: activity diff only for v1 (cheap, auditable).
3. **Soft-delete comments** — currently hard delete. Keep hard delete but
   retain activity; revisit if compliance needs the row.
4. **Load-more threshold** — propose 20 comments per page.
5. **Reactions scope** — limit to a small preset set (👍 ❤️ 🚀 👀) to avoid
   emoji sprawl.

## 8. References
- Jira: threaded comments (2025 rollout), notification schemes, mail handler.
- Linear: ProseMirror composer, Cmd+Enter, sub-issues, GitHub sync.
- GitHub Issues: Markdown, mentions, drag-image, reactions, maintainer delete.
- YouTrack: threaded comments, rich text, command hints.
