import { useEffect, useState } from 'react'
import { AdminShell } from '../../admin/AdminShell'
import '../../admin/AdminShell.css'
import { fetchSponsorsOverview } from '../../api/admin'

type SponsorsOverview = {
  metrics?: {
    total_sponsors?: number
    active_campaigns?: number
    sponsored_events?: number
  }
  sponsor_tiers?: string[]
  next_actions?: string[]
}

export default function AdminSponsorsPage() {
  const [data, setData] = useState<SponsorsOverview | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    fetchSponsorsOverview()
      .then((res) => setData(res.data as SponsorsOverview))
      .catch((err) => setError(err.message))
      .finally(() => setLoading(false))
  }, [])

  return (
    <AdminShell>
      <h1 className="admin-page-title">Sponsor Management</h1>
      <p className="admin-page-subtitle">Placeholder management surface for sponsor relationships and campaigns.</p>

      {loading ? <div className="admin-loading">Loading...</div> : null}
      {error ? <div className="admin-loading" style={{ color: 'var(--danger)' }}>{error}</div> : null}

      {!loading && !error && data ? (
        <>
          <div className="admin-stat-grid">
            <div className="admin-stat"><span className="admin-stat__label">Total sponsors</span><span className="admin-stat__value">{data.metrics?.total_sponsors ?? 0}</span></div>
            <div className="admin-stat"><span className="admin-stat__label">Active campaigns</span><span className="admin-stat__value">{data.metrics?.active_campaigns ?? 0}</span></div>
            <div className="admin-stat"><span className="admin-stat__label">Sponsored events</span><span className="admin-stat__value">{data.metrics?.sponsored_events ?? 0}</span></div>
          </div>

          <div className="admin-card" style={{ marginBottom: 20 }}>
            <h2 style={{ marginTop: 0 }}>Sponsor tiers</h2>
            <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
              {(data.sponsor_tiers ?? []).map((tier) => (
                <span key={tier} className="badge badge--blue">{tier}</span>
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
