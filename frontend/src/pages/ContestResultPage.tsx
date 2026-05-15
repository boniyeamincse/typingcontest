import { useEffect, useRef, useState } from 'react'
import { Link, useLocation, useNavigate, useParams } from 'react-router-dom'
import { getContest, getContestLeaderboard, getUserResult } from '../api/contests'
import type { Contest, ContestResult } from '../api/contests'
import { useAuth } from '../auth/AuthContext'
import { AppShell } from '../components/AppShell'
import './ContestResultPage.css'

// ── tiny animated counter ──────────────────────────────────────────────────
function AnimatedNumber({
  target,
  duration = 1200,
  suffix = '',
}: {
  target: number
  duration?: number
  suffix?: string
}) {
  const [value, setValue] = useState(0)
  const frameRef = useRef<number | null>(null)

  useEffect(() => {
    const start = performance.now()
    function step(now: number) {
      const progress = Math.min((now - start) / duration, 1)
      // ease-out cubic
      const eased = 1 - Math.pow(1 - progress, 3)
      setValue(Math.round(target * eased))
      if (progress < 1) {
        frameRef.current = requestAnimationFrame(step)
      }
    }
    frameRef.current = requestAnimationFrame(step)
    return () => { if (frameRef.current) cancelAnimationFrame(frameRef.current) }
  }, [target, duration])

  return <>{value}{suffix}</>
}

// ── rank badge ─────────────────────────────────────────────────────────────
function RankBadge({ rank }: { rank: number | null }) {
  if (rank === null) return <span className="rank-badge rank-badge--none">—</span>

  const medal = rank === 1 ? '🥇' : rank === 2 ? '🥈' : rank === 3 ? '🥉' : null
  const cls =
    rank === 1 ? 'rank-badge--gold'
    : rank === 2 ? 'rank-badge--silver'
    : rank === 3 ? 'rank-badge--bronze'
    : 'rank-badge--plain'

  return (
    <span className={`rank-badge ${cls}`}>
      {medal ? `${medal} ` : '#'}{rank}
    </span>
  )
}

// ── location state from TypingArenaPage ────────────────────────────────────
type ResultState = {
  wpm?: number
  accuracy?: number
  errors?: number
  score?: number | null
}

