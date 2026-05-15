# TypingContest Enterprise Backend Architecture (Laravel)

## 1. Scope and Design Goals

This document defines a production-ready, enterprise-level backend architecture for TypingContest as a modular monolith with event-driven and real-time capabilities.

Primary goals:
- Support 10,000+ concurrent users in live contests.
- Keep leaderboard updates under 200 ms with Redis-first reads.
- Separate concerns with Service + Repository + Event-driven design.
- Keep API contracts stable with versioning (`/api/v1`).
- Enforce security using Sanctum/JWT hybrid auth and RBAC.

## 2. High-Level Architecture

### 2.1 Runtime Components

- API Layer: Laravel HTTP controllers, request validation, resources.
- Domain Layer: Service classes per module.
- Data Layer: Repository interfaces + Eloquent implementations.
- Persistence: MySQL for source-of-truth records.
- Caching and Realtime: Redis (cache, sorted sets, pub/sub, queues).
- Background Processing: Laravel queues + Horizon.
- Event Bus: Laravel events/listeners.
- Broadcasting: Laravel Broadcasting + WebSocket server (Soketi/Pusher-compatible).
- Observability: Telescope, Horizon metrics, structured app logs.

### 2.2 Modular Monolith Boundaries

Each module is isolated by:
- `Http/Controllers/API/<Module>`
- `Services/<Module>`
- `Repositories/<Module>`
- `Events/<Module>` and `Listeners/<Module>`
- `Jobs/<Module>`

Cross-module communication should happen via events and explicit service orchestration, not direct controller-to-controller calls.

## 3. Full Module Architecture

### 3.1 Authentication Module

Responsibilities:
- Register/login/logout.
- Email verification.
- Forgot/reset password.
- Social login.
- Sanctum + JWT token issuance.
- 2FA-ready design (placeholder table/service).

Core classes:
- `AuthService`
- `SocialProviderService`
- `UserRepositoryInterface`
- `LoginActivityRepositoryInterface`

Events:
- `UserLoggedIn`, `UserRegistered`, `PasswordResetRequested`, `PasswordResetCompleted`

Security controls:
- `auth-login` rate limiter.
- Device/IP login tracking.

### 3.2 User Profile Module

Responsibilities:
- Profile CRUD.
- Avatar/cover upload.
- Bio, country, social links.
- Aggregated typing stats.
- Badge/rank snapshots.
- Match history and activity feed.

Core classes:
- `ProfileService`
- `AvatarService`
- `StatisticRepositoryInterface`
- `ActivityRepositoryInterface`

Events:
- `ProfileUpdated`, `AvatarUploaded`

### 3.3 Contest Module

Responsibilities:
- Contest lifecycle (draft, published, active, finished, cancelled).
- Types: daily/weekly/monthly/tournament/special.
- Auto scheduling/start/stop.
- Join/leave and participation checks.
- Contest history and publishing controls.

Core classes:
- `ContestService`
- `ContestRepositoryInterface`
- `ParticipantRepositoryInterface`

Events:
- `ContestScheduled`, `ContestStarted`, `ContestEnded`, `ParticipantJoined`

Jobs:
- `StartScheduledContestJob`, `EndScheduledContestJob`, `RecalculateRankings`

### 3.4 Typing Engine Module

Responsibilities:
- Real-time typing validation.
- WPM/CPM/accuracy/error metrics.
- Anti-cheat checks per update.
- Auto-submit on timeout.
- Session status and history.

Core classes:
- `TypingEngineService`
- `TypingMetricService`
- `TypingAntiCheatService`
- `TypingRealtimeService`

Events:
- `TypingStarted`, `TypingUpdate`, `TypingProgress`, `TypingError`, `TypingFinished`

### 3.5 Live Competition Module

Responsibilities:
- Broadcast progress and rank changes.
- Track online participants.
- Provide rank movement fields for animation.

Realtime channels:
- `typing.session.{id}`
- `typing.leaderboard.{contestId}`
- `leaderboard.contest.{id}`

### 3.6 Leaderboard Module

Responsibilities:
- Global/country/daily/weekly/monthly/contest leaderboards.
- Top-10 payload.
- Tie-breaking: score desc, accuracy desc, errors asc, completion_time asc.
- Redis sorted-set acceleration and DB persistence.

Core classes:
- `Services/Leaderboard/LeaderboardService` (canonical)
- `Repositories/Leaderboard/LeaderboardRepositoryInterface`
- `EloquentLeaderboardRepository`

