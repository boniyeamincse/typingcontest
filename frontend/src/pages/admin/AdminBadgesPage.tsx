import { useEffect, useState } from 'react'
import { AdminShell } from '../../admin/AdminShell'
import '../../admin/AdminShell.css'
import { fetchBadges } from '../../api/admin'

type BadgeRow = {
  icon_url?: string
  name?: string
  description?: string
  active?: boolean
}

export default function AdminBadgesPage() {
  const [rows, setRows] = useState<BadgeRow[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    fetchBadges()
      .then((r) => setRows(r.data as BadgeRow[]))
      .catch((e) => setError(e.message))
      .finally(() => setLoading(false))
  }, [])

  return (
    <AdminShell>
      <h1 className="admin-page-title">Badges</h1>
      <p className="admin-page-subtitle">View and manage achievement badges.</p>

      {loading ? (
        <div className="admin-loading">Loading…</div>
      ) : error ? (
        <div className="admin-loading" style={{ color: 'var(--danger)' }}>{error}</div>
      ) : rows.length === 0 ? (
        <div className="admin-empty">No badges configured yet.</div>
      ) : (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))', gap: 16 }}>
          {rows.map((b, i) => (
            <div key={i} className="admin-card" style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
              {b.icon_url ? (
                <img src={String(b.icon_url)} alt={String(b.name)} style={{ width: 48, height: 48, objectFit: 'contain', borderRadius: 8, background: 'rgba(21,37,51,0.06)', padding: 6 }} />
              ) : null}
              <div style={{ fontWeight: 700, fontSize: '0.9rem' }}>{String(b.name ?? 'Badge')}</div>
              <div style={{ fontSize: '0.78rem', color: 'var(--muted)' }}>{String(b.description ?? '')}</div>
              <span className={`badge ${b.active ? 'badge--green' : 'badge--gray'}`}>{b.active ? 'Active' : 'Inactive'}</span>
            </div>
          ))}
        </div>
      )}
    </AdminShell>
  )
}
