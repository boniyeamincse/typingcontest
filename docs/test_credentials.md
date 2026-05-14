# TypingContest — Test Login Credentials

> **Local development only. Do not commit to a public repository.**

## API Base URL

```
http://127.0.0.1:8001/api
```

---

## Test Accounts

| Role       | Name         | Email                        | Password    |
|------------|--------------|------------------------------|-------------|
| Test User  | Test User    | test@typingcontest.dev       | Password123! |
| Admin Demo | Admin Demo   | admin@typingcontest.dev      | Admin1234!  |

---

## Auth Endpoints Quick Reference

| Method | Endpoint        | Auth Required | Description              |
|--------|-----------------|---------------|--------------------------|
| POST   | `/api/register` | No            | Create account + token   |
| POST   | `/api/login`    | No            | Login + get token        |
| GET    | `/api/me`       | Bearer token  | Fetch current user       |
| POST   | `/api/logout`   | Bearer token  | Invalidate current token |

---

## Smoke Test Results — 2026-05-14

| Test                     | HTTP | Result |
|--------------------------|------|--------|
| POST /login              | 200  | ✅ PASS |
| GET /me                  | 200  | ✅ PASS |
| POST /logout             | 200  | ✅ PASS |
| GET /me (after logout)   | 401  | ✅ PASS |
| GET /contests (public)   | 200  | ✅ PASS |

**Overall: ALL PASS (5/5)**

---

## How to Login Manually (curl)

```bash
# Login
curl -s -X POST http://127.0.0.1:8001/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"admin@typingcontest.dev","password":"Admin1234!"}'

# Use the token from the response
TOKEN="<token from login response>"

# Get current user
curl http://127.0.0.1:8001/api/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Logout
curl -X POST http://127.0.0.1:8001/api/logout \
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
