# Release Notes

## v0.6.0 — No Ticket

**Branch:** `claude-vibe`
**Date:** 2026-05-08

---

### Overview

Issue activity history with field-level diffing, smart checklist change tracking, and UI polish on the issue detail page.

---

### New Features

#### Issue History
- Every issue now has a full activity log tracking `created`, `updated`, and `deleted` events
- Stored in a new `issue_histories` table: `issue_id`, `user_id`, `action`, `field`, `old_value`, `new_value`, `created_at`
- **Created** — recorded on `store`, no values
- **Updated** — one entry per changed field; human-readable labels stored at write time (enum labels, user/sprint names resolved immediately)
- **Deleted** — recorded before soft-delete
- Displayed as a collapsible **History** section on the issue detail page (collapsed by default, shows entry count in badge)
- Each entry shows: user avatar initial, user name, action sentence, timestamp
- Changed values shown as **red pill (old) → green pill (new)**; pills only rendered when the value is non-null — no pill shown for the absent side

#### Field-Level Diff Display
- Short values shown in full; values over 50 chars run through a word-level diff that finds the first/last differing word and shows only that region ± 4 words of context with `…` markers
- Enum fields (status, type, priority) stored as human-readable labels — e.g. `"To Do" → "In Progress"`
- Assignee and sprint stored by name at write time, not by ID

#### Smart Checklist History
- Checklist patches are diffed item-by-item to detect the exact operation:
  - **Added** — only green pill shown with the new item text
  - **Removed** — only red strikethrough pill shown with the removed item text
  - **Checked** — red `"☐ text"` → green `"☑ text"`
  - **Unchecked** — red `"☑ text"` → green `"☐ text"`
  - **Renamed** — red old text → green new text
  - **Multiple changes** — falls back to summary: `"3 items (1 done)"` → `"4 items (2 done)"`

#### Checklist Edit via Pencil Icon
- Double-click to edit replaced with an explicit **Pencil icon** that appears on hover
- Applied in both `IssueChecklist` (issue detail page) and the inline checklist in `IssueModal`
- Icon hidden when item is done or already in edit mode

#### Created / Updated Timestamps on Hover
- Issue detail sidebar always shows the **date** for Created and Updated
- **Time** fades in when hovering over the meta block — no layout shift (text rendered transparent, transitions to visible)

---

### Database Changes

- New migration: `2026_05_08_162608_create_issue_histories_table` — `issue_histories` with foreign keys to `issues` and `users`, cascade delete, no `updated_at`

---

### Changed Files

**Backend**
- `database/migrations/2026_05_08_162608_create_issue_histories_table.php` — new
- `app/Models/IssueHistory.php` — new (`$timestamps = false`, `created_at` cast, relations to `Issue` and `User`)
- `app/Models/Issue.php` — added `histories()` HasMany relation
- `app/Http/Controllers/Issues/IssueController.php` — history recording in `store`, `update`, `destroy`; `buildUpdateHistories` field diff; `checklistDiff` + `checklistSummary` helpers; `histories` loaded and mapped in `show`

**Frontend**
- `resources/js/types/projects.ts` — added `IssueHistory` type; added `histories` to `IssueDetail`
- `resources/js/pages/projects/issue.tsx` — `HistoryFeed` component (collapsible, old→new pills, conditional pill rendering); `historyActionText`, `displayValue`, `wordDiffExcerpt` helpers; created/updated timestamps with hover-reveal time
- `resources/js/components/project/issue-checklist.tsx` — pencil icon edit trigger; removed double-click
- `resources/js/components/project/issue-modal.tsx` — pencil icon edit trigger; removed double-click

---

## v0.5.0 — No Ticket

**Branch:** `claude-vibe`
**Date:** 2026-05-08

---

### Overview

Themes expanded with Green and Dimmed variants, dynamic per-theme favicons, a full visual redesign of all inner pages, smart board/backlog navigation memory, and UX improvements to issue creation — including a checklist builder, "Assign to me" shortcut, and a responsive bottom-sheet modal on mobile.

---

### New Features

