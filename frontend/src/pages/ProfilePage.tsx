import { useEffect, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../auth/AuthContext'
import { AppShell } from '../components/AppShell'
import {
  getMyProfile,
  getMyStats,
  getMyBadges,
  getMyMatches,
  type UserProfile,
  type ProfileStats,
  type UserBadge,
  type MatchRecord,
} from '../api/profile'

function StatCard({ label, value }: { label: string; value: string | number }) {
  return (
    <div
      style={{
        background: 'var(--surface, #fff)',
        border: '1px solid var(--line, #e5e7eb)',
        borderRadius: '1rem',
        padding: '1.25rem 1.5rem',
        textAlign: 'center',
      }}
    >
      <div
        style={{
          fontSize: '1.75rem',
          fontWeight: 700,
          fontFamily: 'var(--font-heading, monospace)',
          color: 'var(--focus, #138F95)',
        }}
      >
        {value}
      </div>
      <div style={{ fontSize: '0.78rem', color: 'var(--muted, #6b7280)', marginTop: 4 }}>{label}</div>
    </div>
  )
}

function BadgeChip({ badge }: { badge: UserBadge }) {
  return (
    <div
      title={badge.description}
      style={{
        display: 'inline-flex',
        alignItems: 'center',
        gap: '0.5rem',
        padding: '0.35rem 0.85rem',
        borderRadius: '999px',
        background: badge.is_premium
          ? 'linear-gradient(135deg, #f59e0b22, #f59e0b44)'
          : 'rgba(19, 143, 149, 0.1)',
        border: `1px solid ${badge.is_premium ? '#f59e0b66' : 'rgba(19,143,149,0.3)'}`,
        fontSize: '0.8rem',
        fontWeight: 600,
        color: badge.is_premium ? '#b45309' : 'var(--focus, #138F95)',
      }}
    >
      {badge.icon_url ? (
        <img src={badge.icon_url} alt="" style={{ width: 16, height: 16, objectFit: 'contain' }} />
      ) : (
        <span aria-hidden>🏅</span>
      )}
      {badge.name}
    </div>
  )
}

export default function ProfilePage() {
  const { token } = useAuth()
  const navigate = useNavigate()

  const [profile, setProfile] = useState<UserProfile | null>(null)
  const [stats, setStats] = useState<ProfileStats | null>(null)
  const [badges, setBadges] = useState<UserBadge[]>([])
  const [matches, setMatches] = useState<MatchRecord[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    if (!token) { navigate('/login'); return }
    setLoading(true)
    Promise.all([
      getMyProfile(token),
      getMyStats(token),
      getMyBadges(token),
      getMyMatches(token),
    ])
      .then(([p, s, b, m]) => {
        setProfile(p.user)
        setStats(s.stats)
        setBadges(b.badges ?? [])
        setMatches(m.matches ?? [])
      })
      .catch((e: Error) => setError(e.message))
      .finally(() => setLoading(false))
  }, [token, navigate])

  if (loading) {
    return (
      <AppShell title="Profile" subtitle="Loading your profile…">
        <p style={{ textAlign: 'center', color: 'var(--muted)', padding: '4rem' }}>Loading…</p>
      </AppShell>
    )
  }

  if (error || !profile) {
    return (
      <AppShell title="Profile" subtitle="Could not load profile">
        <p style={{ textAlign: 'center', color: 'var(--danger, #ef4444)', padding: '4rem' }}>
          {error || 'Profile not found.'}
        </p>
      </AppShell>
    )
  }

  return (
    <AppShell
      title={profile.name}
      subtitle={`@${profile.username}${profile.country ? ` · ${profile.country}` : ''} · ${profile.plan_type.toUpperCase()}`}
    >
      <div style={{ maxWidth: 900, margin: '0 auto', display: 'flex', flexDirection: 'column', gap: '2rem' }}>

        {/* ── Stats grid ──────────────────────────────────────────────── */}
        {stats && (
          <section>
            <h2 style={{ fontFamily: 'var(--font-heading)', marginBottom: '1rem', fontSize: '1rem', textTransform: 'uppercase', letterSpacing: '0.05em', color: 'var(--muted)' }}>
              Statistics
            </h2>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(130px, 1fr))', gap: '1rem' }}>
              <StatCard label="Contests" value={stats.total_contests} />
              <StatCard label="Wins" value={stats.total_wins} />
              <StatCard label="Best WPM" value={stats.best_wpm} />
              <StatCard label="Avg WPM" value={stats.avg_wpm} />
              <StatCard label="Avg Accuracy" value={`${stats.avg_accuracy}%`} />
              <StatCard label="Global Rank" value={stats.global_rank ?? '—'} />
              <StatCard label="Streak" value={`${stats.streak_days}d`} />
              <StatCard label="Total XP" value={stats.total_xp.toLocaleString()} />
            </div>
          </section>
        )}

        {/* ── Badges ──────────────────────────────────────────────────── */}
        <section>
          <h2 style={{ fontFamily: 'var(--font-heading)', marginBottom: '1rem', fontSize: '1rem', textTransform: 'uppercase', letterSpacing: '0.05em', color: 'var(--muted)' }}>
            Badges ({badges.length})
          </h2>
          {badges.length === 0 ? (
            <p style={{ color: 'var(--muted)', fontSize: '0.9rem' }}>No badges earned yet. Compete to earn your first!</p>
          ) : (
            <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.6rem' }}>
              {badges.map((b) => <BadgeChip key={b.id} badge={b} />)}
            </div>
          )}
        </section>

        {/* ── Recent Matches ───────────────────────────────────────────── */}
        <section>
          <h2 style={{ fontFamily: 'var(--font-heading)', marginBottom: '1rem', fontSize: '1rem', textTransform: 'uppercase', letterSpacing: '0.05em', color: 'var(--muted)' }}>
            Recent Matches
          </h2>
          {matches.length === 0 ? (
            <p style={{ color: 'var(--muted)', fontSize: '0.9rem' }}>
              No matches yet. <Link to="/contests">Join a contest</Link> to get started!
            </p>
          ) : (
            <div
              style={{
                background: 'var(--surface, #fff)',
                border: '1px solid var(--line, #e5e7eb)',
                borderRadius: '1rem',
                overflow: 'hidden',
              }}
            >
              <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: '0.85rem' }}>
                <thead>
                  <tr style={{ borderBottom: '1px solid var(--line, #e5e7eb)' }}>
                    {['Contest', 'WPM', 'Accuracy', 'Errors', 'Score', 'Rank', 'Date'].map((h) => (
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
                        }}
                      >
                        {h}
                      </th>
                    ))}
                  </tr>
                </thead>
                <tbody>
                  {matches.map((m, i) => (
                    <tr
                      key={m.id}
                      style={{
                        borderBottom: i < matches.length - 1 ? '1px solid var(--line, #e5e7eb)' : 'none',
                      }}
                    >
                      <td style={{ padding: '0.75rem 1rem', fontWeight: 600 }}>
                        <Link to={`/contests/${m.contest_id}`} style={{ color: 'var(--focus, #138F95)' }}>
                          {m.contest_title}
                        </Link>
                      </td>
                      <td style={{ padding: '0.75rem 1rem', color: 'var(--focus, #138F95)', fontWeight: 700 }}>{m.wpm}</td>
                      <td style={{ padding: '0.75rem 1rem' }}>{m.accuracy}%</td>
                      <td style={{ padding: '0.75rem 1rem', color: m.errors > 0 ? 'var(--danger, #ef4444)' : 'inherit' }}>{m.errors}</td>
                      <td style={{ padding: '0.75rem 1rem' }}>{m.score}</td>
                      <td style={{ padding: '0.75rem 1rem', color: 'var(--muted)' }}>{m.rank ?? '—'}</td>
                      <td style={{ padding: '0.75rem 1rem', color: 'var(--muted)', fontSize: '0.78rem' }}>
                        {new Date(m.played_at).toLocaleDateString()}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </section>

        {/* ── Account info ─────────────────────────────────────────────── */}
        <section
          style={{
            background: 'var(--surface, #fff)',
            border: '1px solid var(--line, #e5e7eb)',
            borderRadius: '1rem',
            padding: '1.5rem',
          }}
        >
          <h2 style={{ fontFamily: 'var(--font-heading)', marginBottom: '1rem', fontSize: '1rem' }}>Account</h2>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '0.5rem 2rem', fontSize: '0.85rem' }}>
            <div><span style={{ color: 'var(--muted)' }}>Email:</span> {profile.email}</div>
            <div><span style={{ color: 'var(--muted)' }}>Plan:</span> {profile.plan_type.toUpperCase()}</div>
            <div><span style={{ color: 'var(--muted)' }}>Status:</span> {profile.subscription_status}</div>
            <div><span style={{ color: 'var(--muted)' }}>Member since:</span> {new Date(profile.created_at).toLocaleDateString()}</div>
          </div>
        </section>

      </div>
    </AppShell>
  )
}
