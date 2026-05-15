/**
 * User-facing profile API client.
 * Covers: /profile, /profile/stats, /profile/badges,
 *         /profile/matches, /profile/activity, /profile/{username} (public),
 *         and leaderboard endpoints.
 */

const API_BASE =
  (import.meta.env.VITE_API_BASE_URL as string | undefined)?.replace(/\/$/, '') ??
  'http://127.0.0.1:8001/api/v1'

async function request<T>(
  path: string,
  options: RequestInit = {},
  token?: string | null,
): Promise<T> {
  const headers = new Headers(options.headers)
  headers.set('Accept', 'application/json')
  if (options.body) headers.set('Content-Type', 'application/json')
  if (token) headers.set('Authorization', `Bearer ${token}`)

  const res = await fetch(`${API_BASE}${path}`, { ...options, headers })
  const isJson = res.headers.get('content-type')?.includes('application/json')
  const payload = isJson ? await res.json() : null

  if (!res.ok) {
    const msg =
      payload?.message ??
      Object.values(payload?.errors ?? {}).flat().join(' ') ??
      `HTTP ${res.status}`
    throw new Error(String(msg))
  }

  return payload as T
}

// ── Types ────────────────────────────────────────────────────────────────────

export type UserProfile = {
  id: number
  name: string
  username: string
  email: string
  avatar: string | null
  cover: string | null
  country: string | null
  plan_type: 'free' | 'pro' | 'vip' | string
  subscription_status: string
  xp_points: number
  global_rank: number | null
  created_at: string
}

export type ProfileStats = {
  total_contests: number
  total_wins: number
  best_wpm: number
  avg_wpm: number
  avg_accuracy: number
  total_xp: number
  global_rank: number | null
  streak_days: number
}

export type UserBadge = {
  id: number
  name: string
  slug: string
  icon_url: string | null
  description: string
  is_premium: boolean
  earned_at: string
}

export type MatchRecord = {
  id: number
  contest_id: number
  contest_title: string
  wpm: number
  accuracy: number
  errors: number
  score: number
  rank: number | null
  played_at: string
}

export type ActivityItem = {
  id: number
  type: string
  description: string
  metadata: Record<string, unknown>
  created_at: string
}

export type PublicProfile = UserProfile & {
  stats: ProfileStats
  badges: UserBadge[]
  recent_matches: MatchRecord[]
}

export type LeaderboardEntry = {
  rank: number
  user_id: number
  name: string
  username: string
  country: string | null
  avatar: string | null
  wpm: number
  accuracy: number
  score: number
  contests_played: number
}

// ── My Profile ───────────────────────────────────────────────────────────────

export function getMyProfile(token: string): Promise<{ user: UserProfile }> {
  return request<{ user: UserProfile }>('/profile', {}, token)
}

export function updateMyProfile(
  token: string,
  data: { name?: string; country?: string; bio?: string },
): Promise<{ user: UserProfile }> {
  return request<{ user: UserProfile }>(
    '/profile/update',
    { method: 'PUT', body: JSON.stringify(data) },
    token,
  )
}

export function getMyStats(token: string): Promise<{ stats: ProfileStats }> {
  return request<{ stats: ProfileStats }>('/profile/stats', {}, token)
}

export function getMyBadges(token: string): Promise<{ badges: UserBadge[] }> {
  return request<{ badges: UserBadge[] }>('/profile/badges', {}, token)
}

export function getMyMatches(token: string): Promise<{ matches: MatchRecord[] }> {
  return request<{ matches: MatchRecord[] }>('/profile/matches', {}, token)
}

export function getMyActivity(token: string): Promise<{ activity: ActivityItem[] }> {
  return request<{ activity: ActivityItem[] }>('/profile/activity', {}, token)
}

// ── Public Profile ───────────────────────────────────────────────────────────

export function getPublicProfile(username: string): Promise<{ user: PublicProfile }> {
  return request<{ user: PublicProfile }>(`/profile/${username}`)
}

// ── Leaderboard ──────────────────────────────────────────────────────────────

export type LeaderboardResponse = {
  leaderboard?: LeaderboardEntry[]
  data?: LeaderboardEntry[]
  total?: number
  per_page?: number
  current_page?: number
  last_page?: number
}

export function getGlobalLeaderboard(): Promise<LeaderboardResponse> {
  return request<LeaderboardResponse>('/leaderboard/global')
}

export function getDailyLeaderboard(): Promise<LeaderboardResponse> {
  return request<LeaderboardResponse>('/leaderboard/daily')
}

export function getWeeklyLeaderboard(): Promise<LeaderboardResponse> {
  return request<LeaderboardResponse>('/leaderboard/weekly')
}

export function getMonthlyLeaderboard(): Promise<LeaderboardResponse> {
  return request<LeaderboardResponse>('/leaderboard/monthly')
}

export function getTopTen(): Promise<LeaderboardResponse> {
  return request<LeaderboardResponse>('/leaderboard/top-10')
}

export function getCountryLeaderboard(countryCode: string): Promise<LeaderboardResponse> {
  return request<LeaderboardResponse>(`/leaderboard/country/${countryCode}`)
}

export function getContestLeaderboard(contestId: number): Promise<LeaderboardResponse> {
  return request<LeaderboardResponse>(`/leaderboard/contest/${contestId}`)
}