Jobs:
- `UpdateLeaderboardRankingsJob`

Events:
- `LeaderboardUpdated`

### 3.7 XP and Rewards Module

Responsibilities:
- XP gain from typed sessions.
- Level progression.
- Daily streak updates.
- Reward bonuses and triggers.

Core classes:
- `XpService`, `StreakService`, `RewardService`

Events:
- `XpAwarded`, `LevelUp`, `StreakUpdated`

### 3.8 Badge Module

Responsibilities:
- Badge catalog management.
- Unlock logic by milestones.
- Seasonal and premium badge policies.

Core classes:
- `BadgeService`
- `BadgeRuleEngineService`

Events:
- `BadgeUnlocked`

### 3.9 Subscription Module

Responsibilities:
- Plan management (Free/Pro/VIP).
- Lifecycle (activate/renew/upgrade/downgrade/cancel/expire).
- Access gating middleware.

Core classes:
- `SubscriptionService`
- `SubscriptionAdminService`

Events:
- `SubscriptionActivated`, `SubscriptionUpgraded`, `SubscriptionExpired`

### 3.10 Payment Module

Responsibilities:
- Gateway adapters (bKash, Nagad, SSLCommerz).
- Transaction lifecycle and webhook verification.
- Invoice generation.
- Refund workflows.

Core classes:
- `PaymentService`
- `PaymentAdminService`
- `PaymentGatewayResolver`
- `Gateways/*GatewayService`
- `InvoicePdfService`

Events:
- `PaymentCreated`, `PaymentConfirmed`, `PaymentFailed`, `PaymentRefunded`

### 3.11 Notification Module

Responsibilities:
- In-app, email, and push notifications.
- Contest alerts/rank/subscription updates.

Core classes:
- `NotificationService`

Events:
- `NotificationQueued`, `AdminNotificationBroadcasted`

Jobs:
- `SendEmailNotificationJob`, `SendPushNotificationJob`

### 3.12 Social Module

Responsibilities:
- Follow/unfollow.
- Friend requests.
- Public profiles.
- Activity feed and achievement sharing.

Core classes:
- `SocialGraphService`
- `FeedService`

### 3.13 Admin Panel Module

Responsibilities:
- User moderation.
- Contest operations.
- Payment and subscription controls.
- Security monitoring.
- CMS management.
- Reports and exports.

Core classes:
- Existing `Admin*Service` set (dashboard, users, contests, security, reports, cms, etc.)

### 3.14 Analytics Module

Responsibilities:
- WPM/accuracy trends.
- Revenue analytics.
- User growth and retention.
- Contest performance dashboards.

Core classes:
- `AnalyticsService`
- Aggregation jobs for daily snapshots.

### 3.15 Anti-Cheat Module

Responsibilities:
- Paste detection.
- Tab switch tracking.
- Fingerprint and IP heuristics.
- Suspicious behavior scoring.

Core classes:
- `TypingAntiCheatService`
- `AntiCheatScoringService`

Events:
- `CheatingDetected`

### 3.16 CMS Module

Responsibilities:
- Static pages.
- Blog posts.
- FAQ and legal content.
- Banner management.

Core classes:
- `AdminCmsService`

### 3.17 Real-Time Infrastructure Module

Responsibilities:
- Redis pub/sub and queues.
- Broadcasting config and channel authorization.
- Presence and online tracking.

Core classes:
- Realtime/event services + channel definitions in `routes/channels.php`.

## 4. Database Schema (ER Design)

## 4.1 Core Tables

- users
- user_profiles
- contests
- contest_rules
- contest_sessions
- contest_participants
- typing_sessions
- typing_results
- typing_inputs
- typing_progress_logs
- typing_errors

## 4.2 Ranking and Rewards Tables

- leaderboards
- rankings
- ranking_history
- contest_rankings
- user_scores
- badges
- user_badges
- country_rankings

## 4.3 Commerce Tables

- subscription_plans
- user_subscriptions
- subscriptions (legacy compatibility if needed)
- payments
- payment_transactions
- invoices
- coupon_codes

## 4.4 Engagement and Ops Tables

- notifications
- user_activities
- support_tickets
- support_ticket_messages
- cms_pages
- cms_banners
- login_activities
- security_blocks
- contest_anti_cheat_logs

## 4.5 Key Relationships

