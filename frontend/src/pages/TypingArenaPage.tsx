import { useCallback, useEffect, useRef, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { getContest } from '../api/contests'
import type { Contest } from '../api/contests'
import {
  startTypingSession,
  submitTypingSession,
  updateTypingSession,
  type TypingMetrics,
  type TypingSession,
} from '../api/typing'
import { useAuth } from '../auth/AuthContext'
import { AppShell } from '../components/AppShell'
import { getEcho } from '../realtime/echo'
import { subscribeTypingChannels } from '../realtime/typingRealtime'
import './TypingArenaPage.css'

type Phase = 'loading' | 'ready' | 'countdown' | 'running' | 'finished' | 'error'

export default function TypingArenaPage() {
  const { id } = useParams<{ id: string }>()
  const { token, user } = useAuth()
  const navigate = useNavigate()

  const [contest, setContest] = useState<Contest | null>(null)
  const [session, setSession] = useState<TypingSession | null>(null)
  const [phase, setPhase] = useState<Phase>('loading')
  const [error, setError] = useState('')

  const [typed, setTyped] = useState('')
  const [timeLeft, setTimeLeft] = useState(0)
  const [countdownLeft, setCountdownLeft] = useState(0)
  const [liveMetrics, setLiveMetrics] = useState<TypingMetrics | null>(null)

  const [finalWpm, setFinalWpm] = useState(0)
  const [finalAccuracy, setFinalAccuracy] = useState(0)
  const [finalErrors, setFinalErrors] = useState(0)
  const [score, setScore] = useState<number | null>(null)
  const [submitting, setSubmitting] = useState(false)

  const inputRef = useRef<HTMLTextAreaElement>(null)
  const timerRef = useRef<ReturnType<typeof setInterval> | null>(null)
  const countdownRef = useRef<ReturnType<typeof setInterval> | null>(null)
  const syncRef = useRef<ReturnType<typeof setInterval> | null>(null)
  const unsubscribeRef = useRef<(() => void) | null>(null)

  const typedRef = useRef('')
  const startTimeRef = useRef<number | null>(null)
  const sequenceRef = useRef(0)
  const previousWpmRef = useRef(0)
  const sessionRef = useRef<TypingSession | null>(null)

  const [focusLostFlag, setFocusLostFlag] = useState(false)
  const [tabSwitchFlag, setTabSwitchFlag] = useState(false)

  const contestId = Number(id)

  useEffect(() => {
    sessionRef.current = session
  }, [session])

  useEffect(() => {
    if (!token) {
      navigate('/login')
      return
    }

    if (!contestId) return

    getContest(contestId)
      .then((c) => {
        if (c.status !== 'active') {
          setError('This contest is not currently active.')
          setPhase('error')
          return
        }

        setContest(c)
        setTimeLeft(c.durationSeconds)
        setPhase('ready')
      })
      .catch((e: unknown) => {
        setError(e instanceof Error ? e.message : 'Failed to load contest')
        setPhase('error')
      })
  }, [contestId, token, navigate])

  useEffect(() => {
    function onVisibility(): void {
      if (document.hidden) setTabSwitchFlag(true)
    }

    function onBlur(): void {
      setFocusLostFlag(true)
    }

    document.addEventListener('visibilitychange', onVisibility)
    window.addEventListener('blur', onBlur)

    return () => {
      document.removeEventListener('visibilitychange', onVisibility)
      window.removeEventListener('blur', onBlur)
    }
  }, [])

  const getDeviceFingerprint = useCallback((): string => {
    const fp = [
      navigator.userAgent,
      navigator.language,
      String(screen.width),
      String(screen.height),
      String(new Date().getTimezoneOffset()),
    ].join('|')

    return btoa(fp).slice(0, 64)
  }, [])

  const computeStats = useCallback(
    (typedText: string, target: string) => {
      const words = typedText.trim().split(/\s+/).filter(Boolean).length
      const elapsedMinutes = startTimeRef.current
        ? (Date.now() - startTimeRef.current) / 60000
        : 0
      const wpm = elapsedMinutes > 0 ? Math.round(words / elapsedMinutes) : 0

      let errors = 0
      for (let i = 0; i < typedText.length; i++) {
        if (typedText[i] !== target[i]) errors++
      }

      const acc =
        typedText.length > 0
          ? Math.max(0, ((typedText.length - errors) / typedText.length) * 100)
          : 100

      return { wpm, errors, accuracy: Math.round(acc * 100) / 100 }
    },
    [],
  )

  const finishRound = useCallback(
    async (typedText: string) => {
      if (!contest || !token || !sessionRef.current) return

      if (timerRef.current) clearInterval(timerRef.current)
      if (syncRef.current) clearInterval(syncRef.current)
      setPhase('finished')

      const local = computeStats(typedText, contest.textContent)
      setFinalWpm(local.wpm)
      setFinalErrors(local.errors)
      setFinalAccuracy(local.accuracy)

      setSubmitting(true)
      try {
        const payload = await submitTypingSession(token, {
          session_id: sessionRef.current.id,
          typed_text: typedText,
          elapsed_ms: startTimeRef.current
            ? Date.now() - startTimeRef.current
            : contest.durationSeconds * 1000,
          previous_wpm: previousWpmRef.current,
          focus_lost: focusLostFlag,
          tab_switched: tabSwitchFlag,
          device_fingerprint: getDeviceFingerprint(),
        })

        setFinalWpm(payload.result.wpm)
        setFinalErrors(payload.result.errors)
        setFinalAccuracy(payload.result.accuracy)
        setScore(payload.result.score)
      } catch {
        // keep local fallback values
      } finally {
        setSubmitting(false)
      }
    },
    [contest, token, computeStats, focusLostFlag, tabSwitchFlag, getDeviceFingerprint],
  )

  const startRunningState = useCallback(
    (sessionDurationSeconds: number) => {
      setTyped('')
      typedRef.current = ''
      setLiveMetrics(null)
      setPhase('running')
      setTimeLeft(sessionDurationSeconds)
      startTimeRef.current = Date.now()
      inputRef.current?.focus()

      timerRef.current = setInterval(() => {
        setTimeLeft((prev) => {
          if (prev <= 1) {
            clearInterval(timerRef.current!)
            void finishRound(typedRef.current)
            return 0
          }
          return prev - 1
        })
      }, 1000)

      syncRef.current = setInterval(() => {
        const activeSession = sessionRef.current
        if (!activeSession || !startTimeRef.current || !contest || !token) return

        sequenceRef.current += 1

        const currentTyped = typedRef.current
        const elapsedMs = Date.now() - startTimeRef.current
        const charIndex = Math.max(0, currentTyped.length - 1)
        const expectedChar = contest.textContent[charIndex] ?? ''
        const typedChar = currentTyped[charIndex] ?? ''
        const isMismatch = typedChar.length > 0 && expectedChar !== typedChar

        void updateTypingSession(token, {
          session_id: activeSession.id,
          sequence: sequenceRef.current,
          typed_text: currentTyped,
          elapsed_ms: elapsedMs,
          cursor_position: currentTyped.length,
          sync_interval_ms: 300,
          previous_wpm: previousWpmRef.current,
          focus_lost: focusLostFlag,
          tab_switched: tabSwitchFlag,
          device_fingerprint: getDeviceFingerprint(),
          error_event: isMismatch,
          char_index: charIndex,
          expected_char: expectedChar,
          typed_char: typedChar,
          error_type: isMismatch ? 'mismatch' : undefined,
        })
          .then((response) => {
            setLiveMetrics(response.metrics)
            previousWpmRef.current = response.metrics.wpm

            if (response.metrics.progress_percent >= 100) {
              void finishRound(currentTyped.slice(0, contest.textContent.length))
            }
          })
          .catch(() => {
            // Keep local experience responsive if sync ping fails.
          })
      }, 300)
    },
    [contest, token, finishRound, focusLostFlag, tabSwitchFlag, getDeviceFingerprint],
  )

  const startRound = useCallback(async () => {
    if (!token || !contest || !user) return

    setSubmitting(true)
    try {
      const startedSession = await startTypingSession(token, {
        contest_id: contest.id,
        device_fingerprint: getDeviceFingerprint(),
      })

      setSession(startedSession)
      setCountdownLeft(startedSession.countdown_seconds)
      setPhase('countdown')
      sequenceRef.current = 0
      previousWpmRef.current = 0

      const echo = getEcho(token)
      unsubscribeRef.current?.()
      unsubscribeRef.current = subscribeTypingChannels(
        echo,
        {
          sessionId: startedSession.id,
          contestId: startedSession.contest_id,
          userId: user.id,
        },
        {
          onUpdate: (payload) => {
            const maybeMetrics = (payload as { payload?: TypingMetrics })?.payload
            if (!maybeMetrics) return
            setLiveMetrics(maybeMetrics)
            previousWpmRef.current = maybeMetrics.wpm
          },
          onProgress: (payload) => {
            const maybeProgress = (payload as { progress?: TypingMetrics })?.progress
            if (maybeProgress) setLiveMetrics(maybeProgress)
          },
          onFinished: () => {
            if (phase !== 'finished') {
              void finishRound(typedRef.current)
            }
          },
        },
      )

      countdownRef.current = setInterval(() => {
        setCountdownLeft((prev) => {
          if (prev <= 1) {
            clearInterval(countdownRef.current!)
            startRunningState(startedSession.duration_seconds)
            return 0
          }

          return prev - 1
        })
      }, 1000)
    } catch (e: unknown) {
      setError(e instanceof Error ? e.message : 'Failed to start typing session')
      setPhase('error')
    } finally {
      setSubmitting(false)
    }
  }, [contest, token, user, finishRound, phase, startRunningState, getDeviceFingerprint])

  function handleInput(e: React.ChangeEvent<HTMLTextAreaElement>): void {
    if (!contest) return

    const val = e.target.value
    setTyped(val)
    typedRef.current = val

    if (val.length >= contest.textContent.length) {
      void finishRound(val.slice(0, contest.textContent.length))
    }
  }

  useEffect(
    () => () => {
      if (timerRef.current) clearInterval(timerRef.current)
      if (countdownRef.current) clearInterval(countdownRef.current)
      if (syncRef.current) clearInterval(syncRef.current)
      unsubscribeRef.current?.()
    },
    [],
  )

  if (phase === 'loading') return <p className="arena-center">Loading…</p>
  if (phase === 'error') return <p className="arena-center error-msg">{error}</p>
  if (!contest) return null

  const target = contest.textContent
  const localLive = phase === 'running' ? computeStats(typed, target) : null

  function renderPassage() {
    return target.split('').map((char, i) => {
      let cls = 'char--pending'
      if (i < typed.length) {
        cls = typed[i] === char ? 'char--correct' : 'char--wrong'
      } else if (i === typed.length) {
        cls = 'char--cursor'
      }

      return (
        <span key={i} className={cls}>
          {char}
        </span>
      )
    })
  }

  return (
    <AppShell
      title="Typing Arena"
      subtitle="Live-synced typing with real-time progress, anti-cheat tracking, and auto submission."
    >
      <div className="typing-arena surface-card">
        <header className="arena-header">
          <h1>{contest.title}</h1>
          <div className={`arena-timer ${timeLeft <= 10 && phase === 'running' ? 'arena-timer--urgent' : ''}`}>
            {phase === 'running' ? `${timeLeft}s` : `${contest.durationSeconds}s`}
          </div>
        </header>

        {phase === 'ready' ? (
          <div className="arena-ready">
            <p>
              Press <strong>Start</strong> and wait for the backend countdown signal.
            </p>
            <div className="arena-passage arena-passage--preview">{target}</div>
            <button className="btn-start" onClick={() => void startRound()} disabled={submitting}>
              {submitting ? 'Starting...' : 'Start'}
            </button>
          </div>
        ) : null}

        {phase === 'countdown' ? (
          <div className="arena-ready">
            <p>Round begins in:</p>
            <div className="arena-countdown">{countdownLeft}</div>
          </div>
        ) : null}

        {phase === 'running' ? (
          <>
            <div className="arena-stats-live">
              <span>
                WPM: <strong>{liveMetrics?.wpm ?? localLive?.wpm ?? 0}</strong>
              </span>
              <span>
                CPM: <strong>{liveMetrics?.cpm ?? 0}</strong>
              </span>
              <span>
                Accuracy: <strong>{(liveMetrics?.accuracy ?? localLive?.accuracy ?? 0).toFixed(1)}%</strong>
              </span>
              <span>
                Errors: <strong>{liveMetrics?.errors ?? localLive?.errors ?? 0}</strong>
              </span>
              <span>
                Progress: <strong>{liveMetrics?.progress_percent ?? 0}%</strong>
              </span>
            </div>

            <div className="arena-passage" aria-hidden="true">
              {renderPassage()}
            </div>

            <textarea
              ref={inputRef}
              className="arena-input"
              value={typed}
              onChange={handleInput}
              placeholder="Start typing here..."
              rows={4}
              spellCheck={false}
              autoCorrect="off"
              autoCapitalize="off"
            />
          </>
        ) : null}

        {phase === 'finished' ? (
          <div className="arena-results">
            <h2>Round Complete!</h2>
            <div className="results-grid">
              <div className="result-card">
                <span className="result-label">WPM</span>
                <span className="result-value">{finalWpm}</span>
              </div>
              <div className="result-card">
                <span className="result-label">Accuracy</span>
                <span className="result-value">{finalAccuracy.toFixed(1)}%</span>
              </div>
              <div className="result-card">
                <span className="result-label">Errors</span>
                <span className="result-value">{finalErrors}</span>
              </div>
              {score !== null ? (
                <div className="result-card result-card--highlight">
                  <span className="result-label">Score</span>
                  <span className="result-value">{score}</span>
                </div>
              ) : null}
            </div>

            {submitting ? <p className="submitting-msg">Saving result...</p> : null}

            <div className="arena-actions">
              <button className="btn-secondary" onClick={() => navigate(`/contests/${contest.id}`)}>
                View Leaderboard
              </button>
              <button className="btn-secondary" onClick={() => navigate('/contests')}>
                All Contests
              </button>
            </div>
          </div>
        ) : null}
      </div>
    </AppShell>
  )
}
