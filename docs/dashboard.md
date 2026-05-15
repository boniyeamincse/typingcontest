# Dashboard Menu Structure

## Overview
This document defines the menu list for dashboard navigation by role: Admin and User.

## Admin Menu List

| Menu | Route | Description |
|------|-------|-------------|
| Dashboard Home | `/dashboard` | Main dashboard summary and quick actions. |
| Contest Management | `/admin/contests` | Create, publish, cancel, and delete contests. |
| Contest List | `/contests` | View all public contests and open contest details. |
| Contest Detail | `/contests/{id}` | Review contest info and leaderboard. |
| Logout | `/auth/logout` (action) | End admin session safely. |

## User Profile Menu List

| Menu | Route | Description |
|------|-------|-------------|
| Dashboard Home | `/dashboard` | Player overview, account status, and quick links. |
| My Profile | `/api/v1/auth/me` (data source) | Load user profile data for UI display. |
| Browse Contests | `/contests` | Discover available contests by type/status. |
| Contest Detail | `/contests/{id}` | View contest details and leaderboard. |
| Typing Arena | `/contests/{id}/play` | Join and play active contests. |
| Logout | `/auth/logout` (action) | End user session safely. |

## Suggested Navigation Grouping

### Core
- Dashboard
- Contests

### Role-Based
- Admin: Contest Management
- User: My Profile

### Session
- Logout

## Notes
- Admin menu entries should only be visible for users with admin role.
- User Profile should show at least name, email, and account status.
- Keep menu labels consistent across desktop and mobile layouts.
