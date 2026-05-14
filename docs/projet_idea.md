# TypingContest Project Documentation Hub

This file is the master index and concise SRS for TypingContest.

For full details, use the dedicated documents in this folder:
- software_blueprint.md
- development_plan.md
- api_documentation.md
- testing_qa_plan.md
- deployment_operations_plan.md

## 1. Project Summary
TypingContest is a real-time competitive typing platform where users join scheduled contests, submit performance, and compete on live leaderboards.

Primary business model:
- Free plan for basic participation.
- Pro plan for advanced access and analytics.

Primary technical direction:
- Laravel API backend.
- Real-time broadcasting for live ranking updates.
- Relational database with Redis for performance-sensitive workloads.

## 2. Problem Statement
Most typing tools are solo practice systems. TypingContest targets competitive, recurring events with progression, fairness checks, and monetized premium experiences.

## 3. Core Requirements (Condensed)
Functional:
- User registration, login, profile, and progression.
- Contest lifecycle: create, publish, join, run, finalize.
- Result submission and deterministic scoring.
- Live and historical leaderboard views.
- Plan-based feature access (Free/Pro).
- Admin operations and moderation.

Non-functional:
- Low-latency updates for active contests.
- High reliability and clear recovery procedures.
- Security hardening across auth, validation, and abuse prevention.
- Observability with logs, metrics, and alerts.

## 4. Suggested Score Formula
Initial formula for implementation and tuning:

Score = (WPM * Accuracy) - Errors

Where:
- WPM is words per minute.
- Accuracy is a normalized value (0 to 1).
- Errors is total mistake count.

## 5. Main User Roles
- Free User: limited access and baseline stats.
- Pro User: premium features and broader participation.
- Admin: operational control, moderation, and reporting.

## 6. MVP Feature Baseline
- Auth + profile APIs.
- Contest discovery/join/submit APIs.
- Contest and global leaderboards.
- Admin contest CRUD and publish controls.
- Basic anti-cheat rules and suspicious activity logging.

## 7. Documentation Completion Status
This documentation set now includes:
- Product/software blueprint.
- End-to-end development roadmap.
- API planning contract.
- QA and testing approach.
- Deployment and production operations plan.

## 8. Next Build Step
Use these docs to implement the backend in phases:
1. Build auth, contest models, and migrations.
2. Implement contest workflows and scoring.
3. Add real-time leaderboards and caching.
4. Enforce plan restrictions and anti-cheat checks.
5. Harden with tests, CI, and deployment pipeline.
| Country Ranking             | Yes         | Yes       |
| Contest History             | Last 10     | Unlimited |
| Typing Practice Mode        | Basic       | Advanced  |
| Multiplayer Battle          | No          | Yes       |
| AI Typing Suggestions       | No          | Yes       |
| Advanced Analytics          | No          | Yes       |
| WPM Trend Graph             | No          | Yes       |
| Accuracy Analytics          | No          | Yes       |
| Error Heatmap               | No          | Yes       |
| Typing Replay               | No          | Yes       |
| Detailed Performance Report | No          | Yes       |
| Badge Collection            | Limited     | Unlimited |
| Premium Badges              | No          | Yes       |
| XP Boost                    | No          | Yes       |
| Bonus Reward Points         | No          | Yes       |
| Daily Reward Bonus          | Basic       | Premium   |
| Ad-Free Experience          | No          | Yes       |
| Profile Customization       | Limited     | Full      |
| Custom Themes               | No          | Yes       |
| Animated Profile Frame      | No          | Yes       |
| Priority Match Join         | No          | Yes       |
| Private Typing Rooms        | No          | Yes       |
| Team Battle Access          | No          | Yes       |
| Friend Challenge            | Limited     | Unlimited |
| Contest Notifications       | Basic       | Advanced  |
| Support Priority            | Normal      | Priority  |
| Data Export                 | No          | Yes       |
| Multiple Device Sync        | Limited     | Yes       |
| Premium Dashboard           | No          | Yes       |
| VIP Crown/Icon              | No          | Yes       |
| Advanced Security Logs      | No          | Yes       |
| Keyboard Statistics         | No          | Yes       |
| Premium Seasonal Events     | No          | Yes       |
| Premium Rewards             | No          | Yes       |
| API Access                  | No          | Limited   |
| Early Feature Access        | No          | Yes       |

---

# Normal User Features

## Free User Gets

### Core Features

* Register/Login
* Join basic contests
* View leaderboard
* Earn points
* Basic badges
* Basic profile

---

## Limitations

* Ads enabled
* Contest limits
* No advanced analytics
* No premium tournaments

---

# Pro User Features

## Pro User Gets

### Full Competitive Experience

* Unlimited contests
* Advanced analytics
* AI typing suggestions
* Multiplayer battles
* Premium rewards
* No advertisements

---

## Pro Exclusive Features

* Custom themes
* Profile frame
* Pro badge
* Priority leaderboard updates
* Exclusive tournaments

---

# Suggested UI Labels

## Free Plan

```txt id="lk5xkk"
Starter Plan
```

## Pro Plan

```txt id="0s5x1f"
TypingContest Pro
```

---

# Suggested Upgrade Section

## Show Benefits

### Upgrade to Pro

* Unlimited contests
* Premium tournaments
* No ads
* Advanced stats
* Multiplayer battle mode

---

# Suggested Future Plans

## VIP Plan (Later)

Additional:

* AI coach
* Exclusive rewards
* VIP leaderboard
* Sponsored competitions

---

# Recommended Backend Logic

## Middleware Example

```php id="k2lqcl"
if($user->plan == 'free'){
   // limit contests
}
```

---

# Suggested Database Fields

## users table

