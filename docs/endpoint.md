# TypingContest Endpoint Reference

This document reflects the currently implemented Laravel API routes.

## Base URL

Local API base URL:

```text
http://127.0.0.1:8001/api/v1
```

## Authentication

The API uses JWT bearer tokens.

Protected requests must include:

```http
Authorization: Bearer <jwt_token>
Accept: application/json
```

For `POST` and `PUT` requests, also send:

```http
Content-Type: application/json
```

## API Conventions

### Route Parameters

- `{contest}` uses Laravel route-model binding and expects a contest ID.
- `{username}` expects the user's unique username.

### Common Enums

- Contest `type`: `daily`, `weekly`, `monthly`, `special`
- User `plan_type`: `free`, `pro`
- Leaderboard `type`: `global`, `daily`, `weekly`, `monthly`, `country`

### Pagination

The following endpoints return Laravel paginator JSON objects instead of plain arrays:

- `GET /contests`
- `GET /leaderboard`
- `GET /admin/contests`

Typical paginator fields include:

- `current_page`
- `data`
- `per_page`
- `total`
- `last_page`

### Common Error Responses

Typical error status codes used by the current API:

- `401 Unauthorized`: missing or invalid JWT token, or invalid login credentials
- `403 Forbidden`: authenticated user is not allowed to access the resource
- `404 Not Found`: route-model binding failed or requested record was not found
- `422 Unprocessable Entity`: validation failed
- `500 Internal Server Error`: token generation or refresh failure

