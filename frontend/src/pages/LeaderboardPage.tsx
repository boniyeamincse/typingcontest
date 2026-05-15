import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { AppShell } from '../components/AppShell'
import {
  getDailyLeaderboard,
  getGlobalLeaderboard,
  getMonthlyLeaderboard,
  getWeeklyLeaderboard,
  type LeaderboardEntry,
  type LeaderboardResponse,
} from '../api/profile'

type Period = 'global' | 'daily' | 'weekly' | 'monthly'

const PERIOD_LABELS: Record<Period, string> = {
  global: 'All Time',
  daily: 'Today',
  weekly: 'This Week',
  monthly: 'This Month',
}

function MedalIcon({ rank }: { rank: number }) {
  if (rank === 1) return <span aria-label="Gold">🥇</span>
  if (rank === 2) return <span aria-label="Silver">🥈</span>
  if (rank === 3) return <span aria-label="Bronze">🥉</span>
  return <span style={{ color: 'var(--muted)', fontWeight: 600 }}>#{rank}</span>
}

function normalizeEntries(data: LeaderboardResponse): LeaderboardEntry[] {
  const raw = data.leaderboard ?? data.data ?? []
  // Assign rank if missing (API may return pre-ranked or raw list)
  return raw.map((e, i) => ({ ...e, rank: e.rank ?? i + 1 }))
}

