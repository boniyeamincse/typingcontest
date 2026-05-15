# TypingContest — Worldwide Branding Contest Platform
## Software Task List
**Stack:** Laravel 12 API · React 19 + TypeScript Frontend · MySQL · Redis · Laravel Echo / Pusher · JWT Auth

---

## PHASE 1 — Project Setup & Infrastructure

### 1.1 Laravel Backend Bootstrap
- [x] 001 · Init Laravel 12 project, configure `.env` (DB, Redis, Mail, Queue)
- [x] 002 · Install packages: `laravel/sanctum`, `tymon/jwt-auth`, `laravel/horizon`, `laravel/telescope`, `spatie/laravel-permission`
- [x] 003 · Configure JWT auth guards and middleware
- [x] 004 · Configure Redis for cache, sessions, and queues
- [x] 005 · Set up Laravel Horizon for queue monitoring
- [x] 006 · Set up Laravel Telescope for local debugging
- [x] 007 · Configure CORS for React frontend origin
- [x] 008 · Set up API versioning prefix `/api/v1`
- [x] 009 · Set up Pusher / Laravel Echo Server for WebSocket broadcasting
- [x] 010 · Configure `.env` broadcast driver (Pusher or Soketi self-hosted)

### 1.2 React + TypeScript Frontend Bootstrap
- [ ] 011 · Init Vite + React 19 + TypeScript project
- [ ] 012 · Install packages: `axios`, `zustand`, `react-router-dom v7`, `tailwindcss`, `framer-motion`, `socket.io-client` / `laravel-echo`, `pusher-js`
- [ ] 013 · Configure Axios base URL and JWT interceptor (auto-attach token, refresh on 401)
- [ ] 014 · Configure Tailwind CSS with custom dark/neon gaming theme tokens
- [ ] 015 · Set up global Zustand store structure (auth, contest, leaderboard slices)
- [ ] 016 · Set up React Router v7 layout routes (Public, Auth, Dashboard, Admin)
- [ ] 017 · Configure ESLint + Prettier + Husky pre-commit hooks
- [ ] 018 · Configure environment variables (`VITE_API_URL`, `VITE_PUSHER_KEY`, etc.)

### 1.3 DevOps & CI/CD
- [ ] 019 · Set up Docker Compose (PHP-FPM, Nginx, MySQL, Redis, Soketi)
- [ ] 020 · Configure GitHub Actions CI pipeline (lint, test, build)
- [ ] 021 · Set up staging deployment workflow
- [ ] 022 · Configure production server (Nginx reverse proxy, SSL/TLS)

---

## PHASE 2 — Database Design & Migrations

- [ ] 023 · Migration: `users` — id, username, email, password, avatar, country, plan_type, subscription_status, subscription_end_date, xp_points, global_rank, total_wpm, accuracy_avg, is_banned, timestamps
- [ ] 024 · Migration: `roles` and `permissions` (via Spatie) — free, pro, admin
- [ ] 025 · Migration: `contests` — id, title, slug, type (daily/weekly/monthly/special), status (draft/published/active/finished), start_time, end_time, max_participants, prize_description, typing_text_id, created_by, timestamps
- [ ] 026 · Migration: `typing_texts` — id, content, language, word_count, difficulty, source_label, timestamps
- [ ] 027 · Migration: `contest_participants` — id, contest_id, user_id, wpm, accuracy, errors, score, rank, submitted_at, timestamps
- [ ] 028 · Migration: `leaderboards` — id, user_id, type (global/daily/weekly/monthly/country), period_key, rank, score, wpm, accuracy, timestamps
- [ ] 029 · Migration: `badges` — id, name, slug, icon_url, description, requirement_type, requirement_value, timestamps
- [ ] 030 · Migration: `user_badges` — id, user_id, badge_id, earned_at
- [ ] 031 · Migration: `subscriptions` — id, user_id, plan, amount, currency, payment_gateway, transaction_id, status, started_at, expires_at, timestamps
- [ ] 032 · Migration: `contest_anti_cheat_logs` — id, contest_id, user_id, event_type (tab_switch/paste_attempt/suspicious_wpm/ai_flag), metadata JSON, logged_at
- [ ] 033 · Migration: `notifications` — id, user_id, type, title, body, data JSON, read_at, timestamps
- [ ] 034 · Migration: `country_rankings` — id, country_code, country_name, total_score, avg_wpm, participant_count, rank, period_key
- [ ] 035 · Create Eloquent models with relationships for all tables
- [ ] 036 · Create database seeders (roles, permissions, badge definitions, sample typing texts)
- [ ] 037 · Create factories for users, contests, participants (for testing)

