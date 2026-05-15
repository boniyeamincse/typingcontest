import type Echo from 'laravel-echo'

type TypingEventHandlers = {
  onStarted?: (payload: unknown) => void
  onUpdate?: (payload: unknown) => void
  onError?: (payload: unknown) => void
  onProgress?: (payload: unknown) => void
  onFinished?: (payload: unknown) => void
}

export function subscribeTypingChannels(
  echo: Echo<'pusher'>,
  params: { sessionId: number; contestId: number; userId: number },
  handlers: TypingEventHandlers,
): () => void {
  const sessionChannel = `typing.session.${params.sessionId}`
  const leaderboardChannel = `typing.leaderboard.${params.contestId}`
  const userChannel = `typing.user.${params.userId}`

  echo.channel(sessionChannel)
    .listen('.typing.started', (payload: unknown) => handlers.onStarted?.(payload))
    .listen('.typing.update', (payload: unknown) => handlers.onUpdate?.(payload))
    .listen('.typing.error', (payload: unknown) => handlers.onError?.(payload))
    .listen('.typing.progress', (payload: unknown) => handlers.onProgress?.(payload))
    .listen('.typing.finished', (payload: unknown) => handlers.onFinished?.(payload))

  echo.channel(leaderboardChannel)
    .listen('.typing.progress', (payload: unknown) => handlers.onProgress?.(payload))
    .listen('.typing.finished', (payload: unknown) => handlers.onFinished?.(payload))

  echo.channel(userChannel)
    .listen('.typing.started', (payload: unknown) => handlers.onStarted?.(payload))
    .listen('.typing.update', (payload: unknown) => handlers.onUpdate?.(payload))
    .listen('.typing.finished', (payload: unknown) => handlers.onFinished?.(payload))

  return () => {
    echo.leave(sessionChannel)
    echo.leave(leaderboardChannel)
    echo.leave(userChannel)
  }
}
