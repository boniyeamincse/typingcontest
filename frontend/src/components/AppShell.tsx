import { Link, useLocation } from 'react-router-dom'
import { useAuth } from '../auth/AuthContext'

type AppShellProps = {
  title: string
  subtitle?: string
  children: React.ReactNode
  actions?: React.ReactNode
}

function NavItem({ to, label }: { to: string; label: string }) {
  const location = useLocation()
  const isActive = location.pathname === to || location.pathname.startsWith(`${to}/`)

  return (
    <Link to={to} className={`shell-nav__link ${isActive ? 'is-active' : ''}`}>
      {label}
    </Link>
  )
}

export function AppShell({ title, subtitle, children, actions }: AppShellProps) {
  const { isAuthenticated, user } = useAuth()

  return (
    <div className="shell-wrap">
      <header className="shell-topbar">
        <Link to="/dashboard" className="shell-brand">
          <span className="shell-brand__dot" aria-hidden="true" />
          TypingContest
        </Link>

        <nav className="shell-nav" aria-label="Primary">
          <NavItem to="/contests" label="Contests" />
          {isAuthenticated ? <NavItem to="/dashboard" label="Dashboard" /> : null}
          {!isAuthenticated ? <NavItem to="/login" label="Login" /> : null}
          {!isAuthenticated ? <NavItem to="/register" label="Register" /> : null}
        </nav>

        <div className="shell-user">{isAuthenticated ? `Hi, ${user?.name ?? 'Player'}` : 'Guest'}</div>
      </header>

      <main className="shell-content">
        <section className="page-hero">
          <div>
            <h1>{title}</h1>
            {subtitle ? <p>{subtitle}</p> : null}
          </div>
          {actions ? <div className="page-hero__actions">{actions}</div> : null}
        </section>

        {children}
      </main>
    </div>
  )
}