import { Link, NavLink, useNavigate } from 'react-router-dom'
import { useAdminAuth } from './AdminAuthContext'
import { ADMIN_SECTIONS, getAdminSectionPath } from './adminSections'
import './AdminShell.css'

type NavGroup = {
  label: string
  icon: string
  path: string
  roles?: string[] // undefined = accessible by all admin roles
}

const SECTION_ICONS: Record<string, string> = {
  'dashboard-overview': '▦',
  'user-management': 'U',
  'contest-management': 'C',
  'typing-engine-control': 'T',
  'leaderboard-system': 'L',
  'subscription-management': 'S',
  'payment-system': 'P',
  'badge-reward-system': 'B',
  'notification-center': 'N',
  'analytics-reports': 'R',
  'live-system-monitor': 'M',
  'security-center': 'X',
  'support-system': 'H',
  'cms-management': 'W',
  'advertisement-system': 'A',
  'sponsor-management': 'O',
  'system-settings': 'G',
  'admin-management': 'D',
  'api-management': 'I',
  'backup-maintenance': 'K',
  'system-monitoring': 'Y',
}

const NAV: NavGroup[] = ADMIN_SECTIONS.map((section) => ({
  label: section.title,
  icon: SECTION_ICONS[section.slug] ?? '•',
  path: getAdminSectionPath(section),
}))

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
