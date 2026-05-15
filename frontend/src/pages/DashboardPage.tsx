import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../auth/AuthContext'
import { AppShell } from '../components/AppShell'

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
      subtitle="Monitor your account status, then jump into active competitions."
    >
      <section className="dashboard-card">
        <p className="lead-text">
          Your authentication flow is connected and ready for full gameplay.
        </p>

        <div className="stat-grid">
          <article>
            <h2>Email</h2>
            <p>{user?.email}</p>
          </article>
          <article>
            <h2>User ID</h2>
            <p>{user?.id}</p>
          </article>
          <article>
            <h2>Account</h2>
            <p>Active</p>
          </article>
        </div>

        <div className="dash-actions">
          <Link to="/contests" className="btn-primary">
            Browse Contests
          </Link>
          <Link to="/admin/contests" className="btn-secondary">
            Manage Contests
          </Link>
          <button className="btn-secondary" onClick={onLogout} disabled={loading}>
            {loading ? 'Signing out...' : 'Logout'}
          </button>
        </div>
      </section>
    </AppShell>
  )
}
