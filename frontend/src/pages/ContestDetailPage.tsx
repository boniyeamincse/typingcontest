import { useEffect, useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import { getContest, getContestLeaderboard, joinContest } from '../api/contests'
import type { Contest, ContestResult } from '../api/contests'
import { useAuth } from '../auth/AuthContext'
import './ContestDetailPage.css'

export default function ContestDetailPage() {
  const { id } = useParams<{ id: string }>()
  const { token } = useAuth()
  const navigate = useNavigate()

  const [contest, setContest] = useState<Contest | null>(null)
  const [leaderboard, setLeaderboard] = useState<ContestResult[]>([])
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(true)
  const [joining, setJoining] = useState(false)

  const contestId = Number(id)

  useEffect(() => {
    if (!contestId) return
    Promise.all([
      getContest(contestId),
      getContestLeaderboard(contestId),
    ])
      .then(([c, lb]) => {
        setContest(c)
        setLeaderboard(lb.leaderboard)
      })
      .catch((e: unknown) =>
        setError(e instanceof Error ? e.message : 'Failed to load contest'),
      )
      .finally(() => setLoading(false))
  }, [contestId])

  async function handleJoinOrPlay() {
    if (!token) { navigate('/login'); return }
    if (!contest) return

    if (contest.status === 'active') {
      setJoining(true)
      try {
        await joinContest(contest.id, token)
        navigate(`/contests/${contest.id}/play`)
      } catch (e) {
        if (e instanceof Error && e.message.toLowerCase().includes('already')) {
          navigate(`/contests/${contest.id}/play`)
        } else {
          setError(e instanceof Error ? e.message : 'Could not join contest')
        }
      } finally {
        setJoining(false)
      }
    }
  }

  if (loading) return <p className="loading-msg">Loading…</p>
  if (error) return <p className="error-msg" style={{ padding: '2rem' }}>{error}</p>
  if (!contest) return null

  const canPlay = contest.status === 'active'

  return (
    <div className="contest-detail">
      <header className="detail-header">
        <Link to="/contests" className="back-link">← All Contests</Link>
        <div className="detail-badges">
          <span className={`badge badge--${contest.status}`}>{contest.status}</span>
          <span className="badge badge--type">{contest.type}</span>
        </div>
      </header>

      <h1 className="detail-title">{contest.title}</h1>

      <div className="detail-meta">
        <span>⏱ {contest.duration_seconds}s</span>
        {contest.starts_at && (
          <span>📅 {new Date(contest.starts_at).toLocaleString()}</span>
        )}
        {contest.ends_at && (
          <span>⏳ Ends {new Date(contest.ends_at).toLocaleString()}</span>
        )}
      </div>

      <div className="detail-text-preview">
        <h2>Typing Passage</h2>
        <blockquote>{contest.text_content}</blockquote>
      </div>

      {canPlay && (
        <button
          className="play-btn"
          onClick={handleJoinOrPlay}
          disabled={joining}
        >
          {joining ? 'Joining…' : '▶ Play Now'}
        </button>
      )}

      {contest.status === 'published' && (
        <p className="detail-upcoming">
          Contest starts on {contest.starts_at ? new Date(contest.starts_at).toLocaleString() : 'TBD'}.
        </p>
      )}

      <section className="leaderboard-section">
        <h2>Leaderboard</h2>
        {leaderboard.length === 0 ? (
          <p className="empty-msg">No results yet.</p>
        ) : (
          <table className="leaderboard-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Player</th>
                <th>WPM</th>
                <th>Accuracy</th>
                <th>Errors</th>
                <th>Score</th>
              </tr>
            </thead>
            <tbody>
              {leaderboard.map((r) => (
                <tr key={r.id}>
                  <td>{r.rank ?? '—'}</td>
                  <td>{r.user?.name ?? `User #${r.user_id}`}</td>
                  <td>{r.wpm}</td>
                  <td>{r.accuracy}%</td>
                  <td>{r.errors}</td>
                  <td>{r.score}</td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </section>
    </div>
  )
}