#### Green & Dimmed Themes
- Added **Green Light**, **Green Dark**, **Green Dim** — full CSS variable sets with green-tinted palettes
- Added **Brown Dim**, **Blue Dim**, **Dark Dim** — lower-contrast dimmed variants of existing dark themes
- `Appearance` type extended with `'green-light' | 'green-dark' | 'green-dim' | 'brown-dim' | 'blue-dim' | 'dark-dim'`
- Appearance settings page updated with new theme tabs and icons
- All new themes get matching favicon sets

#### Dynamic Per-Theme Favicons
- Favicon switches automatically when the user changes their theme
- `use-appearance.tsx` now calls `updateFavicon(theme)` after applying a theme class
- Each theme has its own `.svg`, `.ico`, `.png` (32px), and Apple touch icon asset
- New theme assets: `brown`, `blue`, `azure`, `light`, `green-light`, `green-dark`, `green-dim`, `brown-dim`, `blue-dim`, `dark-dim`, `dark-dim`
- `app.blade.php` updated to serve the correct default favicon on first load

#### Inner Page Visual Redesign
- All inner pages received a major visual pass:
  - **Dashboard** — gradient orb backgrounds, dot-grid texture, stat card polish
  - **Projects index** — dot-grid + orb background, card hover effects
  - **Board** — dot-grid background, sprint info bar refinements
  - **Backlog** — dot-grid background, sprint section polish
  - **Issue detail** — full layout rework; two-column sidebar, better typography hierarchy
  - **Appearance settings** — redesigned theme picker with live preview swatches

#### Project View Memory
- Projects remember whether each user last visited the **Board** or **Backlog**
- Stored in `localStorage` as `noticket_view_<projectId>`
- Project cards on the index page link directly to the remembered view
- `ProjectController@index` redirects to the stored view instead of always defaulting to board

#### Custom Sidebar Logo
- Sidebar now shows a custom "No Ticket" SVG icon (ticket with a slash) instead of a generic icon
- `app-logo-icon.tsx` rewritten with inline SVG; `app-logo.tsx` simplified

#### Create Issue Button on Board
- "Create Issue" button added to the board page header when an active sprint exists
- Opens `IssueModal` with `todo` status pre-selected and the active sprint pre-filled

#### "Assign to Me" Shortcut
- Appears next to the **Assignee** label in the create/edit modal and on the issue detail sidebar
- Only shown when the current user is a project member and is not already the assignee
- In the modal: sets the select value; on the issue detail page: fires an immediate `PATCH`

#### Checklist in Create/Edit Modal
- Checklist builder added to `IssueModal` (create and edit views)
- Add items by typing and pressing Enter or clicking `+`
- Toggle done state, delete on hover
- **Double-click to edit** any unchecked item inline — Enter/blur commits, Escape cancels
- Checklist is submitted with the issue payload on create and save
- Backend `store` validation updated to accept `checklist` array (same rules as `update`)
- New migration: `checklist JSON NULL` added to `issues` table

#### Smart Back Navigation on Issue Page
- "Back" link on the issue detail page now reads `noticket_view_<projectId>` from localStorage
- Shows **"Back to Board"** or **"Back to Backlog"** depending on where the user came from
- Post-delete redirect follows the same logic

#### Responsive Issue Modal
- `DialogContent` (shared) updated to render as a **bottom sheet on mobile** and a centered modal on `sm+`
- Mobile: slides up from the bottom, `max-h-[90dvh]` with internal scroll, rounded top corners
- Desktop: zoom-in animation, `max-h-[90vh]` scroll, all corners rounded
- Form field grids in `IssueModal` stack to 1 column on mobile (`grid-cols-1 sm:grid-cols-2`)

---

### Bug Fixes

- `crypto.randomUUID()` unavailable on HTTP (Laragon local dev) — replaced with `crypto.randomUUID?.() ?? timestamp+random` fallback in `issue-modal.tsx` and `issue-checklist.tsx`

---

### Database Changes

- New migration: `2026_05_08_add_checklist_to_issues_table` — adds `checklist JSON NULL` after `description`

---

### Changed Files

