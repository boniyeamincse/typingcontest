import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAdminAuth } from '../admin/AdminAuthContext'
import './AdminLoginPage.css'

export default function AdminLoginPage() {
  const { login, isAdminAuthenticated } = useAdminAuth()
  const navigate = useNavigate()

  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const [busy, setBusy] = useState(false)

  if (isAdminAuthenticated) {
    navigate('/admin', { replace: true })
    return null
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    setError('')
    setBusy(true)
    try {
      await login(email, password)
      navigate('/admin', { replace: true })
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Login failed')
    } finally {
      setBusy(false)
    }
  }

  return (
    <div className="admin-login-wrap">
      <form className="admin-login-card" onSubmit={handleSubmit}>
        <div className="admin-login-brand">
          <span className="admin-login-dot" aria-hidden="true" />
          TC Admin
        </div>
        <h1 className="admin-login-title">Sign in to admin panel</h1>

        {error ? <p className="admin-login-error">{error}</p> : null}

        <label className="admin-login-label">
          Email
          <input
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
            autoFocus
            placeholder="admin@example.com"
            className="admin-login-input"
          />
        </label>

        <label className="admin-login-label">
          Password
          <input
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
            placeholder="••••••••"
            className="admin-login-input"
          />
        </label>

        <button type="submit" className="btn btn-primary" style={{ marginTop: 4 }} disabled={busy}>
          {busy ? 'Signing in…' : 'Sign in'}
        </button>
      </form>
    </div>
  )
}
