import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../auth/AuthContext'

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
    <main className="dashboard-page">
      <section className="dashboard-card">
        <h1>Welcome, {user?.name}</h1>
        <p className="lead-text">Your backend and frontend auth modules are connected.</p>

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

        <button className="logout-btn" onClick={onLogout} disabled={loading}>
          {loading ? 'Signing out...' : 'Logout'}
        </button>
      </section>
    </main>
  )
}
