import { useEffect, useState } from 'react'
import { AreaChart, Area, XAxis, YAxis, Tooltip, ResponsiveContainer } from 'recharts'
import { AdminShell } from '../../admin/AdminShell'
import '../../admin/AdminShell.css'
import { fetchDashboardOverview, type DashboardOverview } from '../../api/admin'

function fmt(n: number) {
  if (n >= 1_000_000) return (n / 1_000_000).toFixed(1) + 'M'
  if (n >= 1_000) return (n / 1_000).toFixed(1) + 'k'
  return String(n)
}

function HealthDot({ status }: { status: string }) {
  const color = status === 'ok' ? 'var(--ok)' : status === 'down' ? 'var(--danger)' : '#b8830d'
  return (
    <span
      style={{
        display: 'inline-block',
        width: 8,
        height: 8,
        borderRadius: '50%',
        background: color,
        marginRight: 5,
        verticalAlign: 'middle',
      }}
      aria-hidden="true"
    />
  )
}

export default function AdminOverviewPage() {
  const [data, setData] = useState<DashboardOverview | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [days, setDays] = useState(7)

  useEffect(() => {
    setLoading(true)
    setError('')
    fetchDashboardOverview(days)
      .then((res: { data: DashboardOverview }) => setData(res.data))
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false))
  }, [days])

  return (
    <AdminShell>
      <h1 className="admin-page-title">Dashboard Overview</h1>
      <p className="admin-page-subtitle">Real-time platform health and activity snapshot.</p>

      {loading ? (
        <div className="admin-loading">Loading…</div>
      ) : error ? (
        <div className="admin-loading" style={{ color: 'var(--danger)' }}>{error}</div>
      ) : data ? (
        <>
          {/* ── Stat cards ──────────────────────────────────────────────── */}
          <div className="admin-stat-grid">
            <div className="admin-stat">
              <span className="admin-stat__label">Total users</span>
              <span className="admin-stat__value">{fmt(data.summary.total_users)}</span>
            </div>
            <div className="admin-stat">
              <span className="admin-stat__label">Active (7d)</span>
              <span className="admin-stat__value">{fmt(data.summary.active_users)}</span>
            </div>
            <div className="admin-stat">
              <span className="admin-stat__label">Pro / VIP</span>
              <span className="admin-stat__value">{fmt(data.summary.pro_users)}</span>
            </div>
            <div className="admin-stat">
              <span className="admin-stat__label">Revenue</span>
              <span className="admin-stat__value">${fmt(data.summary.revenue)}</span>
            </div>
            <div className="admin-stat">
              <span className="admin-stat__label">Live contests</span>
              <span className="admin-stat__value">{data.summary.active_contests}</span>
            </div>
            <div className="admin-stat">
              <span className="admin-stat__label">Live players</span>
              <span className="admin-stat__value">{data.summary.live_players}</span>
              <span className="admin-stat__sub">● typing now</span>
            </div>
            <div className="admin-stat">
              <span className="admin-stat__label">New today</span>
              <span className="admin-stat__value">{data.summary.today_new_users}</span>
            </div>
          </div>

          {/* ── Growth chart ─────────────────────────────────────────────── */}
          <div className="admin-card" style={{ marginBottom: 24 }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 18 }}>
              <h2 style={{ fontSize: '1rem', fontWeight: 700, color: 'var(--ink)' }}>User growth</h2>
              <select
                value={days}
                onChange={(e) => setDays(Number(e.target.value))}
                style={{
                  padding: '5px 10px',
                  borderRadius: 8,
                  border: '1px solid rgba(21,37,51,0.18)',
                  fontSize: '0.82rem',
                  color: 'var(--ink)',
                  background: '#fff',
                }}
              >
                {[7, 14, 30].map((d) => (
                  <option key={d} value={d}>Last {d} days</option>
                ))}
              </select>
            </div>
            {data.daily_growth.length ? (
              <ResponsiveContainer width="100%" height={200}>
                <AreaChart data={data.daily_growth} margin={{ top: 4, right: 4, bottom: 0, left: -10 }}>
                  <defs>
                    <linearGradient id="gradUsers" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="5%" stopColor="#138f95" stopOpacity={0.25} />
                      <stop offset="95%" stopColor="#138f95" stopOpacity={0} />
                    </linearGradient>
                  </defs>
                  <XAxis
                    dataKey="date"
                    tick={{ fontSize: 11, fill: '#4c6578' }}
                    tickFormatter={(v: string) => v.slice(5)}
                    axisLine={false}
                    tickLine={false}
                  />
                  <YAxis
                    tick={{ fontSize: 11, fill: '#4c6578' }}
                    axisLine={false}
                    tickLine={false}
                    allowDecimals={false}
                  />
                  <Tooltip
                    contentStyle={{ fontSize: '0.8rem', borderRadius: 8, border: '1px solid rgba(21,37,51,0.12)' }}
                  />
                  <Area
                    type="monotone"
                    dataKey="users"
                    stroke="#138f95"
                    strokeWidth={2}
                    fill="url(#gradUsers)"
                  />
                </AreaChart>
              </ResponsiveContainer>
            ) : (
              <div className="admin-empty">No growth data yet.</div>
            )}
          </div>

          {/* ── System health ─────────────────────────────────────────────── */}
          <div className="admin-card">
            <h2 style={{ fontSize: '1rem', fontWeight: 700, color: 'var(--ink)', marginBottom: 14 }}>
              System health
            </h2>
            <div style={{ display: 'flex', gap: 24, flexWrap: 'wrap' }}>
              {Object.entries(data.system_health).map(([service, status]) => (
                <div key={service} style={{ fontSize: '0.85rem', color: 'var(--ink)' }}>
                  <HealthDot status={String(status)} />
                  <span style={{ textTransform: 'capitalize', fontWeight: 600 }}>{service}</span>
                  <span style={{ color: 'var(--muted)', marginLeft: 4 }}>— {String(status)}</span>
                </div>
              ))}
            </div>
          </div>
        </>
      ) : null}
    </AdminShell>
  )
}
