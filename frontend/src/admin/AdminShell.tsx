import { Link, NavLink, useNavigate } from 'react-router-dom'
import { useAdminAuth } from './AdminAuthContext'
import './AdminShell.css'

type NavGroup = {
  label: string
  icon: string
  path: string
  roles?: string[] // undefined = accessible by all admin roles
}

const NAV: NavGroup[] = [
  { label: 'Overview', icon: '▦', path: '/admin' },
  { label: 'Users', icon: '👤', path: '/admin/users', roles: ['super_admin', 'user_moderator', 'admin'] },
  { label: 'Contests', icon: '🏆', path: '/admin/contests', roles: ['super_admin', 'contest_admin', 'admin'] },
  { label: 'Live Monitor', icon: '📡', path: '/admin/live', roles: ['super_admin', 'contest_admin', 'admin'] },
  { label: 'Leaderboard', icon: '📊', path: '/admin/leaderboard', roles: ['super_admin', 'contest_admin', 'admin'] },
  { label: 'Content', icon: '✏️', path: '/admin/content', roles: ['super_admin', 'content_manager', 'admin'] },
  { label: 'Badges', icon: '🎖️', path: '/admin/badges', roles: ['super_admin', 'content_manager', 'admin'] },
  { label: 'Subscriptions', icon: '💳', path: '/admin/subscriptions', roles: ['super_admin', 'contest_admin', 'admin'] },
  { label: 'Payments', icon: '💰', path: '/admin/payments', roles: ['super_admin', 'contest_admin', 'admin'] },
  { label: 'Reports', icon: '📋', path: '/admin/reports', roles: ['super_admin', 'contest_admin', 'support_admin', 'admin'] },
  { label: 'Security', icon: '🔒', path: '/admin/security', roles: ['super_admin', 'user_moderator', 'admin'] },
  { label: 'Support', icon: '🎫', path: '/admin/support', roles: ['super_admin', 'support_admin', 'admin'] },
  { label: 'CMS', icon: '📄', path: '/admin/cms', roles: ['super_admin', 'content_manager', 'admin'] },
  { label: 'Notifications', icon: '🔔', path: '/admin/notifications', roles: ['super_admin', 'contest_admin', 'support_admin', 'admin'] },
  { label: 'System', icon: '⚙️', path: '/admin/system', roles: ['super_admin', 'contest_admin', 'admin'] },
  { label: 'Roles', icon: '🛡️', path: '/admin/roles', roles: ['super_admin'] },
]

type AdminShellProps = {
  children: React.ReactNode
}

export function AdminShell({ children }: AdminShellProps) {
  const { admin, logout, hasRole } = useAdminAuth()
  const navigate = useNavigate()

  const adminRoles = admin?.roles?.map((r) => r.name) ?? []

  const visibleNav = NAV.filter((item) => {
    if (!item.roles) return true
    return item.roles.some((r) => adminRoles.includes(r))
  })

  async function handleLogout() {
    await logout()
    navigate('/admin/login')
  }

  return (
    <div className="admin-shell">
      {/* ── Sidebar ──────────────────────────────────────────────────────── */}
      <aside className="admin-sidebar">
        <Link to="/admin" className="admin-sidebar__brand">
          <span className="admin-sidebar__brand-dot" aria-hidden="true" />
          TC Admin
        </Link>

        <nav className="admin-sidebar__nav" aria-label="Admin navigation">
          {visibleNav.map((item) => (
            <NavLink
              key={item.path}
              to={item.path}
              end={item.path === '/admin'}
              className={({ isActive }) =>
                `admin-sidebar__link ${isActive ? 'is-active' : ''}`
              }
            >
              <span className="admin-sidebar__icon" aria-hidden="true">
                {item.icon}
              </span>
              {item.label}
            </NavLink>
          ))}
        </nav>

        <div className="admin-sidebar__footer">
          <div className="admin-sidebar__user">
            <span className="admin-sidebar__user-name">{admin?.name ?? 'Admin'}</span>
            <span className="admin-sidebar__user-role">
              {adminRoles.join(', ') || '—'}
            </span>
          </div>
          <button className="admin-sidebar__logout" onClick={handleLogout}>
            Sign out
          </button>
        </div>
      </aside>

      {/* ── Main content ─────────────────────────────────────────────────── */}
      <div className="admin-main">
        <header className="admin-topbar">
          <div className="admin-topbar__left">
            <Link to="/dashboard" className="admin-topbar__back">
              ← Public site
            </Link>
          </div>
          <div className="admin-topbar__right">
            <span className="admin-topbar__badge">
              {hasRole('super_admin') ? 'Super Admin' : adminRoles[0] ?? 'Admin'}
            </span>
          </div>
        </header>

        <div className="admin-content">{children}</div>
      </div>
    </div>
  )
}