---

## PHASE 3 — Authentication System

### 3.1 Backend Auth API
- [ ] 038 · `POST /api/v1/auth/register` — validate, create user, assign free role, return JWT
- [ ] 039 · `POST /api/v1/auth/login` — validate credentials, return JWT + refresh token
- [ ] 040 · `POST /api/v1/auth/logout` — invalidate JWT
- [ ] 041 · `POST /api/v1/auth/refresh` — rotate JWT using refresh token
- [ ] 042 · `POST /api/v1/auth/forgot-password` — send reset link via mail queue
- [ ] 043 · `POST /api/v1/auth/reset-password` — validate token, update password
- [ ] 044 · `POST /api/v1/auth/google` — Google OAuth2 callback, upsert user, return JWT
- [ ] 045 · `GET /api/v1/auth/me` — return authenticated user profile
- [ ] 046 · Middleware: `jwt.auth`, `role:admin`, `plan:pro`
- [ ] 047 · Rate limiter on login/register (throttle: 5/min per IP)
- [ ] 048 · Email verification flow (send email, verify endpoint)

### 3.2 Frontend Auth
- [ ] 049 · Register page — form with username, email, password, country selector, real-time validation
- [ ] 050 · Login page — email/password form, Google OAuth button
- [ ] 051 · Forgot password / Reset password pages
- [ ] 052 · Email verification notice page
- [ ] 053 · Auth Zustand slice (token storage, user state, login/logout/refresh actions)
- [ ] 054 · `ProtectedRoute` HOC — redirect to login if unauthenticated
- [ ] 055 · `ProRoute` HOC — redirect/show upgrade modal if plan is free

---

## PHASE 4 — User Profile System

### 4.1 Backend Profile API
- [ ] 056 · `GET /api/v1/users/{username}` — public profile (stats, badges, rank)
- [ ] 057 · `PUT /api/v1/profile` — update avatar, bio, country, social links
- [ ] 058 · `GET /api/v1/profile/stats` — WPM history, accuracy trend, error heatmap data
- [ ] 059 · `GET /api/v1/profile/history` — paginated contest history (free: last 10, pro: unlimited)
- [ ] 060 · `GET /api/v1/profile/badges` — earned badges list
- [ ] 061 · Avatar upload to S3/local storage with image validation and resize
- [ ] 062 · Country flag integration using ISO 3166-1 alpha-2 codes

### 4.2 Frontend Profile
- [ ] 063 · Public profile page — avatar, country flag, global rank, WPM, accuracy, badges grid
- [ ] 064 · Settings page — update avatar, username, country, password tabs
- [ ] 065 · WPM trend chart (Recharts line chart) — pro gated
- [ ] 066 · Accuracy analytics chart — pro gated
- [ ] 067 · Error heatmap visualization — pro gated
- [ ] 068 · Typing replay viewer component — pro gated
- [ ] 069 · Detailed performance report table — pro gated
- [ ] 070 · Contest history list with pagination — free shows last 10, pro shows all
- [ ] 071 · Keyboard statistics panel — pro gated

---

## PHASE 5 — Contest Management System

