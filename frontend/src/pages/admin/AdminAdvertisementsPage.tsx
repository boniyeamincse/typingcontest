import { useEffect, useState } from 'react'
import { AdminShell } from '../../admin/AdminShell'
import '../../admin/AdminShell.css'
import { fetchAdvertisementsOverview } from '../../api/admin'

type AdvertisementsOverview = {
  metrics?: {
    active_banners?: number
    sponsored_contests?: number
    monthly_impressions?: number
    monthly_revenue?: number
  }
  placements?: { name: string; status: string }[]
  next_actions?: string[]
}

export default function AdminAdvertisementsPage() {
  const [data, setData] = useState<AdvertisementsOverview | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    fetchAdvertisementsOverview()
      .then((res) => setData(res.data as AdvertisementsOverview))
      .catch((err) => setError(err.message))
      .finally(() => setLoading(false))
  }, [])

  return (
    <AdminShell>
      <h1 className="admin-page-title">Advertisement System</h1>
      <p className="admin-page-subtitle">Placeholder operational dashboard for ad banners and sponsored inventory.</p>

      {loading ? <div className="admin-loading">Loading...</div> : null}
      {error ? <div className="admin-loading" style={{ color: 'var(--danger)' }}>{error}</div> : null}

      {!loading && !error && data ? (
        <>
          <div className="admin-stat-grid">
            <div className="admin-stat"><span className="admin-stat__label">Active banners</span><span className="admin-stat__value">{data.metrics?.active_banners ?? 0}</span></div>
            <div className="admin-stat"><span className="admin-stat__label">Sponsored contests</span><span className="admin-stat__value">{data.metrics?.sponsored_contests ?? 0}</span></div>
            <div className="admin-stat"><span className="admin-stat__label">Monthly impressions</span><span className="admin-stat__value">{data.metrics?.monthly_impressions ?? 0}</span></div>
            <div className="admin-stat"><span className="admin-stat__label">Monthly revenue</span><span className="admin-stat__value">{data.metrics?.monthly_revenue ?? 0}</span></div>
          </div>

          <div className="admin-card" style={{ marginBottom: 20 }}>
            <h2 style={{ marginTop: 0 }}>Ad placements</h2>
            <div style={{ display: 'grid', gap: 10 }}>
              {(data.placements ?? []).map((placement) => (
                <div key={placement.name} style={{ display: 'flex', justifyContent: 'space-between' }}>
                  <span>{placement.name}</span>
                  <span className={`badge ${placement.status === 'available' ? 'badge--green' : 'badge--gray'}`}>{placement.status}</span>
                </div>
              ))}
            </div>
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