export default function ContestResultPage() {
  const { id } = useParams<{ id: string }>()
  const { token, user } = useAuth()
  const navigate = useNavigate()
  const location = useLocation()
  const contestId = Number(id)

  // Optimistic data passed from TypingArenaPage via router state
  const stateData = (location.state as ResultState | null) ?? {}

  const [contest, setContest] = useState<Contest | null>(null)
  const [leaderboard, setLeaderboard] = useState<ContestResult[]>([])
  const [xp, setXp] = useState<number | null>(null)
  const [level, setLevel] = useState<number | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  // Seed optimistic values so the counters run immediately
  const [wpm, setWpm] = useState(stateData.wpm ?? 0)
  const [accuracy, setAccuracy] = useState(stateData.accuracy ?? 0)
  const [errors, setErrors] = useState(stateData.errors ?? 0)
  const [score, setScore] = useState<number>(stateData.score ?? 0)
  const [rank, setRank] = useState<number | null>(null)

  const [rankVisible, setRankVisible] = useState(false)
  const [xpVisible, setXpVisible] = useState(false)

  useEffect(() => {
    if (!token) { navigate('/login'); return }
    if (!contestId) return

    const API_BASE =
      (import.meta.env.VITE_API_BASE_URL as string | undefined)?.replace(/\/$/, '') ??
      'http://127.0.0.1:8001/api/v1'

    async function load() {
      try {
        const [contestData, lbData] = await Promise.all([
          getContest(contestId),
          getContestLeaderboard(contestId),
        ])
        setContest(contestData)
        setLeaderboard(lbData.leaderboard)

        // Fetch user's own result
        try {
          const userResult = await getUserResult(contestId, token!)
          setWpm(userResult.wpm)
          setAccuracy(userResult.accuracy)
          setErrors(userResult.errors)
          setScore(userResult.score)
          setRank(userResult.rank)
        } catch {
          // If not found yet, keep optimistic values
        }

        // Fetch XP / level from rewards endpoint
        try {
          const res = await fetch(`${API_BASE}/rewards/progress`, {
            headers: {
              Authorization: `Bearer ${token}`,
              Accept: 'application/json',
            },
          })
          if (res.ok) {
            const payload = await res.json()
            setXp(Number(payload?.data?.xp ?? 0))
            setLevel(Number(payload?.data?.level ?? 1))
          }
        } catch {}

      } catch (e) {
        setError(e instanceof Error ? e.message : 'Failed to load results.')
      } finally {
        setLoading(false)
      }
    }

    load()

    // Stagger the reveal animations
    const rankTimer = setTimeout(() => setRankVisible(true), 900)
    const xpTimer   = setTimeout(() => setXpVisible(true), 1400)
    return () => { clearTimeout(rankTimer); clearTimeout(xpTimer) }
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [contestId])

  // ── helpers ──────────────────────────────────────────────────────────────
  function wpmLabel(wpm: number): string {
    if (wpm >= 120) return 'Legendary'
    if (wpm >= 90)  return 'Expert'
    if (wpm >= 60)  return 'Proficient'
    if (wpm >= 40)  return 'Average'
    return 'Beginner'
  }

  function wpmClass(wpm: number): string {
    if (wpm >= 90)  return 'tier--legendary'
    if (wpm >= 60)  return 'tier--expert'
    if (wpm >= 40)  return 'tier--average'
    return 'tier--beginner'
  }

  function accuracyClass(acc: number): string {
    if (acc >= 98)  return 'acc--perfect'
    if (acc >= 90)  return 'acc--good'
    if (acc >= 75)  return 'acc--ok'
    return 'acc--low'
  }

  // ── render ────────────────────────────────────────────────────────────────
  if (loading && !stateData.wpm) {
    return (
      <div className="result-loading">
        <div className="result-spinner" />
        <p>Loading results…</p>
      </div>
    )
  }

  if (error) {
    return (
      <AppShell title="Contest Results">
        <div className="result-error">
          <p>{error}</p>
          <Link to="/contests" className="btn-secondary">Back to contests</Link>
        </div>
      </AppShell>
    )
  }

  const tier = wpmLabel(wpm)
  const totalPlayers = leaderboard.length || (rank ?? 1)

  return (
    <AppShell
      title="Round Complete"
      subtitle={contest?.title ?? 'Contest Results'}
      actions={
        <Link to="/contests" className="btn-secondary">All Contests</Link>
      }
    >
      <div className="result-page">

        {/* ── Hero banner ── */}
        <div className="result-hero">
          <div className="result-hero-inner">
            <div className={`result-tier-badge ${wpmClass(wpm)}`}>{tier}</div>
            <h1 className="result-hero-wpm">
              <AnimatedNumber target={wpm} duration={1000} />
              <span className="result-hero-unit"> WPM</span>
            </h1>
            <p className="result-hero-sub">
              {accuracy >= 98 ? '✨ Near-perfect accuracy!' :
               accuracy >= 90 ? 'Great accuracy!' :
               accuracy >= 75 ? 'Decent performance.' :
               'Keep practising for better accuracy.'}
            </p>
          </div>
        </div>

        {/* ── Stats grid ── */}
        <section className="result-section">
          <h2 className="result-section-title">Your Stats</h2>
          <div className="result-stats-grid">
            <div className="stat-card stat-card--wpm">
              <span className="stat-label">WPM</span>
              <span className="stat-value">
                <AnimatedNumber target={wpm} duration={1000} />
              </span>
              <span className="stat-sub">{tier}</span>
            </div>

            <div className={`stat-card ${accuracyClass(accuracy)}`}>
              <span className="stat-label">Accuracy</span>
              <span className="stat-value">
                <AnimatedNumber target={Math.round(accuracy * 10) / 10} duration={900} suffix="%" />
              </span>
              <span className="stat-sub">
                {errors === 0 ? 'No errors!' : `${errors} error${errors !== 1 ? 's' : ''}`}
              </span>
            </div>

            <div className="stat-card">
              <span className="stat-label">Errors</span>
              <span className="stat-value">
                <AnimatedNumber target={errors} duration={700} />
              </span>
              <span className="stat-sub">keystrokes off</span>
            </div>

            <div className="stat-card stat-card--score">
              <span className="stat-label">Score</span>
              <span className="stat-value">
                <AnimatedNumber target={score} duration={1100} />
              </span>
              <span className="stat-sub">contest points</span>
            </div>
          </div>
        </section>

        {/* ── Rank reveal ── */}
        <section className="result-section">
          <h2 className="result-section-title">Your Rank</h2>
          <div className={`result-rank-card ${rankVisible ? 'result-rank-card--visible' : ''}`}>
            <div className="result-rank-inner">
              <RankBadge rank={rank} />
              {rank !== null && totalPlayers > 1 ? (
                <p className="result-rank-sub">out of {totalPlayers} participants</p>
              ) : null}
              {rank === null ? (
                <p className="result-rank-sub">Rank is being calculated…</p>
              ) : null}
            </div>
            {rank === 1 ? (
              <div className="result-confetti-strip">
                {'🎉'.repeat(8)}
              </div>
            ) : null}
          </div>
        </section>

        {/* ── XP / level ── */}
        {xp !== null && (
          <section className="result-section">
            <h2 className="result-section-title">Rewards</h2>
            <div className={`result-xp-card ${xpVisible ? 'result-xp-card--visible' : ''}`}>
              <div className="result-xp-row">
                <span className="result-xp-icon">⚡</span>
                <div className="result-xp-info">
                  <p className="result-xp-label">Total XP</p>
                  <p className="result-xp-value">
                    <AnimatedNumber target={xp} duration={1000} />
                  </p>
                </div>
                {level !== null && (
                  <div className="result-level-badge">
                    <span className="result-level-label">Level</span>
                    <span className="result-level-num">{level}</span>
                  </div>
                )}
              </div>
              {level !== null && (
                <div className="result-xp-bar-wrap">
                  <div
                    className="result-xp-bar-fill"
                    style={{
                      width: xpVisible
                        ? `${Math.min(100, ((xp % 1000) / 1000) * 100)}%`
                        : '0%',
                    }}
                  />
                </div>
              )}
            </div>
          </section>
        )}

        {/* ── Full leaderboard ── */}
        <section className="result-section">
          <h2 className="result-section-title">Final Leaderboard</h2>
          {leaderboard.length === 0 ? (
            <p className="result-empty">No results yet — check back shortly.</p>
          ) : (
            <div className="result-lb-wrap">
              {/* Podium top-3 */}
              {leaderboard.length >= 3 && (
                <div className="result-podium">
                  {/* 2nd */}
                  <div className="podium-slot podium-slot--2">
                    <div className="podium-avatar">
                      {leaderboard[1].user?.avatar
                        ? <img src={leaderboard[1].user.avatar} alt="" />
                        : <span>{(leaderboard[1].user?.username ?? '?').charAt(0).toUpperCase()}</span>}
                    </div>
                    <p className="podium-name">{leaderboard[1].user?.username ?? '—'}</p>
                    <p className="podium-wpm">{leaderboard[1].wpm} WPM</p>
                    <div className="podium-block podium-block--2">🥈</div>
                  </div>
                  {/* 1st */}
                  <div className="podium-slot podium-slot--1">
                    <div className="podium-crown">👑</div>
                    <div className="podium-avatar podium-avatar--lg">
                      {leaderboard[0].user?.avatar
                        ? <img src={leaderboard[0].user.avatar} alt="" />
                        : <span>{(leaderboard[0].user?.username ?? '?').charAt(0).toUpperCase()}</span>}
                    </div>
                    <p className="podium-name">{leaderboard[0].user?.username ?? '—'}</p>
                    <p className="podium-wpm">{leaderboard[0].wpm} WPM</p>
                    <div className="podium-block podium-block--1">🥇</div>
                  </div>
                  {/* 3rd */}
                  <div className="podium-slot podium-slot--3">
                    <div className="podium-avatar">
                      {leaderboard[2].user?.avatar
                        ? <img src={leaderboard[2].user.avatar} alt="" />
                        : <span>{(leaderboard[2].user?.username ?? '?').charAt(0).toUpperCase()}</span>}
                    </div>
                    <p className="podium-name">{leaderboard[2].user?.username ?? '—'}</p>
                    <p className="podium-wpm">{leaderboard[2].wpm} WPM</p>
                    <div className="podium-block podium-block--3">🥉</div>
                  </div>
                </div>
              )}

              {/* Full table */}
              <table className="result-lb-table">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Player</th>
                    <th>WPM</th>
                    <th>Acc</th>
                    <th>Errors</th>
                    <th>Score</th>
                  </tr>
                </thead>
                <tbody>
                  {leaderboard.map((entry, i) => {
                    const isMe = user && entry.user_id === user.id
                    return (
                      <tr
                        key={`${entry.user_id}-${i}`}
                        className={isMe ? 'lb-row lb-row--me' : 'lb-row'}
                      >
                        <td className="lb-rank">
                          {i === 0 ? '🥇' : i === 1 ? '🥈' : i === 2 ? '🥉' : `#${entry.rank ?? i + 1}`}
                        </td>
                        <td className="lb-player">
                          <div className="lb-avatar">
                            {entry.user?.avatar
                              ? <img src={entry.user.avatar} alt="" />
                              : <span>{(entry.user?.username ?? '?').charAt(0).toUpperCase()}</span>}
                          </div>
                          <span>{entry.user?.username ?? '—'}</span>
                          {isMe && <span className="lb-you-tag">You</span>}
                        </td>
                        <td className="lb-wpm">{entry.wpm}</td>
                        <td className="lb-acc">{Number(entry.accuracy).toFixed(1)}%</td>
                        <td className="lb-errors">{entry.errors}</td>
                        <td className="lb-score">{entry.score}</td>
                      </tr>
                    )
                  })}
                </tbody>
              </table>
            </div>
          )}
        </section>

        {/* ── Actions ── */}
        <div className="result-actions">
          <Link to="/contests" className="btn-primary">Browse Contests</Link>
          <Link to="/leaderboard" className="btn-secondary">Global Leaderboard</Link>
          <Link to="/profile" className="btn-secondary">My Profile</Link>
        </div>

      </div>
    </AppShell>
  )
}
