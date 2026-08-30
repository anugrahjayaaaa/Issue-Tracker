# Notifications (assign / mention / status)

> Custom feature for Issue Tracker. Extends base `docs/notifications.md`
> (native Laravel DB notifications + bell). Built on Laravel Base Project.

## Goal
Automatically notify users on issue events: assigned to you, mentioned in a
comment, issue status changed. Reuse the base DB notification channel + header
bell (already present in base — `NotificationController`, bell in layout).

## Scope
- In scope: 3 event notifications (assign, mention, status-change). Fired from
  Issue/Comment services.
- Out of scope: email digest, Slack/Teams webhook (Phase 3).

## How it works
- Reuse base `app/Notifications/*` + `database` channel. Create
  `IssueAssigned`, `Mentioned`, `IssueStatusChanged` notification classes
  (extend base pattern).
- Fire points (single source — services, not controllers):
  - Issue store/update with new assignee → `IssueAssigned` to assignee.
  - Comment store with `@mention` → `Mentioned` to each mentioned user.
  - Issue status change → `IssueStatusChanged` to reporter + assignee
    (skip if same user).
- Bell + `/notifications` page already exist in base — reuse as-is.

## Routes / UI
Reuse base `/notifications` (no new routes needed).

## i18n
Notification copy keys: `messages.notifications.issue_assigned`,
`.mentioned`, `.issue_status_changed` (en/id).

## Tests
`tests/Feature/NotificationTest.php`: assign fires notification; mention fires
to correct user; status change fires to reporter+assignee. SQLite `:memory:`.
