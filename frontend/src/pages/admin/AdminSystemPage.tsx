import { useEffect, useState } from 'react'
import { AdminShell } from '../../admin/AdminShell'
import '../../admin/AdminShell.css'
import { fetchSystemMonitoring } from '../../api/admin'

type Monitor = {
  cpu_usage?: number
  memory_usage?: number
  disk_usage?: number
  db_status?: string
  queue_status?: string
  cache_status?: string
  active_jobs?: number
  failed_jobs?: number
  recent_activity?: Record<string, unknown>[]
}

function UsageBar({ label, value }: { label: string; value: number }) {
  const color = value > 85 ? 'var(--danger)' : value > 60 ? 'var(--action)' : 'var(--ok)'
  return (
    <div className="admin-card" style={{ flex: '1 1 200px', minWidth: 0 }}>
      <div style={{ fontSize: '0.78rem', fontWeight: 700, color: 'var(--muted)', marginBottom: 8, textTransform: 'uppercase', letterSpacing: '0.05em' }}>{label}</div>
      <div style={{ fontSize: '1.6rem', fontWeight: 800, color, marginBottom: 8 }}>{value}%</div>
      <div style={{ background: 'rgba(21,37,51,0.1)', borderRadius: 6, height: 6 }}>
        <div style={{ width: `${value}%`, background: color, height: 6, borderRadius: 6, transition: 'width 0.4s' }} />
      </div>
    </div>
  )
}

export default function AdminSystemPage() {
  const [data, setData] = useState<Monitor | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  function load() {
    setLoading(true)
    fetchSystemMonitoring()
      .then((r) => setData(r.data as Monitor))
      .catch((e) => setError(e.message))
      .finally(() => setLoading(false))
  }

  useEffect(() => { load(); const id = setInterval(load, 30_000); return () => clearInterval(id) }, [])

  return (
    <AdminShell>
      <h1 className="admin-page-title">System Monitoring</h1>
      <p className="admin-page-subtitle">Live infrastructure metrics. Refreshes every 30 seconds.</p>

      {loading && !data ? (
        <div className="admin-loading">Loading…</div>
      ) : error ? (
        <div className="admin-loading" style={{ color: 'var(--danger)' }}>{error}</div>
      ) : data ? (
        <>
          <div className="admin-stat-grid" style={{ marginBottom: 24 }}>
            <UsageBar label="CPU" value={data.cpu_usage ?? 0} />
            <UsageBar label="Memory" value={data.memory_usage ?? 0} />
            <UsageBar label="Disk" value={data.disk_usage ?? 0} />
          </div>

          <div style={{ display: 'flex', gap: 16, flexWrap: 'wrap', marginBottom: 24 }}>
            {[
              { label: 'Database', value: data.db_status ?? '—' },
              { label: 'Queue', value: data.queue_status ?? '—' },
              { label: 'Cache', value: data.cache_status ?? '—' },
            ].map(({ label, value }) => (
              <div key={label} className="admin-card" style={{ flex: '1 1 140px', minWidth: 0 }}>
                <div style={{ fontSize: '0.78rem', fontWeight: 700, color: 'var(--muted)', textTransform: 'uppercase', letterSpacing: '0.05em', marginBottom: 8 }}>{label}</div>
                <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                  <span style={{ width: 10, height: 10, borderRadius: '50%', background: value === 'ok' ? 'var(--ok)' : 'var(--danger)', flexShrink: 0 }} />
                  <span style={{ fontWeight: 700, textTransform: 'capitalize' }}>{value}</span>
                </div>
              </div>
            ))}
            <div className="admin-card" style={{ flex: '1 1 140px', minWidth: 0 }}>
              <div style={{ fontSize: '0.78rem', fontWeight: 700, color: 'var(--muted)', textTransform: 'uppercase', letterSpacing: '0.05em', marginBottom: 8 }}>Jobs</div>
              <div style={{ fontWeight: 700 }}>
                Active: <span style={{ color: 'var(--ok)' }}>{data.active_jobs ?? 0}</span>
                &nbsp;&nbsp;Failed: <span style={{ color: 'var(--danger)' }}>{data.failed_jobs ?? 0}</span>
              </div>
            </div>
          </div>

          {data.recent_activity && data.recent_activity.length > 0 && (
            <>
              <h2 style={{ fontFamily: 'Chakra Petch, sans-serif', fontSize: '0.9rem', color: 'var(--muted)', marginBottom: 10, textTransform: 'uppercase', letterSpacing: '0.05em' }}>
                Recent activity
              </h2>
              <div className="admin-table-wrap">
                <table className="admin-table">
                  <thead>
                    <tr>
                      {Object.keys(data.recent_activity[0]).map((k) => <th key={k}>{k.replace(/_/g, ' ')}</th>)}
                    </tr>
                  </thead>
                  <tbody>
                    {data.recent_activity.map((row, i) => (
                      <tr key={i}>
                        {Object.values(row).map((v, j) => <td key={j} style={{ fontSize: '0.8rem', color: 'var(--muted)' }}>{String(v ?? '—')}</td>)}
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </>
          )}
        </>
      ) : null}
    </AdminShell>
  )
}
