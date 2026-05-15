const API_BASE_URL =
  (import.meta.env.VITE_API_BASE_URL as string | undefined)?.replace(/\/$/, '') ??
  'http://127.0.0.1:8001/api/v1'

export type TypingSession = {
  id: number
  session_uuid: string
  contest_id: number
  user_id: number
  status: 'pending' | 'countdown' | 'active' | 'submitted' | 'expired' | 'disqualified' | string
  duration_seconds: number
  countdown_seconds: number
  countdown_started_at: string | null
  started_at: string | null
  expires_at: string | null
  submitted_at: string | null
  auto_submitted: boolean
  is_flagged: boolean
  disqualified_reason: string | null
  last_sequence: number
}

export type TypingMetrics = {
  correct_words: number
  correct_characters: number
  total_characters: number
  errors: number
  wpm: number
  cpm: number
  accuracy: number
  progress_percent: number
  score: number
  duration_seconds: number
}

export type TypingResult = {
  id: number
  typing_session_id: number
  contest_id: number
  user_id: number
  correct_words: number
  correct_characters: number
  total_characters: number
  errors: number
  wpm: number
  cpm: number
  accuracy: number
  score: number
  progress_percent: number
  duration_seconds: number
  is_disqualified: boolean
  disqualified_reason: string | null
  meta: Record<string, unknown> | null
  created_at: string
}

export type TypingStatus = {
  session: TypingSession
  live: Partial<TypingMetrics> & { sequence?: number; typed_text?: string }
}

type ApiEnvelope<T> = {
  success: boolean
  message: string
  data: T
}

async function request<T>(path: string, token: string, options: RequestInit = {}): Promise<T> {
  const headers = new Headers(options.headers)
  headers.set('Accept', 'application/json')
  headers.set('Authorization', `Bearer ${token}`)
  if (options.body) headers.set('Content-Type', 'application/json')

  const res = await fetch(`${API_BASE_URL}${path}`, { ...options, headers })
  const payload = (await res.json()) as Partial<ApiEnvelope<T>> & { errors?: Record<string, string[]> }

  if (!res.ok) {
    const fromErrors = payload.errors
      ? Object.values(payload.errors).flat().join(' ')
      : ''
    throw new Error(payload.message || fromErrors || 'Typing request failed')
  }

  return payload.data as T
}

export function startTypingSession(
  token: string,
  payload: { contest_id: number; device_fingerprint?: string },
): Promise<TypingSession> {
  return request<TypingSession>('/typing/start', token, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function updateTypingSession(
  token: string,
  payload: {
    session_id: number
    sequence: number
    typed_text: string
    elapsed_ms: number
    cursor_position?: number
    sync_interval_ms?: number
    payload?: Record<string, unknown>
    error_event?: boolean
    word_index?: number
    char_index?: number
    expected_char?: string
    typed_char?: string
    error_type?: string
    paste_detected?: boolean
    tab_switched?: boolean
    focus_lost?: boolean
    bot_pattern?: boolean
    previous_wpm?: number
    device_fingerprint?: string
  },
): Promise<{ session: TypingSession; metrics: TypingMetrics; anti_cheat: { flagged: boolean; flags: string[] } }> {
  return request('/typing/update', token, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function submitTypingSession(
  token: string,
  payload: {
    session_id: number
    typed_text: string
    elapsed_ms: number
    auto_submit?: boolean
    paste_detected?: boolean
    tab_switched?: boolean
    focus_lost?: boolean
    bot_pattern?: boolean
    previous_wpm?: number
    device_fingerprint?: string
  },
): Promise<{
  session: TypingSession
  result: TypingResult
  metrics: TypingMetrics
  anti_cheat: { flagged: boolean; flags: string[] }
}> {
  return request('/typing/submit', token, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function getTypingStatus(token: string, sessionId: number): Promise<TypingStatus> {
  return request(`/typing/status/${sessionId}`, token)
}

export function getTypingResult(token: string, resultId: number): Promise<TypingResult> {
  return request(`/typing/result/${resultId}`, token)
}
