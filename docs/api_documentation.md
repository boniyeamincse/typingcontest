# TypingContest API Documentation (Planning Version)

Base URL:
- /api

Authentication:
- Token-based auth with Sanctum.
- Use Authorization: Bearer <token> for protected endpoints.

## 1. Auth

### POST /register
Purpose: Create new account.

Request:
- name: string
- email: string
- password: string
- password_confirmation: string

Response:
- user object
- access_token

### POST /login
Purpose: Authenticate existing account.

Request:
- email: string
- password: string

Response:
- user object
- access_token

### POST /logout
Purpose: Revoke current token.

Auth required: yes

## 2. Profile

### GET /profile
Purpose: Get current user profile and summary stats.

Auth required: yes

### PATCH /profile
Purpose: Update profile data.

Auth required: yes

Payload (example):
- name
- avatar_url
- bio

## 3. Contest

### GET /contests
Purpose: List available contests.

Query params:
- status: upcoming|active|completed
- type: daily|weekly|monthly
- page

### GET /contests/{id}
Purpose: Contest details.

### POST /contests/{id}/join
Purpose: Join contest.

Auth required: yes

### POST /contests/{id}/submit
Purpose: Submit final result packet.

Auth required: yes

Payload:
- wpm: number
- accuracy: number
- errors: number
- duration_ms: integer
- checksum: string

Validation:
- contest must be active
- user must be enrolled/eligible
- payload signatures and thresholds must pass anti-cheat checks

## 4. Leaderboard

### GET /leaderboard
Purpose: Global ranking list.

Query params:
- period: daily|weekly|monthly|all_time
- page

### GET /contests/{id}/leaderboard
Purpose: Ranking for one contest.

## 5. Subscription

### GET /plans
Purpose: List available plans.

### POST /subscriptions/checkout
Purpose: Start checkout session.

Auth required: yes

### POST /subscriptions/webhook
Purpose: Receive payment provider events.

Auth required: no (signature validated)

## 6. Admin
Admin endpoints must require admin role.

### POST /admin/contests
Purpose: Create contest.

### PATCH /admin/contests/{id}
Purpose: Update contest.

### POST /admin/contests/{id}/publish
Purpose: Publish contest.

### POST /admin/users/{id}/suspend
Purpose: Suspend user account.

### GET /admin/reports/suspicious
Purpose: Fetch anti-cheat review queue.

## 7. Error Model
Common error response:
- message: string
- errors: object (validation errors)
- code: optional machine-readable code

HTTP status conventions:
- 200/201 success
- 401 unauthorized
- 403 forbidden
- 404 not found
- 422 validation failed
- 429 rate limit
- 500 server error

## 8. Versioning
- Start with v1 route group if needed: /api/v1
- Breaking changes require new version.

## 9. Idempotency
- Submission and payment webhook endpoints should support idempotency keys.
- Duplicate requests must not create duplicate results or entitlements.
