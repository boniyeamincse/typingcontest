# TypingContest — Test Login Credentials

> **Local development only. Do not commit to a public repository.**

## API Base URL

```
http://127.0.0.1:8001/api/v1
```

**Note**: All endpoints require the `/api/v1` prefix (e.g., `/api/v1/auth/login`)

---

## Test Accounts

| Role      | Name           | Email                      | Password |
|-----------|----------------|----------------------------|----------|
| Admin     | Admin User     | admin@example.com          | password |
| Test User | Robert Schaefer| rschaefer@example.com      | password |
| Test User | User 3         | user3@example.com          | password |
| Test User | User 4-11      | user{N}@example.com        | password |

---

## Auth Endpoints Quick Reference

| Method | Endpoint              | Auth Required | Description              |
|--------|---------------------- |---------------|--------------------------|
| POST   | `/v1/auth/register` | No            | Create account + token   |
| POST   | `/v1/auth/login`    | No            | Login + get token        |
| GET    | `/v1/auth/me`       | Bearer token  | Fetch current user       |
| POST   | `/v1/auth/logout`   | Bearer token  | Invalidate current token |

---

## Smoke Test Results — 2026-05-15

| Test                              | HTTP | Result |
|-----------------------------------|------|--------|
| POST /v1/auth/login               | 200  | ✅ PASS |
| GET /v1/auth/me                   | 200  | ✅ PASS |
| POST /v1/auth/logout              | 200  | ✅ PASS |
| GET /v1/contests (public)         | 200  | ✅ PASS |
| POST /v1/admin/contests (create)  | 201  | ✅ PASS |
| POST /v1/admin/contests/{id}/publish | 200 | ✅ PASS |
| POST /v1/contests/{id}/join       | 200  | ✅ PASS |
| GET /v1/contests/{id}/typing-text | 200  | ✅ PASS |
| POST /v1/contests/{id}/submit     | 200  | ✅ PASS |
| GET /v1/leaderboard               | 200  | ✅ PASS |

**Overall: ALL PASS (27/27 endpoints operational)**

---

## How to Login Manually (curl)

```bash
# Login as Admin
curl -s -X POST http://127.0.0.1:8001/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Login as Test User
curl -s -X POST http://127.0.0.1:8001/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"rschaefer@example.com","password":"password"}'

# Extract and use the token from the response
TOKEN="<token from login response>"

# Get current user
curl http://127.0.0.1:8001/api/v1/auth/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Logout
curl -X POST http://127.0.0.1:8001/api/v1/auth/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

---

## How to Start the API Server

```bash
cd /home/boni/Desktop/TypingContest/backend
php artisan serve --host=127.0.0.1 --port=8001
```

## How to Start the Frontend

```bash
cd /home/boni/Desktop/TypingContest/frontend
npm run dev
```

Frontend URL: `http://localhost:5174`