Typical validation error format:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email field is required."
    ]
  }
}
```

## Public Endpoints

### POST /auth/register
Create a new user account and return a JWT token.

Request body:

```json
{
  "username": "speedrunner",
  "name": "Speed Runner",
  "email": "speedrunner@example.com",
  "password": "Password123!",
  "password_confirmation": "Password123!",
  "country": "US"
}
```

Success response: `201 Created`

```json
{
  "message": "User registered successfully",
  "token": "<jwt_token>",
  "user": {
    "id": 1,
    "username": "speedrunner",
    "name": "Speed Runner",
    "email": "speedrunner@example.com",
    "country": "US"
  }
}
```

### POST /auth/login
Authenticate a user and return a JWT token.

Request body:

```json
{
  "email": "admin@example.com",
  "password": "Password123!"
}
```

Success response: `200 OK`

```json
{
  "message": "Login successful",
  "token": "<jwt_token>",
  "user": {
    "id": 1,
    "username": "admin",
    "name": "Admin User",
    "email": "admin@example.com",
    "country": "US",
    "plan_type": "pro"
  }
}
```

Failure response: `401 Unauthorized`

```json
{
  "error": "Unauthorized"
}
```

### GET /contests
List published and active contests.

Optional query parameters:
- `type`: `daily`, `weekly`, `monthly`, `special`

Response shape:
- paginated Laravel resource payload
- sorted by contest status first, then `start_time`

### GET /contests/{contest}
Get contest details, participant count, and linked typing text metadata.

Notes:
- returns `404` when the contest is not publicly visible
- current public visibility states are `published`, `active`, and `finished`

### GET /contests/{contest}/leaderboard
Get the top 50 results for a contest.

### GET /leaderboard
Get the global leaderboard.

Optional query parameters:
- `page`: integer

Notes:
- uses leaderboard rows where `type = global`
- uses `period_key = all-time`

### GET /leaderboard/daily
Get the daily leaderboard.

### GET /leaderboard/weekly
Get the weekly leaderboard.

### GET /leaderboard/monthly
Get the monthly leaderboard.

### GET /leaderboard/country
Get country rankings.

## Protected Endpoints

These endpoints require a valid JWT token.

### GET /auth/me
Return the authenticated user profile.

Response fields include:
- `id`
- `username`
- `name`
- `email`
- `avatar`
- `country`
- `plan_type`
- `subscription_status`
- `subscription_end_date`
- `xp_points`
- `global_rank`
- `total_wpm`
- `accuracy_avg`

### POST /auth/logout
Invalidate the current JWT token.

### POST /auth/refresh
Refresh the current JWT token.

Success response includes a newly issued `token`.

### POST /contests/{contest}/join
Join a published or active contest.

Notes:
- Fails if the contest is closed.
- Fails if the user already joined.
- Fails if the user is banned.

Success response: `201 Created`

```json
{
  "message": "Joined contest successfully",
  "participant_id": 15
}
```

### GET /contests/{contest}/typing-text
Get the contest typing text for a joined user.

Notes:
- Only available when the contest status is `active`.
- The authenticated user must already have joined the contest.
- If the user did not join, the endpoint returns `404`.

### POST /contests/{contest}/submit
Submit a contest result.

Request body:

```json
{
  "wpm": 112,
  "accuracy": 97.5,
  "errors": 3,
  "keystroke_timings": []
}
```

Validation:
- `wpm`: integer, `0..500`
- `accuracy`: numeric, `0..100`
- `errors`: integer, `>= 0`
- `keystroke_timings`: optional array

Response:
- success message
- stored `result` payload

### GET /contests/{contest}/result
Get the authenticated user's result for a contest.

### GET /users/{username}
Get a public profile view for a user.

Response includes:
- basic user profile fields
- earned badges summary

### GET /profile/history
Get contest submission history for the authenticated user.

Behavior:
- `pro` users: up to 100 items
- `free` users: up to 10 items
- ordered by `submitted_at` descending

### GET /profile/stats
Get aggregated user statistics.

Response fields:
- `total_contests`
- `avg_wpm`
- `avg_accuracy`
- `best_score`
- `xp_points`
- `global_rank`

### GET /profile/badges
Get all earned badges for the authenticated user.

## Admin Endpoints

These endpoints require:
- valid JWT token
- `admin` role

### GET /admin/contests
List contests for admin management.

Optional query parameters:
- `status`

Response shape:
- paginated Laravel resource payload

### POST /admin/contests
Create a contest in `draft` status.

Request body:

```json
{
  "title": "Weekend Sprint",
  "type": "weekly",
  "typing_text_id": 2,
  "max_participants": 500,
  "prize_description": "Top 3 receive bonus XP",
  "start_time": "2026-05-20 10:00:00",
  "end_time": "2026-05-20 11:00:00"
}
```

### PUT /admin/contests/{contest}
Update contest details.

Allowed fields:
- `title`
- `type`
- `max_participants`
- `prize_description`
- `start_time`
- `end_time`

### DELETE /admin/contests/{contest}
Delete a contest.

Rule:
- only `draft` contests can be deleted

### POST /admin/contests/{contest}/publish
Publish a contest.

Response:
- success message
- updated contest payload

### POST /admin/contests/{contest}/cancel
Cancel a contest.

Response:
- success message only

## Route Summary

Implemented route groups:
- Auth: 5 endpoints
- Public contests and leaderboards: 8 endpoints
- Protected contest and profile endpoints: 8 endpoints
- Admin contest management: 6 endpoints

Total documented routes: 27

## Notes

- Current API version prefix is `/api/v1`.
- This file is intended to reflect the implemented routes in `backend/routes/api.php`.
- The older `api_documentation.md` file still contains planning-era endpoints and auth details that differ from the current implementation.

## Recommended Next Endpoints

These are not implemented yet, but they are high-value additions for a production-ready project:

- `POST /auth/forgot-password` for password reset initiation
- `POST /auth/reset-password` for password reset completion
- `PATCH /profile` for user profile updates
- `GET /notifications` and `POST /notifications/{id}/read` for in-app notifications
- `GET /plans` and subscription checkout endpoints for billing flow
- `GET /health` or `GET /status` for deployment and monitoring checks

## Implementation Notes Found During Review

- The documentation above reflects the current route file and controller methods.
- There is a backend inconsistency around contest cancellation: the controller sets status to `cancelled`, while contest status enums in migrations currently show `draft`, `published`, `active`, `finished` or `completed` depending on migration version. This should be aligned in code and schema before relying on the cancel flow in production.