export default function LeaderboardPage() {
  const [period, setPeriod] = useState<Period>('global')
  const [entries, setEntries] = useState<LeaderboardEntry[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    setLoading(true)
    setError('')
    const fetchers: Record<Period, () => Promise<LeaderboardResponse>> = {
      global: getGlobalLeaderboard,
      daily: getDailyLeaderboard,
      weekly: getWeeklyLeaderboard,
      monthly: getMonthlyLeaderboard,
    }
    fetchers[period]()
      .then((res) => setEntries(normalizeEntries(res)))
      .catch((e: Error) => setError(e.message))
      .finally(() => setLoading(false))
  }, [period])

  return (
    <AppShell
      title="Leaderboard"
      subtitle="Global and time-based rankings across all contests."
    >
      {/* ── Period tabs ────────────────────────────────────────────────── */}
      <div
        style={{
          display: 'flex',
          gap: '0.5rem',
          marginBottom: '2rem',
          flexWrap: 'wrap',
        }}
      >
        {(Object.keys(PERIOD_LABELS) as Period[]).map((p) => (
          <button
            key={p}
            onClick={() => setPeriod(p)}
            style={{
              padding: '0.55rem 1.25rem',
              borderRadius: '999px',
              border: '1px solid var(--line, #e5e7eb)',
              cursor: 'pointer',
              fontWeight: period === p ? 700 : 500,
              background: period === p ? 'var(--focus, #138F95)' : 'transparent',
              color: period === p ? '#fff' : 'inherit',
              transition: 'all 0.15s',
            }}
          >
            {PERIOD_LABELS[p]}
          </button>
        ))}
      </div>

      {loading ? (
        <p style={{ textAlign: 'center', color: 'var(--muted)', padding: '4rem' }}>Loading…</p>
      ) : error ? (
        <p style={{ textAlign: 'center', color: 'var(--danger, #ef4444)', padding: '4rem' }}>{error}</p>
      ) : entries.length === 0 ? (
        <p style={{ textAlign: 'center', color: 'var(--muted)', padding: '4rem' }}>
          No rankings yet for this period. <Link to="/contests">Join a contest</Link> to be first!
        </p>
      ) : (
        <div
          style={{
            background: 'var(--surface, #fff)',
            border: '1px solid var(--line, #e5e7eb)',
            borderRadius: '1.25rem',
            overflow: 'hidden',
          }}
        >
          {/* Top 3 podium (if enough entries) */}
          {entries.length >= 3 && (
            <div
              style={{
                display: 'grid',
                gridTemplateColumns: '1fr 1fr 1fr',
                gap: '1rem',
                padding: '2rem',
                background: 'linear-gradient(135deg, rgba(19,143,149,0.06), rgba(207,78,47,0.06))',
                borderBottom: '1px solid var(--line, #e5e7eb)',
              }}
            >
              {[entries[1], entries[0], entries[2]].map((e, i) =>
                e ? (
                  <div
                    key={e.user_id}
                    style={{
                      textAlign: 'center',
                      padding: '1rem 0.5rem',
                      transform: i === 1 ? 'scale(1.05)' : 'none',
                    }}
                  >
                    <div style={{ fontSize: i === 1 ? '2.5rem' : '2rem', marginBottom: 4 }}>
                      {i === 1 ? '🥇' : i === 0 ? '🥈' : '🥉'}
                    </div>
                    <div style={{ fontWeight: 700, fontSize: '0.9rem' }}>{e.name}</div>
                    <div style={{ color: 'var(--muted)', fontSize: '0.75rem' }}>@{e.username}</div>
                    <div
                      style={{
                        marginTop: 8,
                        fontSize: '1.2rem',
                        fontWeight: 800,
                        color: 'var(--focus, #138F95)',
                        fontFamily: 'var(--font-heading, monospace)',
                      }}
                    >
                      {e.wpm} WPM
                    </div>
                  </div>
                ) : null,
              )}
            </div>
          )}

          {/* Full table */}
          <div style={{ overflowX: 'auto' }}>
            <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: '0.85rem' }}>
              <thead>
                <tr style={{ borderBottom: '1px solid var(--line, #e5e7eb)' }}>
                  {['Rank', 'Player', 'Country', 'WPM', 'Accuracy', 'Score', 'Contests'].map((h) => (
                    <th
                      key={h}
                      style={{
                        padding: '0.75rem 1rem',
                        textAlign: 'left',
                        fontWeight: 600,
                        fontSize: '0.75rem',
                        textTransform: 'uppercase',
                        letterSpacing: '0.05em',
                        color: 'var(--muted)',
                        whiteSpace: 'nowrap',
                      }}
                    >
                      {h}
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {entries.map((e, i) => (
                  <tr
                    key={e.user_id}
                    style={{
                      borderBottom: i < entries.length - 1 ? '1px solid var(--line, #e5e7eb)' : 'none',
                      background: e.rank <= 3 ? 'rgba(19,143,149,0.03)' : 'transparent',
                    }}
                  >
                    <td style={{ padding: '0.75rem 1rem', width: 60 }}>
                      <MedalIcon rank={e.rank} />
                    </td>
                    <td style={{ padding: '0.75rem 1rem' }}>
                      <div style={{ display: 'flex', alignItems: 'center', gap: '0.6rem' }}>
                        {e.avatar ? (
                          <img
                            src={e.avatar}
                            alt=""
                            style={{ width: 28, height: 28, borderRadius: '50%', objectFit: 'cover' }}
                          />
                        ) : (
                          <div
                            style={{
                              width: 28,
                              height: 28,
                              borderRadius: '50%',
                              background: 'var(--focus, #138F95)',
                              display: 'flex',
                              alignItems: 'center',
                              justifyContent: 'center',
                              color: '#fff',
                              fontSize: '0.7rem',
                              fontWeight: 700,
                              flexShrink: 0,
                            }}
                          >
                            {e.name.charAt(0).toUpperCase()}
                          </div>
                        )}
                        <div>
                          <div style={{ fontWeight: 600 }}>{e.name}</div>
                          <div style={{ color: 'var(--muted)', fontSize: '0.75rem' }}>@{e.username}</div>
                        </div>
                      </div>
                    </td>
                    <td style={{ padding: '0.75rem 1rem', color: 'var(--muted)', fontSize: '0.8rem' }}>
                      {e.country ?? '—'}
                    </td>
                    <td
                      style={{
                        padding: '0.75rem 1rem',
                        fontWeight: 700,
                        color: 'var(--focus, #138F95)',
                        fontFamily: 'var(--font-heading, monospace)',
                      }}
                    >
                      {e.wpm}
                    </td>
                    <td style={{ padding: '0.75rem 1rem' }}>{e.accuracy}%</td>
                    <td style={{ padding: '0.75rem 1rem', fontWeight: 600 }}>
                      {typeof e.score === 'number' ? e.score.toLocaleString() : e.score}
                    </td>
                    <td style={{ padding: '0.75rem 1rem', color: 'var(--muted)' }}>
                      {e.contests_played}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </AppShell>
  )
}
