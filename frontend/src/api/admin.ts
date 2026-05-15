/**
 * Admin API client — typed wrappers around every /api/v1/admin/* endpoint.
 * Stores the admin token separately from the regular user token so both
 * sessions can coexist independently.
 */

const API_BASE =
  (import.meta.env.VITE_API_BASE_URL?.replace(/\/$/, '') ?? 'http://127.0.0.1:8001/api/v1')

const ADMIN_TOKEN_KEY = 'tc_admin_token'

export function getAdminToken(): string | null {
  return localStorage.getItem(ADMIN_TOKEN_KEY)
}
export function setAdminToken(t: string | null): void {
  t ? localStorage.setItem(ADMIN_TOKEN_KEY, t) : localStorage.removeItem(ADMIN_TOKEN_KEY)
}

// ── Generic fetch helper ────────────────────────────────────────────────────

async function adminFetch<T>(
  method: string,
  path: string,
  body?: unknown,
  token?: string | null,
): Promise<T> {
  const tok = token ?? getAdminToken()
  const headers: Record<string, string> = { Accept: 'application/json' }
  if (tok) headers.Authorization = `Bearer ${tok}`
  if (body) headers['Content-Type'] = 'application/json'

  const res = await fetch(`${API_BASE}${path}`, {
    method,
    headers,
    body: body ? JSON.stringify(body) : undefined,
  })

  const ct = res.headers.get('content-type') ?? ''
  const payload = ct.includes('application/json') ? await res.json() : null

  if (!res.ok) {
    const msg =
      payload?.message ??
      Object.values(payload?.errors ?? {}).flat().join(' ') ??
      `HTTP ${res.status}`
    throw new Error(String(msg))
  }

  return payload as T
}

const get = <T>(path: string) => adminFetch<T>('GET', path)
const post = <T>(path: string, body?: unknown) => adminFetch<T>('POST', path, body)
// ── Types ───────────────────────────────────────────────────────────────────

export type AdminUser = {
  id: number
  name: string
  username: string
  email: string
  plan_type: string
  is_banned: boolean
  suspended_until: string | null
  created_at: string
  roles?: { name: string }[]
}

export type Contest = {
  id: number
  title: string
  status: string
  difficulty: string
  max_participants: number
  participants_count: number
  starts_at: string
  ends_at: string | null
  created_at: string
}

export type SupportTicket = {
  id: number
  subject: string
  priority: string
  status: string
  created_at: string
  user?: { id: number; name: string; email: string }
}

export type Payment = {
  id: number
  user_id: number
  amount: number
  final_amount: number
  status: string
  gateway: string
  created_at: string
  user?: { name: string; email: string }
}

export type DashboardOverview = {
  summary: {
    total_users: number
    active_users: number
    pro_users: number
    revenue: number
    active_contests: number
    live_players: number
    today_new_users: number
  }
  daily_growth: { date: string; users: number }[]
  system_health: Record<string, string>
}

