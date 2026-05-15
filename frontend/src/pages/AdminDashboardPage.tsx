import { Link } from 'react-router-dom'
import { AppShell } from '../components/AppShell'
import { ADMIN_SECTIONS } from '../admin/adminSections'
import './AdminDashboardPage.css'

const MAIN_MENU = [
  { label: 'Dashboard', path: '/admin/dashboard' },
  { label: 'Users', path: '/admin/section/users' },
  { label: 'Contests', path: '/admin/section/contests' },
  { label: 'Live Matches', path: '/admin/section/live-matches' },
  { label: 'Leaderboards', path: '/admin/section/leaderboards' },
  { label: 'Subscriptions', path: '/admin/section/subscriptions' },
  { label: 'Payments', path: '/admin/section/payments' },
  { label: 'Badges & Rewards', path: '/admin/section/badges-rewards' },
  { label: 'Reports & Analytics', path: '/admin/section/reports-analytics' },
  { label: 'Typing Content', path: '/admin/section/typing-content' },
  { label: 'Notifications', path: '/admin/section/notifications' },
  { label: 'Support Tickets', path: '/admin/section/support-tickets' },
  { label: 'Advertisements', path: '/admin/section/advertisements' },
  { label: 'Sponsors', path: '/admin/section/sponsors' },
  { label: 'CMS Management', path: '/admin/section/cms-management' },
  { label: 'Settings', path: '/admin/section/settings' },
  { label: 'Admins & Roles', path: '/admin/section/admins-roles' },
  { label: 'Activity Logs', path: '/admin/section/activity-logs' },
  { label: 'System Monitoring', path: '/admin/section/system-monitoring' },
  { label: 'API Management', path: '/admin/section/api-management' },
  { label: 'Security Center', path: '/admin/section/security-center' },
  { label: 'Backup & Maintenance', path: '/admin/section/backup-maintenance' },
  { label: 'Logout', path: '/login' },
]

const WIDGETS = [
  'Total Users',
  'Active Users',
  'Online Players',
  'Active Contests',
  'Revenue',
  'Top Players',
  'Live Matches',
  'Server Status',
]

function ModuleCard({ title, items, features }: { title: string; items?: string[]; features?: string[] }) {
  return (
    <article className="admin-dashboard-card surface-card">
      <h3>{title}</h3>
      {items?.length ? (
        <ul>
          {items.map((item) => (
            <li key={item}>{item}</li>
          ))}
        </ul>
      ) : null}
      {features?.length ? (
        <p className="admin-dashboard-features">{features.join(' • ')}</p>
      ) : null}
    </article>
  )
}

export default function AdminDashboardPage() {
  return (
    <AppShell
      title="TypingContest Admin Dashboard"
      subtitle="Scalable control center for contests, users, operations, and platform security."
      actions={
        <>
          <Link to="/admin/contests" className="btn-primary">
            Open Contest Studio
          </Link>
          <Link to="/dashboard" className="btn-secondary">
            User Dashboard
          </Link>
        </>
      }
    >
      <div className="admin-dashboard-layout">
        <aside className="admin-dashboard-sidebar surface-card">
          <h2>Main Sidebar Menu</h2>
          <ul>
            {MAIN_MENU.map((menu) => (
              <li key={menu.label}>
                <Link to={menu.path}>{menu.label}</Link>
              </li>
            ))}
          </ul>
        </aside>

        <section className="admin-dashboard-main">
          <section className="admin-dashboard-widgets surface-card">
            <h2>Overview Widgets</h2>
            <div className="admin-dashboard-widget-grid">
              {WIDGETS.map((widget) => (
                <div key={widget} className="admin-dashboard-widget">
                  <span>{widget}</span>
                  <strong>Live</strong>
                </div>
              ))}
            </div>
          </section>

          <section className="admin-dashboard-modules">
            {ADMIN_SECTIONS.map((section) => (
              <ModuleCard
                key={section.slug}
                title={section.title}
                items={section.submenus}
                features={section.features}
              />
            ))}
          </section>
        </section>
      </div>
    </AppShell>
  )
}