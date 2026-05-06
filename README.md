# NoTicket

A Jira-like project management app built with **Laravel 13**, **React 19**, **Inertia.js**, **Tailwind CSS 4**, and **MySQL**.

---

## Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13, PHP 8.3 |
| Frontend | React 19, TypeScript |
| Bridge | Inertia.js 3 (no API layer) |
| Styling | Tailwind CSS 4, shadcn/ui |
| Auth | Laravel Fortify (email + 2FA) |
| Teams | Custom multi-team system |
| Routes | Laravel Wayfinder (type-safe) |
| Database | MySQL |

---

## Features

- **Multi-team workspaces** — invite members, assign roles (Owner / Admin / Member)
- **Projects** — create per team with a short key; scoped to team membership
- **Kanban board** — drag-and-drop between To Do / In Progress / In Review / Done
- **Backlog** — manage sprints and unassigned issues
- **Sprints** — create, start (one active at a time), complete (incomplete issues return to backlog)
- **Issues** — type, priority, status, assignee, sprint, story points; inline editing
- **Comments** — per issue; edit and delete your own
- **Dark mode** — system preference or manual toggle

---

## Local Setup

### Prerequisites

- PHP 8.3+
- Composer
- Node.js 20+ / npm
- MySQL 8+
- [Laragon](https://laragon.org/) or any local server

### Installation

```bash
# Clone / open the project
cd laragon/www/noticket

# PHP dependencies
composer install

# Node dependencies
npm install

# Environment
cp .env.example .env
php artisan key:generate
```

### Database

```bash
# Edit .env — set DB_DATABASE, DB_USERNAME, DB_PASSWORD

# Run Laravel migrations (users, teams, cache, jobs tables)
php artisan migrate

# Apply the Jira clone schema
php artisan db:import-schema

# Re-import (drops existing app tables first)
php artisan db:import-schema --fresh
```

### Development

```bash
# Start Laravel
php artisan serve

# Start Vite (HMR)
npm run dev
```

Visit `http://localhost:8000`, register an account, and you are ready to go.

### Build for Production

```bash
npm run build
php artisan optimize
```

---

## Project Structure

```
app/
  Http/
    Controllers/
      Issues/          # IssueController, CommentController
      Projects/        # ProjectController, BoardController, BacklogController
      Sprints/         # SprintController
      Teams/           # TeamController, TeamMemberController, TeamInvitationController
      Settings/        # ProfileController, SecurityController
  Models/
    Comment.php
    Issue.php
    Membership.php
    Project.php
    Sprint.php
    Team.php
    TeamInvitation.php
    User.php

database/
  migrations/          # Laravel standard migrations
  sql/
    schema.sql         # Jira clone tables (run after migrate)

resources/js/
  components/
    project/           # IssueCard, IssueModal, KanbanBoard, SprintSection, …
    ui/                # shadcn/ui primitives
  pages/
    auth/              # Login, Register, 2FA, …
    projects/          # Index, Board, Backlog, Issue
    settings/          # Profile, Security, Appearance
    teams/             # Index, Edit
  types/
    projects.ts        # Project, Sprint, Issue, Comment, BoardColumns
    teams.ts           # Team, TeamMember, TeamInvitation
    auth.ts            # User, Auth

routes/
  web.php              # All routes
  settings.php         # Settings routes
```

---

## Database Schema

### Core Tables (from `database/sql/schema.sql`)

**`projects`**
```
id, team_id, created_by, name, key (unique per team), description, timestamps, deleted_at
```

**`sprints`**
```
id, project_id, name, goal, status (planned|active|completed), starts_at, ends_at, timestamps
```

**`issues`**
```
id, project_id, sprint_id, parent_id, reporter_id, assignee_id
number (auto per project), type (epic|story|task|bug|subtask)
status (todo|in_progress|in_review|done), priority (lowest|low|medium|high|highest)
title, description, story_points, board_order, backlog_order, timestamps, deleted_at
```

**`comments`**
```
id, issue_id, user_id, content, timestamps
```

**`labels`** + **`issue_label`** — defined in schema, UI not yet implemented.

---

## Routes

All project routes are scoped to `/{team}` and require auth + team membership.

| Method | URL | Action |
|---|---|---|
| GET | `/{team}/projects` | List projects |
| POST | `/{team}/projects` | Create project |
| GET | `/{team}/projects/{id}/board` | Kanban board |
| GET | `/{team}/projects/{id}/backlog` | Backlog view |
| PATCH | `/{team}/projects/{id}` | Update project |
| DELETE | `/{team}/projects/{id}` | Delete project |
| POST | `/{team}/projects/{id}/issues` | Create issue |
| GET | `/{team}/projects/{id}/issues/{issue}` | Issue detail |
| PATCH | `/{team}/projects/{id}/issues/{issue}` | Update issue |
| DELETE | `/{team}/projects/{id}/issues/{issue}` | Delete issue |
| POST | `/{team}/projects/{id}/sprints` | Create sprint |
| PATCH | `/{team}/projects/{id}/sprints/{sprint}` | Update sprint |
| POST | `/{team}/projects/{id}/sprints/{sprint}/start` | Start sprint |
| POST | `/{team}/projects/{id}/sprints/{sprint}/complete` | Complete sprint |
| DELETE | `/{team}/projects/{id}/sprints/{sprint}` | Delete sprint |
| POST | `/{team}/projects/{id}/issues/{issue}/comments` | Add comment |
| PATCH | `…/comments/{comment}` | Edit comment |
| DELETE | `…/comments/{comment}` | Delete comment |

---

## Development Notes

- **No API layer** — all data flows through Inertia page props; mutations use `router.patch/post/delete` directly
- **Wayfinder** generates type-safe route helpers automatically; run `php artisan wayfinder:generate` after adding new named routes
- **Drag-and-drop** uses the browser's native HTML5 Drag and Drop API (no extra library)
- **Optimistic UI** on the kanban board — status updates appear immediately and sync from server props via `useEffect`
- **Issue numbers** are auto-incremented per project in the Eloquent `creating` event (not a DB sequence)
- **Soft deletes** on projects and issues — data is retained and can be restored

---

## Changelog

See [RELEASE.md](RELEASE.md) for the full changelog.
