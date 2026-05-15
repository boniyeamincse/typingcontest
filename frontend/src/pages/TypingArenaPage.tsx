import { useCallback, useEffect, useRef, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { getContest, submitResult } from '../api/contests'
import type { Contest } from '../api/contests'
import { useAuth } from '../auth/AuthContext'
import { AppShell } from '../components/AppShell'
import './TypingArenaPage.css'

type Phase = 'loading' | 'ready' | 'running' | 'finished' | 'error'

export default function TypingArenaPage() {
  const { id } = useParams<{ id: string }>()
  const { token } = useAuth()
  const navigate = useNavigate()

  const [contest, setContest] = useState<Contest | null>(null)
  const [phase, setPhase] = useState<Phase>('loading')
  const [error, setError] = useState('')

  // Typing state
  const [typed, setTyped] = useState('')
  const [timeLeft, setTimeLeft] = useState(0)
  const [startTime, setStartTime] = useState<number | null>(null)
  const timerRef = useRef<ReturnType<typeof setInterval> | null>(null)
  const inputRef = useRef<HTMLTextAreaElement>(null)

  // Stats (computed live)
  const [finalWpm, setFinalWpm] = useState(0)
  const [finalAccuracy, setFinalAccuracy] = useState(0)
  const [finalErrors, setFinalErrors] = useState(0)
  const [submitting, setSubmitting] = useState(false)
  const [score, setScore] = useState<number | null>(null)
  const [rank, setRank] = useState<number | null>(null)

  const contestId = Number(id)

  useEffect(() => {
    if (!token) { navigate('/login'); return }
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

  // Compute live stats
  function computeStats(typedText: string, target: string) {
    const words = typedText.trim().split(/\s+/).filter(Boolean).length
    const elapsedMinutes = startTime ? (Date.now() - startTime) / 60000 : 0
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
  }

  const finishRound = useCallback(
    async (typedText: string) => {
      if (!contest || !token) return
      if (timerRef.current) clearInterval(timerRef.current)
      setPhase('finished')

      const { wpm, errors, accuracy } = computeStats(typedText, contest.textContent)
      setFinalWpm(wpm)
      setFinalErrors(errors)
      setFinalAccuracy(accuracy)

      setSubmitting(true)
      try {
        const res = await submitResult(contest.id, token, { wpm, accuracy, errors })
        setScore(res.result.score)
        setRank(res.result.rank)
      } catch {
        // Already submitted or other error — just show stats
      } finally {
        setSubmitting(false)
      }
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [contest, token],
  )

  function startRound() {
    setTyped('')
    setStartTime(Date.now())
    setPhase('running')
    inputRef.current?.focus()

    timerRef.current = setInterval(() => {
      setTimeLeft((prev) => {
        if (prev <= 1) {
          clearInterval(timerRef.current!)
          finishRound(typed)
          return 0
        }
        return prev - 1
      })
    }, 1000)
  }

  function handleInput(e: React.ChangeEvent<HTMLTextAreaElement>) {
    const val = e.target.value
    setTyped(val)

    if (contest && val.length >= contest.textContent.length) {
      finishRound(val.slice(0, contest.textContent.length))
    }
  }

  useEffect(() => () => { if (timerRef.current) clearInterval(timerRef.current) }, [])

  if (phase === 'loading') return <p className="arena-center">Loading…</p>
  if (phase === 'error') return <p className="arena-center error-msg">{error}</p>
  if (!contest) return null

  const target = contest.textContent
  const liveStats =
    phase === 'running' ? computeStats(typed, target) : null

  // Render coloured characters
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
      subtitle="Stay accurate under pressure. Results submit automatically when the round ends."
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
              Read the passage below, then press <strong>Start</strong> when ready.
            </p>
            <div className="arena-passage arena-passage--preview">{target}</div>
            <button className="btn-start" onClick={startRound}>
              Start
            </button>
          </div>
        ) : null}

        {phase === 'running' ? (
          <>
            <div className="arena-stats-live">
              <span>
                WPM: <strong>{liveStats?.wpm ?? 0}</strong>
              </span>
              <span>
                Accuracy: <strong>{liveStats?.accuracy.toFixed(1) ?? 0}%</strong>
              </span>
              <span>
                Errors: <strong>{liveStats?.errors ?? 0}</strong>
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
              {rank !== null ? (
                <div className="result-card result-card--highlight">
                  <span className="result-label">Rank</span>
                  <span className="result-value">#{rank}</span>
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