### 5.1 Backend Contest API
- [ ] 072 · `GET /api/v1/contests` — list published contests (filter by type, status, upcoming/active/past)
- [ ] 073 · `GET /api/v1/contests/{id}` — single contest detail with participant count, time left
- [ ] 074 · `POST /api/v1/contests/{id}/join` — validate plan limits, create participant record, broadcast join event
- [ ] 075 · `GET /api/v1/contests/{id}/typing-text` — return text only when contest is active (prevent early access)
- [ ] 076 · `POST /api/v1/contests/{id}/submit` — validate submission window, calculate score, update participant record, trigger leaderboard update event
- [ ] 077 · `GET /api/v1/contests/{id}/result` — user's own result for finished contest
- [ ] 078 · Contest lifecycle job: `PublishContestJob` — auto-publish at start_time via scheduled command
- [ ] 079 · Contest lifecycle job: `FinalizeContestJob` — close submissions, compute final ranks, award badges/points, broadcast finish
- [ ] 080 · Score calculation service: `ScoreCalculator::compute(wpm, accuracy, errors)` — formula: `(WPM × accuracy) − errors`
- [ ] 081 · Contest join validation: free user limits, ban check, duplicate join prevention
- [ ] 082 · Free user contest limit middleware (max 3 active contests per day)

### 5.2 Admin Contest API
- [ ] 083 · `GET /api/v1/admin/contests` — paginated contest list with status filters
- [ ] 084 · `POST /api/v1/admin/contests` — create contest (type, schedule, typing text assignment)
- [ ] 085 · `PUT /api/v1/admin/contests/{id}` — edit contest details
- [ ] 086 · `DELETE /api/v1/admin/contests/{id}` — soft-delete draft contests only
- [ ] 087 · `POST /api/v1/admin/contests/{id}/publish` — set status to published
- [ ] 088 · `POST /api/v1/admin/contests/{id}/cancel` — cancel and notify participants
- [ ] 089 · `GET /api/v1/admin/typing-texts` — list all texts
- [ ] 090 · `POST /api/v1/admin/typing-texts` — create/import typing text with difficulty tagging
- [ ] 091 · Admin user management: list, ban, unban, plan override
- [ ] 092 · Admin anti-cheat log viewer with flag count per user

### 5.3 Frontend Contest Pages
- [ ] 093 · Contest List page — cards grid (Daily / Weekly / Monthly / Special tabs), each card shows countdown, prize, player count, join button
- [ ] 094 · Contest Detail page — full description, typing text preview (blurred pre-start), participant count live badge
- [ ] 095 · Join confirmation modal — shows rules, anti-cheat disclaimer, confirm button
- [ ] 096 · Countdown Timer component — real-time countdown to contest start (WebSocket synced)
- [ ] 097 · Contest card status badges — Upcoming / Live / Finished with color coding

---

## PHASE 6 — Typing Engine

### 6.1 Core Typing Component (React/TypeScript)
- [ ] 098 · `TypingEngine` component — renders text word-by-word, tracks keystrokes
- [ ] 099 · Real-time WPM calculator — rolling 5-second window calculation
- [ ] 100 · Real-time accuracy calculator — (correct chars / total chars typed) × 100
- [ ] 101 · Error counter and per-character color coding (correct = green, error = red, untyped = gray)
- [ ] 102 · Progress bar — percentage of text completed
- [ ] 103 · Countdown timer bar — time remaining with color shift (green → yellow → red)
- [ ] 104 · Auto-submit on timer expiry or text completion
- [ ] 105 · Smooth caret animation with CSS transitions
- [ ] 106 · Keyboard sound effects toggle (optional setting)
- [ ] 107 · Support for multilingual text (UTF-8, future Bangla mode)

### 6.2 Anti-Cheat System
- [ ] 108 · Disable paste events (`onPaste` blocked, `ctrl+v` blocked)
- [ ] 109 · Tab/window visibility change detection (`visibilitychange` event) — log flag to API
- [ ] 110 · Right-click context menu disabled during contest
- [ ] 111 · Suspicious WPM threshold detection backend (flag if WPM > 250 sustained)
- [ ] 112 · Minimum time-per-keystroke validation on submission (server-side)
- [ ] 113 · Keystroke timing array — record ms between keystrokes, send with submission
- [ ] 114 · Server-side: compare submitted wpm against keystroke timing array
- [ ] 115 · Auto-flag and log suspicious submissions to `contest_anti_cheat_logs`
- [ ] 116 · Admin alert notification when high-risk flag count per user exceeds threshold

