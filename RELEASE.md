# Release Notes

## v0.3.0 — No Ticket

**Branch:** `claude-vibe`
**Date:** 2026-05-06

---

### Overview

MySQL setup, a real dashboard, issue checklist, and several bug fixes.

---

### New Features

#### Dashboard
- Replaced the placeholder dashboard with a live data view
- 4 stat cards: Projects, Open Issues, Assigned to Me, Active Sprints
- **My Open Issues** panel — issues assigned to the current user, sorted by priority (highest first), each linking to the issue detail page
- **Recent Activity** panel — 10 most recently updated issues across all team projects
- New `DashboardController` powering the page with scoped queries per team

#### Issue Checklist
- Optional checklist section on the issue detail page
- Hidden until the user clicks **Add checklist**; auto-shown if the issue already has items
- Progress bar showing completion ratio (e.g. 3/5)
- Add items via input + Enter or the + button
- Toggle done/undone with a checkbox; completed items get struck through
- Delete items on hover
- Persists immediately to the server on every change
- Stored as a `checklist` JSON column on the `issues` table

#### Artisan Schema Import Command
- `php artisan db:import-schema` — imports `database/sql/schema.sql` into the connected MySQL database
- `--fresh` flag drops existing app tables before re-importing
- Strips SQL comments correctly before executing statements

---

### Bug Fixes

- Fixed `<Select.Item value="">` crash (Radix UI error) in `issue-modal.tsx` and `issue.tsx` — replaced empty-string values with `"none"` sentinel for Assignee and Sprint selects

---

### Database Changes

- Added `checklist JSON DEFAULT NULL` column to the `issues` table
- Database renamed from `noticket` to `no_ticket`
- Re-import schema with `php artisan db:import-schema --fresh` to pick up the new column

---

### Changed Files

**Backend**
- `app/Http/Controllers/DashboardController.php` — new
- `app/Http/Controllers/Issues/IssueController.php` — expose `checklist` in show; validate `checklist` array in update
- `app/Models/Issue.php` — added `checklist` to fillable; added `array` cast
- `app/Console/Commands/ImportSchema.php` — new
- `database/sql/schema.sql` — added `checklist` column to `issues`
- `config/database.php` — default connection changed from `sqlite` to `mysql`
- `routes/web.php` — dashboard route switched to `DashboardController`
- `.env` / `.env.example` — updated to MySQL with `no_ticket` database

**Frontend**
- `resources/js/pages/dashboard.tsx` — full rewrite with stats, my issues, recent activity
- `resources/js/pages/projects/issue.tsx` — added checklist section; fixed Select empty-value bug
- `resources/js/components/project/issue-checklist.tsx` — new
- `resources/js/components/project/issue-modal.tsx` — fixed Select empty-value bug
- `resources/js/types/projects.ts` — added `ChecklistItem` type; added `checklist` to `IssueDetail`

---

## v0.2.0 — No Ticket

**Branch:** `claude-vibe`
**Date:** 2026-04-27
**Files changed:** 30 files · +2364 lines

---

### Overview

Full project management feature set — projects, sprints, issues, and comments — modelled after Jira, built on top of the existing Laravel 13 + Inertia + React 19 + Tailwind CSS 4 stack.

---

### New Features

#### Projects
- Create projects with a name, short key (e.g. `NT`), and optional description
- Project key is auto-generated from the name and enforced unique per team
- Projects list page with card grid view showing issue count
- Projects scoped to the current team (inherits team membership auth)

#### Board (Kanban)
- Four-column kanban board: **To Do → In Progress → In Review → Done**
- Drag-and-drop issue cards between columns with optimistic UI updates
- Board is scoped to the active sprint; shows an empty-state prompt when no sprint is active
- Complete Sprint button in the board header

#### Backlog
- Lists all planned/active sprints with collapsible sections
- Shows the pure backlog (issues with no sprint assignment) at the bottom
- Inline **Create Sprint** — names the sprint automatically (`KEY Sprint N`)
- **Start Sprint** (only one active sprint allowed at a time) and **Complete Sprint** actions per sprint
- Completing a sprint moves all non-done issues back to the backlog

#### Issues
- Full CRUD: create, view, update, delete
- Fields: title, type, priority, status, description, assignee, sprint, story points
- Issue type: Epic · Story · Task · Bug · Subtask (with colour-coded icons)
- Issue priority: Lowest · Low · Medium · High · Highest (with colour-coded icons)
- Issue number auto-incremented per project (e.g. `NT-1`, `NT-2`)
- Inline editing on the issue detail page — click any field to edit, saves on blur
- Status, type, priority, assignee, and sprint are updated instantly via select dropdowns
- Delete with confirmation

#### Comments
- Add comments on the issue detail page
- Edit and delete your own comments inline

#### Navigation
- **Projects** link added to the app sidebar for the current team

---

### Database Schema

New tables (apply `database/sql/schema.sql` to MySQL after running `php artisan migrate`):

| Table | Description |
|---|---|
| `projects` | Scoped to team; unique `(team_id, key)` |
| `sprints` | Belong to project; status: `planned / active / completed` |
| `issues` | Belong to project + optional sprint; type/status/priority enums; auto `number` per project |
| `labels` | Belong to project (schema only, UI not yet wired) |
| `issue_label` | Pivot for issues ↔ labels |
| `comments` | Belong to issue + user |

---

### Changed Files

**Backend**
- `database/sql/schema.sql` — new
- `app/Models/Project.php` — new
- `app/Models/Issue.php` — new (auto-increments `number` in `creating` event)
- `app/Models/Sprint.php` — new
- `app/Models/Comment.php` — new
- `app/Models/Team.php` — added `projects()` HasMany relation
- `app/Http/Controllers/Projects/ProjectController.php` — new
- `app/Http/Controllers/Projects/BoardController.php` — new
- `app/Http/Controllers/Projects/BacklogController.php` — new
- `app/Http/Controllers/Issues/IssueController.php` — new
- `app/Http/Controllers/Issues/CommentController.php` — new
- `app/Http/Controllers/Sprints/SprintController.php` — new
- `routes/web.php` — added all project/issue/sprint/comment routes

**Frontend**
- `resources/js/types/projects.ts` — new (Project, Sprint, Issue, Comment, BoardColumns types)
- `resources/js/types/index.ts` — added projects re-export
- `resources/js/pages/projects/index.tsx` — new
- `resources/js/pages/projects/board.tsx` — new
- `resources/js/pages/projects/backlog.tsx` — new
- `resources/js/pages/projects/issue.tsx` — new
- `resources/js/components/project/create-project-modal.tsx` — new
- `resources/js/components/project/issue-modal.tsx` — new
- `resources/js/components/project/issue-card.tsx` — new
- `resources/js/components/project/issue-type-icon.tsx` — new
- `resources/js/components/project/priority-icon.tsx` — new
- `resources/js/components/project/kanban-board.tsx` — new (with HTML5 drag-and-drop)
- `resources/js/components/project/sprint-section.tsx` — new
- `resources/js/components/app-sidebar.tsx` — added Projects nav item

---

### Known Limitations / Not Yet Implemented

- Labels UI (schema and model exist, picker not yet wired)
- Epic roadmap / hierarchy view
- Issue ordering within a column (drag-to-reorder)
- File attachments
- Activity log / audit trail
- Notifications
- Wayfinder route files not regenerated (run `php artisan wayfinder:generate` after setup)
