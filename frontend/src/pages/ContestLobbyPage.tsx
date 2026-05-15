import { useEffect, useRef, useState, useCallback } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import { getContest, joinContest } from '../api/contests'
import type { Contest } from '../api/contests'
import { useAuth } from '../auth/AuthContext'
import { AppShell } from '../components/AppShell'
import { getEcho } from '../realtime/echo'
import './ContestLobbyPage.css'

type Joiner = {
  userId: number
  username: string
  avatar: string | null
  joinedAt: number // Date.now()
}

function useCountdown(targetIso: string | null): string {
  const [display, setDisplay] = useState('')

  useEffect(() => {
    if (!targetIso) {
      setDisplay('')
      return
    }

    function tick() {
      const diff = new Date(targetIso!).getTime() - Date.now()
      if (diff <= 0) {
        setDisplay('Starting…')
        return
      }
      const h = Math.floor(diff / 3_600_000)
      const m = Math.floor((diff % 3_600_000) / 60_000)
      const s = Math.floor((diff % 60_000) / 1_000)
      const parts = []
      if (h > 0) parts.push(`${h}h`)
      if (h > 0 || m > 0) parts.push(`${m}m`)
      parts.push(`${s}s`)
      setDisplay(parts.join(' '))
    }

    tick()
    const id = setInterval(tick, 1_000)
    return () => clearInterval(id)
  }, [targetIso])

  return display
}