---

## PHASE 7 — Real-Time Leaderboard System

### 7.1 Backend Real-Time
- [ ] 117 · Laravel Echo broadcast channel: `contest.{id}.leaderboard` (public channel)
- [ ] 118 · `LeaderboardUpdatedEvent` — fired after each valid submission, payload: top 10 array
- [ ] 119 · Redis sorted set for live contest leaderboard — `leaderboard:contest:{id}` keyed by score
- [ ] 120 · `GET /api/v1/contests/{id}/leaderboard` — return current top N (default 50)
- [ ] 121 · `GET /api/v1/leaderboard/global` — all-time global leaderboard with pagination
- [ ] 122 · `GET /api/v1/leaderboard/daily` — today's top performers
- [ ] 123 · `GET /api/v1/leaderboard/weekly` — current week leaderboard
- [ ] 124 · `GET /api/v1/leaderboard/monthly` — current month leaderboard
- [ ] 125 · `GET /api/v1/leaderboard/country` — country aggregate ranking
- [ ] 126 · Scheduled command: `RecalculateLeaderboardsCommand` — runs daily to update global/country ranks

### 7.2 Frontend Leaderboard
- [ ] 127 · Live Contest Leaderboard component — subscribes to Echo channel, animates rank changes
- [ ] 128 · Top 10 live scoreboard panel with animated position change indicators (up/down arrows)
- [ ] 129 · Crown icon animation for rank #1 (Framer Motion)
- [ ] 130 · Global Leaderboard page — table with avatar, flag, username, WPM, accuracy, points, rank
- [ ] 131 · Daily / Weekly / Monthly leaderboard tabs with period selector
- [ ] 132 · Country Leaderboard page — country flag, country name, avg WPM, total participants, rank
- [ ] 133 · User's own rank highlight in leaderboard table

---

## PHASE 8 — Badge & Point System

### 8.1 Backend
- [ ] 134 · `BadgeAwarder` service — checks all badge conditions after contest finalization
- [ ] 135 · Badge definitions: Beginner, Speed Demon, Accuracy King, Daily Winner, Weekly Champion, Monthly Legend, Global Top 100, Country #1, Perfectionist (100% accuracy), Marathon (50 contests)
- [ ] 136 · Points award on contest completion — Daily: 50pts, Weekly: 200pts, Monthly: 1000pts, Special: variable
- [ ] 137 · XP system — separate XP track for level progression
- [ ] 138 · Pro plan XP Boost: ×1.5 multiplier on all XP earned
- [ ] 139 · Bonus reward points for top 3 placement
- [ ] 140 · Daily login reward — basic for free, premium amount for pro
- [ ] 141 · `GET /api/v1/badges` — all available badges with unlock requirements
- [ ] 142 · Notification dispatch when badge is earned or points awarded

### 8.2 Frontend
- [ ] 143 · Badge collection gallery component — locked badges shown as grayscale with requirement tooltip
- [ ] 144 · Badge earned notification toast with animation
- [ ] 145 · Points/XP gain animation on result screen (counter increment effect)
- [ ] 146 · Level/XP progress bar in dashboard sidebar
- [ ] 147 · Premium badges with special glow effect (pro only)

---

## PHASE 9 — Subscription & Plan System

### 9.1 Backend Subscription
- [ ] 148 · Plan config: free vs pro feature matrix in `config/plans.php`
- [ ] 149 · `POST /api/v1/subscription/checkout` — generate payment gateway session (Stripe / SSLCommerz)
- [ ] 150 · `POST /api/v1/subscription/webhook` — handle payment success, activate pro plan, set expiry
- [ ] 151 · `POST /api/v1/subscription/cancel` — mark for non-renewal, send confirmation email
- [ ] 152 · `GET /api/v1/subscription/status` — return current plan, expiry, renewal status
- [ ] 153 · Scheduled job: `ExpireSubscriptionsCommand` — downgrade expired pro users to free daily
- [ ] 154 · Plan-gating middleware applied across all pro-only endpoints