```sql id="z5d4z6"
plan_type
subscription_status
subscription_end_date
```

---

# Suggested Gamification

## Free Users

Encourage upgrades using:

* Locked features
* Premium badges
* Advanced analytics preview

---

# Suggested Revenue Strategy

## Income Sources

* Pro subscriptions
* Ads for free users
* Sponsored contests
* Premium tournaments

---

This structure creates a strong freemium ecosystem similar to:

* Monkeytype
* Nitro Type
* TypeRacer


You can build a modern competitive typing platform named **TypingContest** with features similar to typing battle systems, coding contest scoreboards, and esports ranking systems.

## Suggested Project Structure

### Project Name

# `TypingContest`

---

# Main Features

## 1. User System

Users can:

* Register/Login
* Join contests
* View rank
* Earn badges
* Track typing speed
* View history

### User Profile Fields

* Username
* Email
* Avatar
* Total Points
* Total WPM
* Accuracy %
* Badge Count
* Global Rank
* Country

---

# Contest Types

## Daily Contest

* Runs every day
* Fast competition
* Small rewards

## Weekly Contest

* 7-day leaderboard
* Bigger points

## Monthly Championship

* Premium leaderboard
* Top 10 featured users
* Badge rewards

---

# Contest Flow

## User Experience

### Step 1

User sees contest cards:

* Daily Contest
* Weekly Contest
* Monthly Contest

Each card shows:

* Start time
* End time
* Total players
* Prize
* Join button

---

## Step 2

User clicks:

# “Join Now”

Then:

* User automatically joins
* Contest added to dashboard
* Countdown timer starts

Example:

```txt
Contest Starts In:
01:12:55
```

---

## Step 3

At scheduled time:

# Competition automatically starts

Typing engine opens.

---

# Typing Engine Features

## Real-time Typing Test

Show:

* WPM
* Accuracy
* Errors
* Remaining time
* Progress bar

## Anti-cheat

* Disable paste
* Tab switch detection
* AI detection rules
* Suspicious speed detection

---

# Ranking System

## Live Rank Update

After every submission:

* Rank updates
* Scoreboard updates
* Top 10 updates instantly

---

# Score Calculation

Example formula:

```txt
Score =
(WPM × Accuracy) - Errors
```

---

# Leaderboard System

## Contest Leaderboard

Show:

| Rank | User  | WPM | Accuracy | Points |
| ---- | ----- | --- | -------- | ------ |
| 1    | Boni  | 115 | 98%      | 1500   |
| 2    | Ratan | 108 | 97%      | 1400   |

---

# Top 10 Live Scoreboard

Feature:

* Auto-refresh every 5 seconds
* Animated rank changes
* Crown icon for #1

---

# Badge System

Users earn badges like:

* Beginner
* Speed Master
* Accuracy King
* Daily Winner
* Weekly Champion
* Monthly Legend

---

# Point System

## Global Points

Every contest gives points.

Example:

* Daily → 50 points
* Weekly → 200 points
* Monthly → 1000 points

---

# Dashboard Modules

## User Dashboard

Show:

* Current Rank
* Total Points
* Badges
* Joined Contests
* Upcoming Matches
* Typing History

---

# Admin Panel

Admin can:

* Create contests
* Set contest time
* Add typing paragraphs
* Monitor users
* Ban cheaters
* Publish results

---

# Suggested Technology Stack

## Frontend

* React.js / Next.js
* Tailwind CSS
* Socket.IO Client

## Backend

* Node.js
* Express.js

## Database

* PostgreSQL
  or
* MongoDB

## Real-time Features

* Socket.IO
  or
* Redis Pub/Sub

## Authentication

* JWT
* Google Login

---

# Recommended Database Tables

## users

```txt
id
username
email
password
points
badges
rank
```

## contests

```txt
id
title
type
start_time
end_time
status
```

## contest_participants

```txt
id
contest_id
user_id
wpm
accuracy
score
rank
```

## badges

```txt
id
name
icon
description
```

---

# Real-time Architecture

## Important

Use:

* WebSocket
* Socket.IO

For:

* Live scoreboard
* Countdown
* Rank updates
* Contest start notification

---

# Suggested Pages

## Public Pages

* Home
* Contest List
* Leaderboard
* About

## Private Pages

* Dashboard
* Active Contest
* Result Page
* Profile

## Admin Pages

* Contest Management
* User Management
* Reports

---

# Extra Advanced Features

## Future Ideas

* Multiplayer typing battle
* Team contests
* AI-generated typing text
* বাংলা typing mode
* University leaderboard
* Country leaderboard
* Referral rewards

---

# Suggested UI Design

Modern gaming style:

* Dark mode
* Neon leaderboard
* Animated ranks
* Trophy effects
* Live counters

---

# Suggested API Endpoints

## Authentication

```txt
POST /api/auth/register
POST /api/auth/login
```

## Contest

```txt
GET /api/contests
POST /api/contest/join
GET /api/contest/:id
```

## Leaderboard

```txt
GET /api/leaderboard/daily
GET /api/leaderboard/weekly
GET /api/leaderboard/monthly
```

## Result

```txt
POST /api/result/submit
```

---

# Recommended Development Phases

## Phase 1

* Authentication
* Contest system
* Typing engine

## Phase 2

* Live leaderboard
* Ranking
* Points

## Phase 3

* Badges
* Admin panel
* Anti-cheat

## Phase 4

* Mobile app
* Multiplayer mode
* AI analytics

---

You can make this project similar to:

* Monkeytype
* TypeRacer
* 10FastFingers

This idea has strong potential for:

* Competitive gaming
* EdTech
* University competitions
* Skill development platforms
* Sponsorship-based contests
* Ad revenue and premium memberships

