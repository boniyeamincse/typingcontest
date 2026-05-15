export type ContestType = 'daily' | 'weekly' | 'monthly' | 'special' | 'speed' | string
export type ContestStatus =
  | 'draft'
  | 'published'
  | 'active'
  | 'finished'
  | 'completed'
  | 'cancelled'
  | string

export type Contest = {
  id: number
  title: string
  type: ContestType
  status: ContestStatus
  textContent: string
  durationSeconds: number
  startsAt: string | null
  endsAt: string | null
  participantCount: number
  maxParticipants: number | null
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

export type AdminContestInput = {
  title: string
  type: 'daily' | 'weekly' | 'monthly' | 'special'
  typing_text_id: number
  start_time: string
  end_time: string
  max_participants?: number
  prize_description?: string
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
  'http://127.0.0.1:8001/api/v1'

function normalizeContest(raw: any): Contest {
  const textContent = String(raw?.text_content ?? raw?.typing_text?.content ?? '')
  const startsAt = (raw?.start_time ?? raw?.starts_at ?? null) as string | null
  const endsAt = (raw?.end_time ?? raw?.ends_at ?? null) as string | null

  return {
    id: Number(raw?.id ?? 0),
    title: String(raw?.title ?? 'Untitled Contest'),
    type: String(raw?.type ?? 'special'),
    status: String(raw?.status ?? 'draft'),
    textContent,
    durationSeconds: Number(raw?.duration_seconds ?? raw?.duration_minutes ? (raw.duration_minutes * 60) : 60),
    startsAt,
    endsAt,
    participantCount: Number(raw?.participant_count ?? 0),
    maxParticipants: raw?.max_participants != null ? Number(raw.max_participants) : null,
    created_by: raw?.created_by ?? null,
    created_at: String(raw?.created_at ?? ''),
    updated_at: String(raw?.updated_at ?? ''),
  }
}

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
  return request<any>(`/contests${qs ? `?${qs}` : ''}`).then((payload) => {
    const rows = Array.isArray(payload?.data) ? payload.data : []
    return {
      data: rows.map((row: any) => normalizeContest(row)),
      total: Number(payload?.total ?? rows.length),
      per_page: Number(payload?.per_page ?? rows.length),
      current_page: Number(payload?.current_page ?? 1),
      last_page: Number(payload?.last_page ?? 1),
    }
  })
}

export function getContest(id: number): Promise<Contest> {
  return request<any>(`/contests/${id}`).then((payload) => {
    if (payload?.contest) {
      return normalizeContest(payload.contest)
    }

    return normalizeContest(payload)
  })
}

export function getContestLeaderboard(
  id: number,
): Promise<{ contest: Contest; leaderboard: ContestResult[] }> {
  return request<any>(`/contests/${id}/leaderboard`).then((payload) => ({
    contest: payload?.contest ? normalizeContest(payload.contest) : normalizeContest({ id }),
    leaderboard: Array.isArray(payload?.leaderboard) ? payload.leaderboard : [],
  }))
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
  payload: AdminContestInput,
): Promise<Contest> {
  return request<any>(
    '/admin/contests',
    { method: 'POST', body: JSON.stringify(payload) },
    token,
  ).then((raw) => normalizeContest(raw?.contest ?? raw))
}

export function publishContest(
  id: number,
  token: string,
): Promise<{ message: string; contest: Contest }> {
  return request<any>(
    `/admin/contests/${id}/publish`,
    { method: 'POST' },
    token,
  ).then((raw) => ({
    message: String(raw?.message ?? 'Contest published successfully'),
    contest: normalizeContest(raw?.contest ?? { id }),
  }))
}

export function cancelContest(
  id: number,
  token: string,
): Promise<{ message: string }> {
  return request<{ message: string }>(
    `/admin/contests/${id}/cancel`,
    { method: 'POST' },
    token,
  )
}

export function deleteContest(
  id: number,
  token: string,
): Promise<{ message: string }> {
  return request<{ message: string }>(
    `/admin/contests/${id}`,
    { method: 'DELETE' },
    token,
  )
}

export function updateContest(
  id: number,
  token: string,
  payload: Partial<AdminContestInput>,
): Promise<Contest> {
  return request<any>(
    `/admin/contests/${id}`,
    { method: 'PUT', body: JSON.stringify(payload) },
    token,
  ).then((raw) => normalizeContest(raw?.contest ?? raw))
}

export function listAdminContests(
  token: string,
  params?: { status?: ContestStatus; page?: number },
): Promise<PaginatedResponse<Contest>> {
  const query = new URLSearchParams()
  if (params?.status) query.set('status', params.status)
  if (params?.page) query.set('page', String(params.page))
  const qs = query.toString()

  return request<any>(`/admin/contests${qs ? `?${qs}` : ''}`, { method: 'GET' }, token).then(
    (payload) => {
      const rows = Array.isArray(payload?.data) ? payload.data : []
      return {
        data: rows.map((row: any) => normalizeContest(row)),
        total: Number(payload?.total ?? rows.length),
        per_page: Number(payload?.per_page ?? rows.length),
        current_page: Number(payload?.current_page ?? 1),
        last_page: Number(payload?.last_page ?? 1),
      }
    },
  )
}
