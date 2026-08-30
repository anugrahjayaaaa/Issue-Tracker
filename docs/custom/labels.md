# Labels / Tags

Per-project labels attached to issues (many-to-many).

- Tables: `labels` (project_id, name, color) + `issue_label` pivot.
- Managed on the project page (`projects.show`) by `project.manage` holders: create / edit / delete.
- Assigned on issue create/edit via checkboxes; `labels.*` validated to belong to the issue's project.
- List view filters by `label_id` (`whereHas('labels')`).
- Deleting a label cascades untag (pivot `cascadeOnDelete`).
- Permission gate: `project.manage` (same as members).
