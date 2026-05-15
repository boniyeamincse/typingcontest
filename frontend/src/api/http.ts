export type AuthUser = {
  id: number
  name: string
  email: string
  created_at?: string
  updated_at?: string
}

type AuthResponse = {
  message: string
  token: string
  user: AuthUser
}

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL?.replace(/\/$/, '') ??
  'http://127.0.0.1:8001/api/v1'

const TOKEN_KEY = 'typing_contest_token'

export function getStoredToken(): string | null {
  return localStorage.getItem(TOKEN_KEY)
}

export function setStoredToken(token: string | null): void {
  if (!token) {
    localStorage.removeItem(TOKEN_KEY)
    return
  }

  localStorage.setItem(TOKEN_KEY, token)
}

async function request<T>(
  path: string,
  options: RequestInit = {},
  token?: string | null,
): Promise<T> {
  const headers = new Headers(options.headers)
  headers.set('Accept', 'application/json')

  if (options.body) {
    headers.set('Content-Type', 'application/json')
  }

  if (token) {
    headers.set('Authorization', `Bearer ${token}`)
  }

  const response = await fetch(`${API_BASE_URL}${path}`, {
    ...options,
    headers,
  })

  const isJson = response.headers
    .get('content-type')
    ?.includes('application/json')

  const payload = isJson ? await response.json() : null

  if (!response.ok) {
    let message = 'Request failed'

    if (payload?.message && typeof payload.message === 'string') {
      message = payload.message
    } else if (payload?.errors && typeof payload.errors === 'object') {
      message = Object.values(payload.errors)
        .flat()
        .map((value) => String(value))
        .join(' ')
    }

    throw new Error(message)
  }

  return payload as T
}

export async function register(input: {
  username: string
  name: string
  email: string
  password: string
  password_confirmation: string
}): Promise<AuthResponse> {
  return request<AuthResponse>('/auth/register', {
    method: 'POST',
    body: JSON.stringify(input),
  })
}

export async function login(input: {
  email: string
  password: string
}): Promise<AuthResponse> {
  return request<AuthResponse>('/auth/login', {
    method: 'POST',
    body: JSON.stringify(input),
  })
}

export async function me(token: string): Promise<{ user: AuthUser }> {
  return request<{ user: AuthUser }>('/auth/me', { method: 'GET' }, token)
}

export async function logout(token: string): Promise<{ message: string }> {
  return request<{ message: string }>('/auth/logout', { method: 'POST' }, token)
}
