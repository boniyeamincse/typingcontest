import { useEffect, useState } from 'react'
import { AdminShell } from '../../admin/AdminShell'
import '../../admin/AdminShell.css'
import { fetchBackupMaintenanceOverview } from '../../api/admin'

type BackupMaintenanceOverview = {
  metrics?: {
    last_backup_at?: string | null
    backup_status?: string
    maintenance_mode?: boolean
  }
  operations?: string[]
  next_actions?: string[]
}

export default function AdminBackupMaintenancePage() {
  const [data, setData] = useState<BackupMaintenanceOverview | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    fetchBackupMaintenanceOverview()
      .then((res) => setData(res.data as BackupMaintenanceOverview))
      .catch((err) => setError(err.message))
      .finally(() => setLoading(false))
  }, [])

  return (
    <AdminShell>
      <h1 className="admin-page-title">Backup & Maintenance</h1>
      <p className="admin-page-subtitle">Operational placeholder for backup pipeline and maintenance controls.</p>

      {loading ? <div className="admin-loading">Loading...</div> : null}
      {error ? <div className="admin-loading" style={{ color: 'var(--danger)' }}>{error}</div> : null}

      {!loading && !error && data ? (
        <>
          <div className="admin-stat-grid">
            <div className="admin-stat"><span className="admin-stat__label">Last backup</span><span className="admin-stat__value">{data.metrics?.last_backup_at ? 'Done' : 'N/A'}</span></div>
            <div className="admin-stat"><span className="admin-stat__label">Backup status</span><span className="admin-stat__value">{data.metrics?.backup_status ?? 'unknown'}</span></div>
            <div className="admin-stat"><span className="admin-stat__label">Maintenance mode</span><span className="admin-stat__value">{data.metrics?.maintenance_mode ? 'On' : 'Off'}</span></div>
          </div>

          <div className="admin-card" style={{ marginBottom: 20 }}>
            <h2 style={{ marginTop: 0 }}>Available operations</h2>
            <ul>
              {(data.operations ?? []).map((item) => (
                <li key={item}>{item}</li>
              ))}
            </ul>
          </div>

          <div className="admin-card">
            <h2 style={{ marginTop: 0 }}>Next actions</h2>
            <ul>
              {(data.next_actions ?? []).map((action) => (
                <li key={action}>{action}</li>
              ))}
            </ul>
          </div>
        </>
      ) : null}
    </AdminShell>
  )
}
