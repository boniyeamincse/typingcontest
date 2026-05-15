import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../auth/AuthContext'
import { AppShell } from '../components/AppShell'
import { ProfileSummary } from '../components/ProfileSummary'

export function DashboardPage() {
  const navigate = useNavigate()
  const { user, logout } = useAuth()
  const [loading, setLoading] = useState(false)

  async function onLogout() {
    setLoading(true)
    try {
      await logout()
      navigate('/login')
    } finally {
      setLoading(false)
    }
  }

  return (
    <AppShell
      title={`Welcome back, ${user?.name ?? 'Player'}`}
      subtitle="Track your progress and compete with typists around the world."
    >
      <div className="dashboard-content">
        <ProfileSummary user={user} />

        <div className="dashboard-grid" style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: '2rem' }}>
          <section className="dashboard-card">
            <h3 style={{ fontFamily: 'var(--font-heading)', marginBottom: '1rem' }}>Next Steps</h3>
            <p className="lead-text" style={{ marginBottom: '1.5rem' }}>
              Jump back into the arena and improve your global ranking.
            </p>

            <div className="dash-actions">
              <Link to="/contests" className="btn-primary" style={{ padding: '1rem 2rem' }}>
                Join Active Contest
              </Link>
              <Link to="/leaderboard" className="btn-secondary">
                Leaderboard
              </Link>
              <Link to="/profile" className="btn-secondary">
                My Profile
              </Link>
            </div>
          </section>

          <section className="dashboard-card" style={{ background: 'linear-gradient(135deg, #152533 0%, #1c3a50 100%)', color: 'white' }}>
            <h3 style={{ fontFamily: 'var(--font-heading)', marginBottom: '1rem', color: '#bde2e4' }}>Quick Actions</h3>
            <div style={{ display: 'grid', gap: '0.5rem' }}>
              <Link to="/admin/dashboard" className="btn-secondary" style={{ background: 'rgba(255,255,255,0.1)', border: '1px solid rgba(255,255,255,0.2)', color: 'white' }}>
                Admin Panel
              </Link>
              <button 
                className="btn-secondary" 
                onClick={onLogout} 
                disabled={loading}
                style={{ background: 'rgba(207, 78, 47, 0.2)', border: '1px solid rgba(207, 78, 47, 0.4)', color: '#ff8a6d' }}
              >
                {loading ? 'Signing out...' : 'Sign Out'}
              </button>
            </div>
          </section>
        </div>
      </div>
    </AppShell>
  )
}