**Backend**
- `app/Http/Controllers/Projects/ProjectController.php` — index redirects to remembered view
- `app/Http/Controllers/Issues/IssueController.php` — `store` now validates `checklist` array
- `database/migrations/2026_05_08_161058_add_checklist_to_issues_table.php` — new

**Frontend**
- `resources/css/app.css` — added Green Light/Dark/Dim and Dimmed variant theme CSS variable sets
- `resources/js/hooks/use-appearance.tsx` — extended `Appearance` type; `updateFavicon` per theme; handles all new variants
- `resources/js/pages/settings/appearance.tsx` — new theme tabs (Green variants, Dimmed variants)
- `resources/js/pages/dashboard.tsx` — visual redesign
- `resources/js/pages/projects/index.tsx` — visual redesign; project card links to remembered view
- `resources/js/pages/projects/board.tsx` — visual redesign; localStorage view tracking; "Create Issue" button + `IssueModal`
- `resources/js/pages/projects/backlog.tsx` — visual redesign; localStorage view tracking
- `resources/js/pages/projects/issue.tsx` — full layout rework; "Assign to me"; smart back navigation; edit checklist items
- `resources/js/components/app-logo-icon.tsx` — custom "No Ticket" SVG icon
- `resources/js/components/app-logo.tsx` — simplified
- `resources/js/components/project/issue-modal.tsx` — checklist builder; "Assign to me"; responsive grids; edit checklist items
- `resources/js/components/project/issue-checklist.tsx` — double-click inline edit; `crypto.randomUUID` fallback
- `resources/js/components/ui/dialog.tsx` — responsive bottom sheet on mobile
- `resources/views/app.blade.php` — per-theme favicon on first load
- `public/favicons/` — new favicon assets for all themes (SVG, ICO, 32px PNG, Apple touch)

---

## v0.4.0 — No Ticket

**Branch:** `claude-vibe`
**Date:** 2026-05-07

---

### Overview

Three new color themes, a redesigned landing page with a live Kanban mockup, and branding fixes.

---

### New Features

#### Color Themes
- Added **Brown** — dark warm brown palette (Coffee icon)
- Added **Blue** — dark deep blue with neon glow effects (Sparkles icon)
- Added **Azure** — light blue theme with polished elevation effects (Gem icon)
- Brown and Blue are treated as dark-mode variants; Azure is light-mode
- Theme-specific CSS enhancements per theme:
  - **Azure** — smooth transitions on interactive elements, blue focus-ring glow, primary button shadow/glow, card elevation with inset highlight, sidebar active glow
  - **Blue** — transitions on interactive elements, neon focus-ring glow, primary button neon glow, card depth with edge highlight, sidebar active glow
- `Appearance` type extended: `'light' | 'dark' | 'system' | 'brown' | 'blue' | 'azure'`
- `applyTheme` toggles `.theme-brown`, `.theme-blue`, `.theme-azure` CSS classes on `<html>`

#### Welcome Page Redesign
- Full rewrite of the landing page (`welcome.tsx`)
- Interactive Kanban board mockup showing a simulated board with 4 columns (To Do / In Progress / In Review / Done)
- Mock issue cards with type badges, priority colours, and avatar initials
- Browser chrome frame around the mockup for realism

---

### Branding Fixes

- App sidebar logo label changed from "Laravel Starter Kit" → "No Ticket"
- `.env.example` `APP_NAME` updated from `Laravel` → `"No Ticket"`
- README heading corrected from `NoTicket` → `No Ticket`

---

### Changed Files

**Frontend**
- `resources/css/app.css` — added `.theme-brown`, `.theme-blue`, `.theme-azure` with full CSS variable sets and theme-specific effect rules
- `resources/js/hooks/use-appearance.tsx` — extended `Appearance` type; `isDarkMode` handles brown/blue; `applyTheme` toggles theme classes
- `resources/js/components/appearance-tabs.tsx` — added Brown, Blue, Azure tabs with Coffee/Sparkles/Gem icons
- `resources/js/pages/welcome.tsx` — full rewrite with `KanbanMockup` component and feature highlights
- `resources/js/components/app-logo.tsx` — sidebar label updated to "No Ticket"

**Config**
- `.env.example` — `APP_NAME` corrected

---

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