### 9.2 Frontend Subscription
- [ ] 155 · Pricing page — Free vs Pro comparison table with full feature matrix
- [ ] 156 · Upgrade modal — triggered when free user hits plan limit or clicks locked feature
- [ ] 157 · Checkout flow — payment gateway redirect / embedded form
- [ ] 158 · Subscription management page — shows current plan, expiry date, cancel option
- [ ] 159 · Pro badge / VIP crown icon displayed in profile and leaderboard for pro users
- [ ] 160 · Upgrade CTA banners — contextual locked feature previews for free users

---

## PHASE 10 — Dashboard

### 10.1 Frontend User Dashboard
- [ ] 161 · Dashboard layout — sidebar nav (Dashboard, Contests, Leaderboard, Profile, Settings, Upgrade)
- [ ] 162 · Dashboard home — Current Rank card, Total Points card, Badges count card, WPM today card
- [ ] 163 · Upcoming contests widget — next 3 contests with countdown and join button
- [ ] 164 · Active contest banner — if user has a contest in progress right now
- [ ] 165 · Recent results table — last 5 contest results with scores
- [ ] 166 · Notification bell with dropdown — unread notifications with mark-all-read
- [ ] 167 · Dark mode by default, with optional light mode toggle stored in user preferences

### 10.2 Admin Panel
- [ ] 168 · Admin layout — separate route group `/admin`, admin role guard
- [ ] 169 · Admin dashboard overview — total users, active contests today, total revenue, new registrations chart
- [ ] 170 · Contest Management CRUD — create, edit, publish, cancel, delete contests
- [ ] 171 · Typing Text Management — list, create, edit, delete texts; difficulty tagging
- [ ] 172 · User Management — search, view profile, ban/unban, plan override
- [ ] 173 · Anti-Cheat Logs — table of flagged events, filter by contest/user/type
- [ ] 174 · Reports page — export user stats, contest results as CSV
- [ ] 175 · Premium Dashboard (pro users) — advanced charts, performance insights

---

## PHASE 11 — Notification System

- [ ] 176 · Laravel notification channels: database + broadcast (real-time toast) + mail
- [ ] 177 · Notification types: ContestStartingSoon (30 min before), ContestFinalized, BadgeEarned, PointsAwarded, RankChanged (pro), SubscriptionExpiring, AccountBanned
- [ ] 178 · `GET /api/v1/notifications` — paginated notification list
- [ ] 179 · `POST /api/v1/notifications/read` — mark notification(s) as read
- [ ] 180 · Frontend: real-time toast notifications via Echo broadcast
- [ ] 181 · Pro users: advanced notification preferences page (toggle per type)

---

## PHASE 12 — Advanced Pro Features

- [ ] 182 · Multiplayer Battle Mode (1v1 or up to 6 players) — private room creation, invite link, synchronized start
- [ ] 183 · `POST /api/v1/battles/create` — create private room, returns invite code
- [ ] 184 · `POST /api/v1/battles/{code}/join` — join room by invite code
- [ ] 185 · Battle real-time channel: `battle.{room_id}` — sync typing progress of all players live
- [ ] 186 · Private Typing Rooms — persistent room for friends/teams
- [ ] 187 · Team Battle Mode — team vs team cumulative score
- [ ] 188 · Friend Challenge — challenge friend directly by username (pro: unlimited, free: 2/day)
- [ ] 189 · AI Typing Suggestions — post-session analysis, identify weak letter patterns, suggest drills
- [ ] 190 · Typing Replay — playback of user's previous session with cursor animation
- [ ] 191 · Data Export — export personal stats as CSV/PDF (pro only)
- [ ] 192 · Multiple Device Sync — session/settings sync via cloud (pro: full, free: limited)
- [ ] 193 · API Access — limited API key for personal integrations (pro only)

---

## PHASE 13 — Worldwide Branding & Internationalization