- users 1:n typing_sessions, typing_results, payments, notifications, user_badges, user_subscriptions.
- contests 1:n contest_participants, contest_sessions, typing_sessions, typing_results, contest_rankings.
- typing_sessions 1:1 typing_results, 1:n typing_inputs, typing_progress_logs, typing_errors.
- users n:n badges via user_badges.
- users 1:n leaderboards/rankings/user_scores.
- user_subscriptions 1:n payments.
- payments 1:n payment_transactions and 1:1 invoice.

## 5. Laravel Folder Structure

```
app/
  Http/
    Controllers/API/
      Auth/
      Profile/
      Contest/
      Typing/
      Leaderboard/
      Subscription/
      Payment/
      Admin/
    Requests/
    Resources/
  Services/
    Auth/
    Profile/
    Contest/
    Typing/
    Leaderboard/
    Subscription/
    Payment/
    Admin/
    Analytics/
    Rewards/
    Social/
  Repositories/
    Auth/
    Profile/
    Contest/
    Typing/
    Leaderboard/
    Subscription/
    Payment/
    Admin/
  Events/
    Auth/
    Profile/
    Contest/
    Typing/
    Leaderboard/
    Payment/
    Subscription/
    Admin/
  Listeners/
    Auth/
    Profile/
    Contest/
    Typing/
    Leaderboard/
    Payment/
    Subscription/
  Jobs/
    Leaderboard/
    Contest/
    Typing/
    Payment/
    Analytics/
  Models/
routes/
  api.php
  channels.php
```

## 6. Service and Controller Structure

### 6.1 Service Rules

- Controllers should not contain business logic.
- Services orchestrate validation outcomes, repositories, events, and jobs.
- Repositories encapsulate query logic and persistence details.

### 6.2 Response Contract

Success:
```json
{
  "success": true,
  "message": "Leaderboard fetched successfully",
  "data": {}
}
```

Error:
```json
{
  "success": false,
  "message": "No leaderboard data found"
}
```

## 7. Event and Listener System

Recommended event flow for typing submit:
1. `TypingFinished` emitted by typing engine.
2. Listener dispatches `UpdateLeaderboardRankingsJob`.
3. Job calculates/upserts score aggregates.
4. Service updates Redis sorted sets.
5. Service emits `LeaderboardUpdated`.
6. WebSocket clients consume rank updates.

Other key event flows:
- Payment confirmed -> activate/upgrade subscription -> notify user.
- Badge unlocked -> user notification + activity feed entry.
- Contest started/ended -> contest alerts + admin monitoring events.

## 8. WebSocket and Broadcasting Design

Public and private channels:
- `leaderboard.global`
- `leaderboard.country.{code}`
- `leaderboard.daily`
- `leaderboard.weekly`
- `leaderboard.monthly`
- `leaderboard.contest.{id}`
- `typing.session.{id}`
- `typing.leaderboard.{contestId}`
- `admin.live.contest.{id}`

Event payload recommendations:
- Include `rank`, `previous_rank`, `rank_movement`.
- Include `score`, `wpm`, `accuracy`, `errors`.
- Include `updated_at` and leaderboard scope metadata.

## 9. Queue Job Design

Queues:
- `leaderboard`: rank updates, recalculations.
- `payments`: verification, reconciliation, refunds.
- `notifications`: email/push dispatch.
- `analytics`: snapshot aggregation.
- `default`: fallback jobs.

Important jobs:
- `UpdateLeaderboardRankingsJob`
- `RecalculateRankings`
- `StartScheduledContestJob`
- `EndScheduledContestJob`
- `AutoSubmitTypingSession`
- `GenerateAnalyticsSnapshotJob`
- `SendSubscriptionExpiryReminderJob`

Queue design notes:
- Idempotent job handlers.
- Retry policy with exponential backoff.
- Dead-letter queue for persistent failures.

## 10. REST API Endpoint List (v1)

### 10.1 Auth
- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `GET /api/v1/auth/me`
- `POST /api/v1/auth/forgot-password`
- `POST /api/v1/auth/reset-password`
- `POST /api/v1/auth/email/verify`
- `POST /api/v1/auth/social/google`
- `POST /api/v1/auth/social/github`

### 10.2 Profile
- `GET /api/v1/profile`
- `PUT /api/v1/profile/update`
- `POST /api/v1/profile/avatar`
- `GET /api/v1/profile/stats`
- `GET /api/v1/profile/badges`
- `GET /api/v1/profile/matches`
- `GET /api/v1/profile/activity`
- `GET /api/v1/profile/{username}`