export type PaginatedResponse<T> = {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export type ApiResponse<T> = { success: boolean; message: string; data: T }

// ── Auth ────────────────────────────────────────────────────────────────────

export async function adminLogin(email: string, password: string) {
  const res = await adminFetch<ApiResponse<{ token: string; user: AdminUser }>>(
    'POST',
    '/admin/login',
    { email, password },
    null,
  )
  setAdminToken(res.data.token)
  return res.data
}

export async function adminLogout() {
  await post('/admin/logout').catch(() => null)
  setAdminToken(null)
}

// ── Dashboard ───────────────────────────────────────────────────────────────

export const fetchDashboardOverview = (days = 7) =>
  get<ApiResponse<DashboardOverview>>(`/admin/dashboard/overview?days=${days}`)

// ── Users ───────────────────────────────────────────────────────────────────

export const fetchUsers = (params?: Record<string, string | number>) =>
  get<ApiResponse<PaginatedResponse<AdminUser>>>(
    '/admin/users?' + new URLSearchParams(params as Record<string, string>).toString(),
  )

export const fetchSuspiciousUsers = () =>
  get<ApiResponse<PaginatedResponse<AdminUser>>>('/admin/users/suspicious')

export const banUser = (userId: number, reason: string) =>
  post<ApiResponse<AdminUser>>('/admin/users/ban', { user_id: userId, reason })

export const unbanUser = (userId: number) =>
  post<ApiResponse<AdminUser>>('/admin/users/unban', { user_id: userId })

export const suspendUser = (userId: number, until: string, reason: string) =>
  post<ApiResponse<AdminUser>>('/admin/users/suspend', { user_id: userId, until, reason })

export const resetUserPassword = (userId: number, newPassword: string) =>
  post<ApiResponse<null>>('/admin/users/reset-password', {
    user_id: userId,
    new_password: newPassword,
  })

// ── Contests ────────────────────────────────────────────────────────────────

export const fetchAdminContests = (params?: Record<string, string | number>) =>
  get<ApiResponse<PaginatedResponse<Contest>>>(
    '/admin/contests?' + new URLSearchParams(params as Record<string, string>).toString(),
  )

export const stopContest = (id: number) =>
  post<ApiResponse<Contest>>(`/admin/contests/${id}/stop`)

export const cloneContest = (id: number) =>
  post<ApiResponse<Contest>>(`/admin/contests/${id}/clone`)

export const fetchContestAnalytics = (id: number) =>
  get<ApiResponse<unknown>>(`/admin/contests/${id}/analytics`)

// ── Support Tickets ──────────────────────────────────────────────────────────

export const fetchTickets = (params?: Record<string, string | number>) =>
  get<ApiResponse<PaginatedResponse<SupportTicket>>>(
    '/admin/support/tickets?' + new URLSearchParams(params as Record<string, string>).toString(),
  )

export const closeTicket = (id: number) =>
  post<ApiResponse<SupportTicket>>(`/admin/support/tickets/${id}/close`)

export const escalateTicket = (id: number) =>
  post<ApiResponse<SupportTicket>>(`/admin/support/tickets/${id}/escalate`)

export const respondToTicket = (id: number, message: string) =>
  post<ApiResponse<SupportTicket>>(`/admin/support/tickets/${id}/respond`, { message })

// ── Payments ─────────────────────────────────────────────────────────────────

export const fetchPayments = (params?: Record<string, string | number>) =>
  get<ApiResponse<PaginatedResponse<Payment>>>(
    '/admin/payments?' + new URLSearchParams(params as Record<string, string>).toString(),
  )

export const refundPayment = (intentId: string) =>
  post<ApiResponse<Payment>>(`/admin/payments/${intentId}/refund`)

// ── Security ─────────────────────────────────────────────────────────────────

export const fetchCheatingUsers = () =>
  get<ApiResponse<PaginatedResponse<AdminUser>>>('/admin/security/cheating-users')

export const fetchSuspiciousLogins = () =>
  get<ApiResponse<PaginatedResponse<unknown>>>('/admin/security/suspicious-logins')

export const blockIp = (value: string, reason: string) =>
  post<ApiResponse<unknown>>('/admin/security/blocks', { type: 'ip', value, reason })

// ── Leaderboard ───────────────────────────────────────────────────────────────

export const recalculateLeaderboard = () =>
  post<ApiResponse<unknown>>('/admin/leaderboard/recalculate')

export const recalculateLeaderboardByPeriod = (period: string) =>
  post<ApiResponse<unknown>>('/admin/leaderboard/recalculate', { period })

export const resetLeaderboard = (period: string) =>
  post<ApiResponse<unknown>>('/admin/leaderboard/reset', { period })

// ── Reports ───────────────────────────────────────────────────────────────────

export const fetchReports = () =>
  get<ApiResponse<unknown[]>>('/admin/reports')

export const queueReport = (reportType: string, dateFrom: string, dateTo: string) =>
  post<ApiResponse<unknown>>('/admin/reports/queue', {
    report_type: reportType,
    date_from: dateFrom,
    date_to: dateTo,
  })

// ── Roles ─────────────────────────────────────────────────────────────────────

export const fetchRoles = () =>
  get<ApiResponse<{ id: number; name: string }[]>>('/admin/roles')

export const assignRoles = (userId: number, roles: string[]) =>
  post<ApiResponse<AdminUser>>('/admin/roles/assign', { user_id: userId, roles })

// ── Notifications ─────────────────────────────────────────────────────────────

export const sendNotification = (title: string, message: string) =>
  post<ApiResponse<unknown>>('/admin/notifications/send', { title, message })

export const sendNotificationToTarget = (title: string, message: string, target: string) =>
  post<ApiResponse<unknown>>('/admin/notifications/send', { title, message, target })

// ── System ────────────────────────────────────────────────────────────────────

export const fetchSystemMonitoring = () =>
  get<ApiResponse<unknown>>('/admin/system/monitoring')

export const fetchActivityLogs = () =>
  get<ApiResponse<unknown[]>>('/admin/activity-logs')

// ── CMS ───────────────────────────────────────────────────────────────────────

export const fetchCmsPages = () =>
  get<ApiResponse<unknown[]>>('/admin/cms/pages')

export const saveCmsPage = (slug: string, title: string, body: string, isPublished: boolean) =>
  post<ApiResponse<unknown>>('/admin/cms/pages', { slug, title, content: body, is_published: isPublished })

export const fetchCmsBanners = () =>
  get<ApiResponse<unknown[]>>('/admin/cms/banners')

// ── Live Monitoring ───────────────────────────────────────────────────────────

export const fetchLiveMonitoring = () =>
  get<ApiResponse<unknown>>('/admin/monitoring/live')

// ── Subscriptions ─────────────────────────────────────────────────────────────

export const fetchSubscriptions = (params?: Record<string, string | number>) =>
  get<ApiResponse<unknown>>(
    '/admin/subscriptions?' + new URLSearchParams(params as Record<string, string>).toString(),
  )

// ── Badges ────────────────────────────────────────────────────────────────────

export const fetchBadges = () =>
  get<ApiResponse<unknown[]>>('/admin/badges')

// ── Content Items ─────────────────────────────────────────────────────────────

export const fetchContentItems = (params?: Record<string, string | number>) =>
  get<ApiResponse<unknown[]>>(
    '/admin/content?' + new URLSearchParams((params ?? {}) as Record<string, string>).toString(),
  )
