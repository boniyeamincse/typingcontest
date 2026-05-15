import { useEffect, useState } from 'react'
import { AdminShell } from '../../admin/AdminShell'
import '../../admin/AdminShell.css'
import { fetchSystemMonitoringOverview } from '../../api/admin'

type SystemMonitoringOverview = {
  snapshot?: {
    cpu_usage?: number | null
    ram_usage?: number | null
    redis_status?: string
    queue_worker_status?: string
    websocket_status?: string
    checked_at?: string
  }
  queue_jobs?: { driver?: string; status?: string }
  notes?: string[]
}

export default function AdminInfraMonitoringPage() {
  const [data, setData] = useState<SystemMonitoringOverview | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    fetchSystemMonitoringOverview()
      .then((res) => setData(res.data as SystemMonitoringOverview))
      .catch((err) => setError(err.message))
      .finally(() => setLoading(false))
  }, [])

  return (
    <AdminShell>
      <h1 className="admin-page-title">System Monitoring</h1>
      <p className="admin-page-subtitle">Infrastructure and runtime health placeholder module.</p>

      {loading ? <div className="admin-loading">Loading...</div> : null}
      {error ? <div className="admin-loading" style={{ color: 'var(--danger)' }}>{error}</div> : null}

      {!loading && !error && data ? (
        <>
          <div className="admin-stat-grid">
            <div className="admin-stat"><span className="admin-stat__label">Redis</span><span className="admin-stat__value">{data.snapshot?.redis_status ?? 'unknown'}</span></div>
            <div className="admin-stat"><span className="admin-stat__label">Queue worker</span><span className="admin-stat__value">{data.snapshot?.queue_worker_status ?? 'unknown'}</span></div>
            <div className="admin-stat"><span className="admin-stat__label">WebSocket</span><span className="admin-stat__value">{data.snapshot?.websocket_status ?? 'unknown'}</span></div>
            <div className="admin-stat"><span className="admin-stat__label">Queue driver</span><span className="admin-stat__value">{data.queue_jobs?.driver ?? 'n/a'}</span></div>
          </div>

          <div className="admin-card" style={{ marginBottom: 20 }}>
            <h2 style={{ marginTop: 0 }}>Snapshot</h2>
            <p>CPU usage: {data.snapshot?.cpu_usage ?? 'placeholder'}</p>
            <p>RAM usage: {data.snapshot?.ram_usage ?? 'placeholder'}</p>
            <p>Checked at: {data.snapshot?.checked_at ?? 'n/a'}</p>
          </div>

          <div className="admin-card">
            <h2 style={{ marginTop: 0 }}>Notes</h2>
            <ul>
              {(data.notes ?? []).map((note) => (
                <li key={note}>{note}</li>
              ))}
            </ul>
          </div>
        </>
      ) : null}
    </AdminShell>
  )
}