### 10.3 Contest
- `GET /api/v1/contests`
- `GET /api/v1/contests/{contest}`
- `POST /api/v1/contests/{contest}/join`
- `GET /api/v1/contests/{contest}/typing-text`
- `POST /api/v1/contests/{contest}/submit`
- `GET /api/v1/contests/{contest}/result`
- `GET /api/v1/contests/{contest}/leaderboard`

### 10.4 Typing
- `POST /api/v1/typing/start`
- `POST /api/v1/typing/update`
- `POST /api/v1/typing/submit`
- `GET /api/v1/typing/status/{session_id}`
- `GET /api/v1/typing/result/{id}`
- `GET /api/v1/typing/history`

### 10.5 Leaderboard
- `GET /api/v1/leaderboard/global`
- `GET /api/v1/leaderboard/country/{code}`
- `GET /api/v1/leaderboard/daily`
- `GET /api/v1/leaderboard/weekly`
- `GET /api/v1/leaderboard/monthly`
- `GET /api/v1/leaderboard/contest/{id}`
- `GET /api/v1/leaderboard/top-10`

### 10.6 Subscription and Payment
- `GET /api/v1/plans`
- `POST /api/v1/subscription/subscribe`
- `POST /api/v1/subscription/upgrade`
- `GET /api/v1/subscription/current`
- `POST /api/v1/subscription/cancel`
- `POST /api/v1/payment/create`
- `POST /api/v1/payment/verify`
- `POST /api/v1/payment/webhook`
- `GET /api/v1/payment/history`

### 10.7 Admin
- Full `/api/v1/admin/*` suite for users, contests, subscriptions, payments, leaderboard control, reports, security, support, cms, notifications, system monitoring, roles.

## 11. Redis Design

Key families:
- `leaderboard:global:all-time:all`
- `leaderboard:daily:{yyyy-mm-dd}:all`
- `leaderboard:weekly:{iso-week}:all`
- `leaderboard:monthly:{yyyy-mm}:all`
- `leaderboard:country:all-time:{country}`
- `leaderboard:contest:{contestId}`
- `typing:progress:{sessionId}`
- `online:contest:{contestId}`

Data structures:
- Sorted Sets for ranks.
- Hashes for session snapshots.
- Sets for online participant IDs.
- Streams (optional) for audit/event replay.

## 12. Security Architecture

- Auth: Sanctum for SPA/API tokens, JWT for cross-client workflows.
- Authorization: Spatie roles and permissions + module middleware.
- Input safety: FormRequest validation for all writes.
- Rate limiting: auth, typing updates, payment webhooks, admin APIs.
- Anti-cheat: suspicious scoring with IP/fingerprint correlation.
- Duplicate submission prevention: unique constraints + session status checks.
- API hardening: strict CORS, secure headers, signed webhooks, idempotency keys.

## 13. Performance and Scalability Plan

### 13.1 Performance Targets

- Leaderboard read path: Redis-first, DB fallback.
- Typing update path: minimal synchronous writes + async aggregation.
- Sub-200ms leaderboard update after score submit via queued update and broadcast.

### 13.2 Scaling Strategy

- Horizontal API scaling (stateless app servers).
- Dedicated Redis for cache + queue separation.
- MySQL read replica for analytics/reporting.
- Horizon worker pools by queue priority.
- CDN and object storage for avatar/static assets.

### 13.3 Data Lifecycle

- Hot data in Redis with short TTL.
- Durable aggregate data in MySQL.
- Scheduled cleanup for noisy typing logs and old event traces.

## 14. Deployment and Operations

- Use Horizon for queue monitoring and balancing.
- Use Telescope in non-production or protected internal access.
- Health checks: DB, Redis, queue backlog, websocket connection health.
- Alerting: payment failures, queue lag, leaderboard update latency, suspicious cheat spikes.
- Blue/green or rolling deployment with zero-downtime migrations.

## 15. Optional Advanced Features

- Elo rating and bracketed tournaments.
- Seasonal reset and season archives.
- Rank decay for inactivity.
- Fraud ML model and anomaly pipeline.
- Regional shards for very large traffic.

## 16. Implementation Phases

Phase 1:
- Core auth, contests, typing engine, leaderboard, profile.

Phase 2:
- Subscription/payment, notifications, admin controls, analytics snapshots.

Phase 3:
- Social graph, advanced anti-cheat, seasonal systems, elo/tournament features.

---

This architecture is aligned with a production Laravel modular monolith and your current project structure, while remaining extensible toward a full SaaS typing esports platform.
