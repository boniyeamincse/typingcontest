import { useEffect, useState } from 'react'
import { toast } from 'react-hot-toast'
import { AdminShell } from '../../admin/AdminShell'
import '../../admin/AdminShell.css'
import { recalculateLeaderboardByPeriod } from '../../api/admin'

type LeaderEntry = { rank: number; user_id: number; username: string; score: number }

export default function AdminLeaderboardPage() {
  const [period, setPeriod] = useState<'weekly' | 'monthly' | 'all_time'>('monthly')
  const [rows, setRows] = useState<LeaderEntry[]>([])
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')

  async function handleRecalculate() {
    setLoading(true)
    setError('')
    try {
      const res = await recalculateLeaderboardByPeriod(period)
      const data = (res as { data: { leaderboard?: LeaderEntry[] } }).data
      setRows(data.leaderboard ?? [])
      toast.success('Leaderboard recalculated')
    } catch (err) {
      const msg = err instanceof Error ? err.message : 'Failed'
      setError(msg)
      toast.error(msg)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => { handleRecalculate() }, [period]) // eslint-disable-line

  return (
    <AdminShell>
      <h1 className="admin-page-title">Leaderboard</h1>
      <p className="admin-page-subtitle">View and recalculate the global leaderboard.</p>

      <div className="admin-filter-bar">
        {(['weekly', 'monthly', 'all_time'] as const).map((p) => (
          <button
            key={p}
            className={`btn ${period === p ? 'btn-primary' : 'btn-secondary'}`}
            onClick={() => setPeriod(p)}
          >
            {p.replace('_', ' ')}
          </button>
        ))}
        <button className="btn btn-ok" disabled={loading} onClick={handleRecalculate}>
          {loading ? 'Recalculating…' : '↻ Recalculate'}
        </button>
      </div>

      {error ? <div className="admin-loading" style={{ color: 'var(--danger)' }}>{error}</div> : null}

      {!loading && rows.length === 0 && !error ? (
        <div className="admin-empty">No leaderboard data yet — click recalculate.</div>
      ) : null}

      {rows.length > 0 && (
        <div className="admin-table-wrap">
          <table className="admin-table">
            <thead>
              <tr>
                <th>Rank</th>
                <th>User ID</th>
                <th>Username</th>
                <th>Score</th>
              </tr>
            </thead>
            <tbody>
              {rows.map((r) => (
                <tr key={r.user_id}>
                  <td style={{ fontWeight: 700 }}>#{r.rank}</td>
                  <td style={{ color: 'var(--muted)' }}>{r.user_id}</td>
                  <td>{r.username}</td>
                  <td style={{ fontWeight: 600, color: 'var(--focus)' }}>{r.score.toLocaleString()}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </AdminShell>
  )
}
