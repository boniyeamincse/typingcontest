import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

declare global {
  interface Window {
    Pusher: typeof Pusher
  }
}

type EchoInstance = Echo<'pusher'>

let echo: EchoInstance | null = null

export function getEcho(token?: string | null): EchoInstance {
  if (echo) return echo

  window.Pusher = Pusher

  const wsHost = (import.meta.env.VITE_WS_HOST as string | undefined) ?? '127.0.0.1'
  const wsPort = Number((import.meta.env.VITE_WS_PORT as string | undefined) ?? 6001)
  const wsScheme = ((import.meta.env.VITE_WS_SCHEME as string | undefined) ?? 'http') as 'http' | 'https'
  const appKey = (import.meta.env.VITE_WS_APP_KEY as string | undefined) ?? 'app-key'
  const apiBase =
    (import.meta.env.VITE_API_BASE_URL as string | undefined)?.replace(/\/$/, '') ??
    'http://127.0.0.1:8001/api/v1'

  echo = new Echo({
    broadcaster: 'pusher',
    key: appKey,
    wsHost,
    wsPort,
    wssPort: wsPort,
    forceTLS: wsScheme === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: `${apiBase}/broadcasting/auth`,
    auth: token
      ? {
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json',
          },
        }
      : undefined,
  })

  return echo
}

export function disconnectEcho(): void {
  if (!echo) return
  echo.disconnect()
  echo = null
}