export default function ContestLobbyPage() {
  const { id } = useParams<{ id: string }>()
  const { token, user } = useAuth()
  const navigate = useNavigate()
  const contestId = Number(id)

  const [contest, setContest] = useState<Contest | null>(null)
  const [participantCount, setParticipantCount] = useState(0)
  const [joiners, setJoiners] = useState<Joiner[]>([])
  const [phase, setPhase] = useState<'loading' | 'waiting' | 'starting' | 'error'>('loading')
  const [error, setError] = useState('')
  const [joinError, setJoinError] = useState('')

  const unsubRef = useRef<(() => void) | null>(null)
  const pollRef = useRef<ReturnType<typeof setInterval> | null>(null)
  const navigatedRef = useRef(false)

  const countdown = useCountdown(contest?.startsAt ?? null)

  // Navigate to play — called once
  const goToPlay = useCallback(() => {
    if (navigatedRef.current) return
    navigatedRef.current = true
    setPhase('starting')
    setTimeout(() => navigate(`/contests/${contestId}/play`), 1_200)
  }, [contestId, navigate])

  // Subscribe to WebSocket lobby channel
  const subscribeWs = useCallback((token: string | null) => {
    const echo = getEcho(token)
    const channel = echo.channel(`contest.${contestId}`)

    channel.listen('.participant.joined', (payload: any) => {
      const count = Number(payload?.participant_count ?? 0)
      if (count > 0) setParticipantCount(count)

      const joiner: Joiner = {
        userId:   Number(payload?.user_id ?? 0),
        username: String(payload?.username ?? 'Unknown'),
        avatar:   payload?.avatar ?? null,
        joinedAt: Date.now(),
      }
      setJoiners(prev => [joiner, ...prev].slice(0, 8))
    })

    channel.listen('.contest.started', () => {
      goToPlay()
    })

    channel.listen('.contest.ended', () => {
      navigate(`/contests/${contestId}`)
    })

    return () => {
      echo.leave(`contest.${contestId}`)
    }
  }, [contestId, goToPlay, navigate])

  // Load contest + join
  useEffect(() => {
    if (!token) {
      navigate('/login')
      return
    }
    if (!contestId) return

    async function init() {
      try {
        // 1. Load contest details
        const c = await getContest(contestId)
        setContest(c)
        setParticipantCount(c.participantCount)

        // 2. If already active → go straight to play
        if (c.status === 'active') {
          goToPlay()
          return
        }

        if (c.status === 'finished' || c.status === 'cancelled') {
          setError(`This contest has ${c.status}.`)
          setPhase('error')
          return
        }

        // 3. Join the contest (idempotent — "already joined" is OK)
        try {
          const res = await joinContest(contestId, token!)
          // Update participant count from join response if available
          if (res?.contest?.participantCount) {
            setParticipantCount(res.contest.participantCount)
          }
        } catch (e) {
          if (e instanceof Error && !e.message.toLowerCase().includes('already')) {
            setJoinError(e.message)
          }
        }

        setPhase('waiting')

        // 4. Subscribe WebSocket
        unsubRef.current = subscribeWs(token!)

        // 5. Polling fallback: re-check contest status every 5s
        pollRef.current = setInterval(async () => {
          try {
            const fresh = await getContest(contestId)
            setContest(fresh)
            setParticipantCount(fresh.participantCount)
            if (fresh.status === 'active') goToPlay()
            if (fresh.status === 'finished' || fresh.status === 'cancelled') {
              clearInterval(pollRef.current!)
              setError(`Contest has ${fresh.status}.`)
              setPhase('error')
            }
          } catch {}
        }, 5_000)

      } catch (e) {
        setError(e instanceof Error ? e.message : 'Failed to load contest.')
        setPhase('error')
      }
    }

    init()

    return () => {
      unsubRef.current?.()
      if (pollRef.current) clearInterval(pollRef.current)
    }
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [contestId])

  // ── Render ──────────────────────────────────────────────────────────────────

  if (phase === 'loading') {
    return (
      <div className="lobby-loading">
        <div className="lobby-spinner" />
        <p>Entering lobby…</p>
      </div>
    )
  }

  if (phase === 'error') {
    return (
      <AppShell title="Contest Lobby">
        <div className="lobby-error">
          <p>{error}</p>
          <Link to="/contests" className="btn-secondary">Back to contests</Link>
        </div>
      </AppShell>
    )
  }

  if (phase === 'starting') {
    return (
      <div className="lobby-starting-overlay">
        <div className="lobby-starting-content">
          <div className="lobby-flash-ring" />
          <h1>Get Ready!</h1>
          <p>Contest is starting…</p>
        </div>
      </div>
    )
  }

  const maxLabel = contest?.maxParticipants
    ? ` / ${contest.maxParticipants}`
    : ''

  const statusColor =
    contest?.status === 'active' ? 'badge--active' :
    contest?.status === 'published' ? 'badge--published' : 'badge--draft'

  return (
    <AppShell
      title="Waiting Room"
      subtitle={`Lobby for: ${contest?.title ?? ''}`}
      actions={
        <Link to={`/contests/${contestId}`} className="btn-secondary">
          ← Contest Details
        </Link>
      }
    >
      <div className="lobby-wrapper">

        {/* ── Left panel ── */}
        <div className="lobby-left">

          {/* Contest card */}
          <div className="lobby-card lobby-info-card">
            <div className="lobby-badges">
              <span className={`badge ${statusColor}`}>
                {contest?.status === 'active' ? 'Live' : 'Upcoming'}
              </span>
              <span className="badge badge--type">{contest?.type}</span>
            </div>
            <h2 className="lobby-contest-title">{contest?.title}</h2>
            <div className="lobby-meta-row">
              <span>
                {contest?.durationSeconds
                  ? `${contest.durationSeconds}s round`
                  : contest?.startsAt
                    ? null
                    : 'Duration TBD'}
              </span>
              {contest?.startsAt ? (
                <span>Starts {new Date(contest.startsAt).toLocaleString()}</span>
              ) : null}
            </div>
          </div>

          {/* Countdown */}
          {contest?.startsAt && (
            <div className="lobby-card lobby-countdown-card">
              <p className="lobby-countdown-label">Starts in</p>
              <div className="lobby-countdown-digits">{countdown || '—'}</div>
            </div>
          )}

          {/* Participant count */}
          <div className="lobby-card lobby-participants-card">
            <p className="lobby-participants-label">Participants</p>
            <div className="lobby-participants-count">
              {participantCount}{maxLabel}
            </div>
            {contest?.maxParticipants ? (
              <div className="lobby-progress-bar">
                <div
                  className="lobby-progress-fill"
                  style={{
                    width: `${Math.min(100, (participantCount / contest.maxParticipants) * 100)}%`,
                  }}
                />
              </div>
            ) : null}
          </div>

          {/* Blurred text preview */}
          {contest?.textContent && contest.status !== 'published' ? (
            <div className="lobby-card lobby-text-preview">
              <p className="lobby-preview-label">Typing Passage</p>
              <blockquote className="lobby-text-blur">{contest.textContent}</blockquote>
            </div>
          ) : (
            <div className="lobby-card lobby-text-preview lobby-text-hidden">
              <p className="lobby-preview-label">Typing Passage</p>
              <div className="lobby-text-blur-placeholder">
                <span>Hidden until contest starts</span>
              </div>
            </div>
          )}

          {joinError && (
            <p className="lobby-join-error">{joinError}</p>
          )}
        </div>

        {/* ── Right panel: joiner feed ── */}
        <div className="lobby-right">
          <div className="lobby-card lobby-feed-card">
            <h3 className="lobby-feed-title">Recent Joiners</h3>
            {joiners.length === 0 ? (
              <p className="lobby-feed-empty">Waiting for participants…</p>
            ) : (
              <ul className="lobby-feed-list">
                {joiners.map((j, i) => (
                  <li key={`${j.userId}-${j.joinedAt}`} className={`lobby-feed-item${i === 0 ? ' lobby-feed-item--new' : ''}`}>
                    <div className="lobby-avatar">
                      {j.avatar
                        ? <img src={j.avatar} alt={j.username} />
                        : <span>{j.username.charAt(0).toUpperCase()}</span>}
                    </div>
                    <span className="lobby-feed-username">{j.username}</span>
                    <span className="lobby-feed-time">just now</span>
                  </li>
                ))}
              </ul>
            )}

            {/* Pulsing "live" indicator */}
            <div className="lobby-live-badge">
              <span className="lobby-live-dot" />
              Live lobby
            </div>
          </div>

          {/* Tips */}
          <div className="lobby-card lobby-tips-card">
            <h3>While you wait…</h3>
            <ul className="lobby-tips-list">
              <li>Warm up your fingers — accuracy beats speed.</li>
              <li>Find a comfortable typing position.</li>
              <li>Close distracting tabs — focus wins.</li>
              <li>The contest auto-starts when time comes.</li>
            </ul>
          </div>

          {/* Who you are */}
          {user && (
            <div className="lobby-card lobby-you-card">
              <p className="lobby-you-label">Competing as</p>
              <div className="lobby-you-row">
                <div className="lobby-avatar lobby-avatar--lg">
                  {user.avatar
                    ? <img src={user.avatar} alt={user.name} />
                    : <span>{(user.name || user.email).charAt(0).toUpperCase()}</span>}
                </div>
                <div>
                  <p className="lobby-you-name">{user.name}</p>
                  <p className="lobby-you-sub">Good luck!</p>
                </div>
              </div>
            </div>
          )}
        </div>
      </div>
    </AppShell>
  )
}