- [ ] 194 · Country selector at registration — ISO 3166-1 with flag emoji
- [ ] 195 · Country leaderboard — global ranking by country aggregate score
- [ ] 196 · University leaderboard — optional institutional affiliation field, filter by university
- [ ] 197 · `i18n` setup with `react-i18next` — initial support: English, Bangla
- [ ] 198 · Bangla typing mode — Unicode Bengali text support in typing engine
- [ ] 199 · SEO: server-side meta tags, Open Graph for contest pages
- [ ] 200 · Custom domain for regional branding (e.g. bd.typingcontest.com)
- [ ] 201 · Timezone-aware contest scheduling — display times in user's local timezone
- [ ] 202 · Sponsored contest support — sponsor logo, prize description, custom branding on contest page
- [ ] 203 · Premium Seasonal Events — annual championship, country championship tournaments

---

## PHASE 14 — Testing & QA

- [ ] 204 · PHPUnit feature tests — Auth (register, login, refresh, logout)
- [ ] 205 · PHPUnit feature tests — Contest lifecycle (create, publish, join, submit, finalize)
- [ ] 206 · PHPUnit unit tests — `ScoreCalculator` formula accuracy
- [ ] 207 · PHPUnit unit tests — `BadgeAwarder` condition logic
- [ ] 208 · PHPUnit tests — Anti-cheat flag detection logic
- [ ] 209 · PHPUnit tests — Plan-gating middleware
- [ ] 210 · PHPUnit tests — Subscription lifecycle (activate, expire, downgrade)
- [ ] 211 · React Testing Library — `TypingEngine` component (input handling, WPM calculation, paste block)
- [ ] 212 · React Testing Library — Leaderboard real-time update rendering
- [ ] 213 · React Testing Library — Auth flow (register, login, protected route redirect)
- [ ] 214 · Cypress E2E — full contest join and submission flow
- [ ] 215 · Cypress E2E — admin creates and publishes a contest
- [ ] 216 · Load testing — 500 concurrent users on active contest with `k6` or `Artillery`
- [ ] 217 · Security audit — SQL injection, XSS, CSRF, JWT expiry, rate limit bypass tests

---

## PHASE 15 — Deployment & Operations

- [ ] 218 · Production Nginx config — API + frontend, SSL termination
- [ ] 219 · Supervisor config for Laravel Queue workers and Horizon
- [ ] 220 · Soketi (self-hosted Pusher-compatible) WebSocket server setup
- [ ] 221 · MySQL production tuning (indexes on `contests.start_time`, `contest_participants.score`, `users.global_rank`)
- [ ] 222 · Redis cluster config for leaderboard sorted sets and broadcast
- [ ] 223 · S3 bucket config for avatar uploads and exports
- [ ] 224 · Log aggregation with Laravel Log + Sentry for error tracking
- [ ] 225 · Uptime monitoring and alert webhooks
- [ ] 226 · Automated database backup — daily snapshot to S3
- [ ] 227 · CDN setup for frontend static assets (Cloudflare)
- [ ] 228 · Production deployment checklist — cache clear, migrate, queue restart, zero-downtime deploy script

---

## Task Summary

| Phase | Area | Tasks |
|-------|------|-------|
| 1 | Project Setup & Infrastructure | 001–022 |
| 2 | Database Design & Migrations | 023–037 |
| 3 | Authentication System | 038–055 |
| 4 | User Profile System | 056–071 |
| 5 | Contest Management System | 072–097 |
| 6 | Typing Engine + Anti-Cheat | 098–116 |
| 7 | Real-Time Leaderboard | 117–133 |
| 8 | Badge & Point System | 134–147 |
| 9 | Subscription & Plan System | 148–160 |
| 10 | Dashboard & Admin Panel | 161–175 |
| 11 | Notification System | 176–181 |
| 12 | Advanced Pro Features | 182–193 |
| 13 | Worldwide Branding & i18n | 194–203 |
| 14 | Testing & QA | 204–217 |
| 15 | Deployment & Operations | 218–228 |
| **Total** | | **228 Tasks** |
