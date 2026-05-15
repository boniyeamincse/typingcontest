import { Link } from 'react-router-dom'
import { AppShell } from '../components/AppShell'
import './AdminDashboardPage.css'

const MAIN_MENU = [
  'Dashboard',
  'Users',
  'Contests',
  'Live Matches',
  'Leaderboards',
  'Subscriptions',
  'Payments',
  'Badges & Rewards',
  'Reports & Analytics',
  'Typing Content',
  'Notifications',
  'Support Tickets',
  'Advertisements',
  'Sponsors',
  'CMS Management',
  'Settings',
  'Admins & Roles',
  'Activity Logs',
  'System Monitoring',
  'API Management',
  'Security Center',
  'Backup & Maintenance',
  'Logout',
]

const USER_SUBMENUS = [
  'All Users',
  'Free Users',
  'Pro Users',
  'VIP Users',
  'Banned Users',
  'Online Users',
  'User Reports',
  'User Activity Logs',
]

const CONTEST_SUBMENUS = [
  'All Contests',
  'Daily Contests',
  'Weekly Contests',
  'Monthly Contests',
  'Tournament Events',
  'Create Contest',
  'Contest Categories',
  'Contest Results',
]

const LEADERBOARD_SUBMENUS = [
  'Global Rankings',
  'Country Rankings',
  'Daily Rankings',
  'Weekly Rankings',
  'Monthly Rankings',
  'Top 10 Players',
]

const ANALYTICS_SUBMENUS = [
  'User Analytics',
  'Contest Analytics',
  'Revenue Reports',
  'Traffic Reports',
  'Typing Statistics',
  'Growth Reports',
]

const CONTENT_SUBMENUS = [
  'Paragraph Library',
  'Add Paragraph',
  'Categories',
  'Programming Texts',
  'English Texts',
  'Bangla Texts',
]

const SETTINGS_SUBMENUS = [
  'General Settings',
  'Contest Settings',
  'Ranking Settings',
  'Notification Settings',
  'Theme Settings',
  'Localization',
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
              <li key={menu}>{menu}</li>
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
            <ModuleCard
              title="Users Module"
              items={USER_SUBMENUS}
              features={[
                'Search users',
                'Ban/unban',
                'Reset password',
                'Contest history',
                'Suspicious activity review',
              ]}
            />

            <ModuleCard
              title="Contests Module"
              items={CONTEST_SUBMENUS}
              features={[
                'Create contests',
                'Edit contests',
                'Cancel contests',
                'Clone contests',
                'Schedule contests',
              ]}
            />

            <ModuleCard
              title="Leaderboards"
              items={LEADERBOARD_SUBMENUS}
              features={['Reset rankings', 'Recalculate score', 'Remove fake scores']}
            />

            <ModuleCard
              title="Reports & Analytics"
              items={ANALYTICS_SUBMENUS}
              features={['Daily users', 'Revenue chart', 'Contest engagement', 'User retention']}
            />

            <ModuleCard
              title="Typing Content"
              items={CONTENT_SUBMENUS}
              features={['Upload content', 'Approve/reject text', 'Difficulty level management']}
            />

            <ModuleCard
              title="Settings"
              items={SETTINGS_SUBMENUS}
              features={['Spatie role permissions', 'Localization', 'Theme controls']}
            />

            <ModuleCard
              title="Operations"
              features={[
                'Live participants and WPM monitoring',
                'Payment verification and gateway controls',
                'Support ticket management',
                'Activity and audit logs',
              ]}
            />

            <ModuleCard
              title="Security & Infrastructure"
              features={[
                'Suspicious login detection',
                'Cheat detection and IP bans',
                'CPU/RAM monitoring',
                'API rate limits and token management',
                'Backup and maintenance workflows',
              ]}
            />
          </section>
        </section>
      </div>
    </AppShell>
  )
}