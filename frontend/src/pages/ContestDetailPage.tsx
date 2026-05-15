import { useEffect, useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import { getContest, getContestLeaderboard, joinContest } from '../api/contests'
import type { Contest, ContestResult } from '../api/contests'
import { useAuth } from '../auth/AuthContext'
import { AppShell } from '../components/AppShell'
import './ContestDetailPage.css'

function statusLabel(status: string): string {
  if (status === 'published') return 'Upcoming'
  if (status === 'active') return 'Live'
  if (status === 'finished') return 'Finished'
  if (status === 'completed') return 'Ended'
  return status.charAt(0).toUpperCase() + status.slice(1)
}

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

  async function handleJoinLobby() {
    if (!token) { navigate('/login'); return }
    if (!contest) return
    setJoining(true)
    try {
      await joinContest(contest.id, token)
    } catch (e) {
      // "already joined" is fine — just proceed to lobby
      if (e instanceof Error && !e.message.toLowerCase().includes('already')) {
        setError(e instanceof Error ? e.message : 'Could not join contest')
        setJoining(false)
        return
      }
    }
    navigate(`/contests/${contest.id}/lobby`)
  }

  if (loading) return <p className="loading-msg">Loading…</p>
  if (error) return <p className="error-msg" style={{ padding: '2rem' }}>{error}</p>
  if (!contest) return null

  const canPlay = contest.status === 'active'

  return (
    <AppShell
      title={contest.title}
      subtitle="Room overview, typing passage preview, and live ranking results."
      actions={
        <Link to="/contests" className="btn-secondary">
          All Contests
        </Link>
      }
    >
      <div className="contest-detail surface-card">
        <header className="detail-header">
          <div className="detail-badges">
            <span className={`badge badge--${contest.status}`}>{statusLabel(contest.status)}</span>
            <span className="badge badge--type">{contest.type}</span>
          </div>
          <div className="detail-meta">
            <span>{contest.durationSeconds}s round</span>
            {contest.startsAt ? <span>Starts {new Date(contest.startsAt).toLocaleString()}</span> : null}
            {contest.endsAt ? <span>Ends {new Date(contest.endsAt).toLocaleString()}</span> : null}
          </div>
        </header>

        <div className="detail-text-preview">
          <h2>Typing Passage</h2>
          <blockquote>{contest.textContent}</blockquote>
        </div>

        {canPlay ? (
          <button className="play-btn" onClick={handleJoinOrPlay} disabled={joining}>
            {joining ? 'Joining...' : 'Play Now'}
          </button>
        ) : null}

        {contest.status === 'published' ? (
          <div className="detail-upcoming">
            <p>This contest is upcoming — join the lobby now and get notified the moment it starts.</p>
            <button className="play-btn" onClick={handleJoinLobby} disabled={joining}>
              {joining ? 'Joining…' : 'Join Lobby'}
            </button>
          </div>
        ) : null}

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
                    <td>{r.rank ?? '-'}</td>
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
    </AppShell>
  )
}
