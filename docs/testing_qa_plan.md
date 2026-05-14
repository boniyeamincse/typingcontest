# TypingContest Testing and QA Plan

## 1. Test Strategy
Test pyramid:
- Unit tests for scoring, eligibility, anti-cheat rules.
- Feature/integration tests for API endpoints and authz rules.
- End-to-end tests for contest lifecycle and leaderboard updates.

## 2. Test Types
- Unit: score formula, rank ordering, plan entitlement checks.
- API integration: request validation, status codes, response schema.
- Real-time tests: event broadcast and client receipt behavior.
- Security tests: auth bypass attempts, rate limits, invalid signatures.
- Performance tests: concurrent submissions and leaderboard read load.
- Regression tests: all resolved defects get automated tests.

## 3. Core Scenarios
- User registration/login/logout.
- Join active contest and submit result.
- Reject submission after contest end.
- Enforce Free plan limits.
- Process Pro plan activation webhook.
- Admin contest publishing and moderation actions.

## 4. Quality Gates
Before merge:
- All tests pass.
- Lint and static checks pass.
- New endpoints documented.

Before release:
- Smoke test in staging.
- No open critical defects.
- Rollback procedure validated.

## 5. Test Data
- Seed deterministic users, contests, and text datasets.
- Use dedicated fixtures for anti-cheat edge cases.
- Avoid production data in test environments.

## 6. Acceptance Criteria Template
Every story should define:
- Functional acceptance checks.
- Validation and error behavior.
- Security and authorization expectations.
- Observability expectations (log/metric traces).

## 7. Defect Workflow
- Severity levels: Critical, High, Medium, Low.
- Critical and High must be fixed before release.
- Root cause analysis required for repeat defects.
