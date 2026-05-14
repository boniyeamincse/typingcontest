export type ContestType = 'daily' | 'weekly' | 'monthly' | 'special'
export type ContestStatus = 'draft' | 'published' | 'active' | 'completed'

export type Contest = {
  id: number
  title: string
  type: ContestType
  status: ContestStatus
  text_content: string
  duration_seconds: number
  starts_at: string | null
  ends_at: string | null
  created_by: number | null
  created_at: string
  updated_at: string
}

export type ContestResult = {
  id: number
  user_id: number
  contest_id: number
  wpm: number
  accuracy: number
  errors: number
  score: number
  rank: number | null
  user?: { id: number; name: string }
}

export type PaginatedResponse<T> = {
  data: T[]
  total: number
  per_page: number
  current_page: number
  last_page: number
}

const API_BASE_URL =
  (import.meta.env.VITE_API_BASE_URL as string | undefined)?.replace(/\/$/, '') ??
  'http://127.0.0.1:8000/api'

async function request<T>(
  path: string,
  options: RequestInit = {},
  token?: string | null,
): Promise<T> {
  const headers = new Headers(options.headers)
  headers.set('Accept', 'application/json')
  if (options.body) headers.set('Content-Type', 'application/json')
  if (token) headers.set('Authorization', `Bearer ${token}`)

  const res = await fetch(`${API_BASE_URL}${path}`, { ...options, headers })
  const isJson = res.headers.get('content-type')?.includes('application/json')
  const payload = isJson ? await res.json() : null

  if (!res.ok) {
    let message = 'Request failed'
    if (payload?.message && typeof payload.message === 'string') {
      message = payload.message
    } else if (payload?.errors && typeof payload.errors === 'object') {
      message = Object.values(payload.errors)
        .flat()
        .map((v) => String(v))
        .join(' ')
    }
    throw new Error(message)
  }

  return payload as T
}

export function listContests(params?: {
  status?: ContestStatus
  type?: ContestType
  page?: number
}): Promise<PaginatedResponse<Contest>> {
  const query = new URLSearchParams()
  if (params?.status) query.set('status', params.status)
  if (params?.type) query.set('type', params.type)
  if (params?.page) query.set('page', String(params.page))
  const qs = query.toString()
  return request<PaginatedResponse<Contest>>(`/contests${qs ? `?${qs}` : ''}`)
}

export function getContest(id: number): Promise<Contest> {
  return request<Contest>(`/contests/${id}`)
}

export function getContestLeaderboard(
  id: number,
): Promise<{ contest: Contest; leaderboard: ContestResult[] }> {
  return request<{ contest: Contest; leaderboard: ContestResult[] }>(
    `/contests/${id}/leaderboard`,
  )
}

export function getGlobalLeaderboard(): Promise<ContestResult[]> {
  return request<ContestResult[]>('/leaderboard')
}

export function joinContest(
  id: number,
  token: string,
): Promise<{ message: string; contest: Contest }> {
  return request<{ message: string; contest: Contest }>(
    `/contests/${id}/join`,
    { method: 'POST' },
    token,
  )
}

export function submitResult(
  id: number,
  token: string,
  payload: { wpm: number; accuracy: number; errors: number },
): Promise<{ message: string; result: ContestResult }> {
  return request<{ message: string; result: ContestResult }>(
    `/contests/${id}/submit`,
    { method: 'POST', body: JSON.stringify(payload) },
    token,
  )
}

export function createContest(
  token: string,
  payload: {
    title: string
    type: ContestType
    text_content: string
    duration_seconds: number
    starts_at?: string
    ends_at?: string
  },
): Promise<Contest> {
  return request<Contest>(
    '/admin/contests',
    { method: 'POST', body: JSON.stringify(payload) },
    token,
  )
}

export function publishContest(
  id: number,
  token: string,
): Promise<{ message: string; contest: Contest }> {
  return request<{ message: string; contest: Contest }>(
    `/admin/contests/${id}/publish`,
    { method: 'POST' },
    token,
  )
}
