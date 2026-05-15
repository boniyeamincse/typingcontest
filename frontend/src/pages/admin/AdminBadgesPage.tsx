import { useEffect, useState } from 'react'
import { AdminShell } from '../../admin/AdminShell'
import '../../admin/AdminShell.css'
import { fetchBadges } from '../../api/admin'

type BadgeRow = {
  id: number
  name: string
  slug: string
  icon_url?: string
  description: string
  requirement_type: 'contest_count' | 'accuracy' | 'wpm' | 'rank' | 'special'
  requirement_value: number
  is_premium: boolean
  active?: boolean
}

export default function AdminBadgesPage() {
  const [rows, setRows] = useState<BadgeRow[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  const [search, setSearch] = useState('')

  useEffect(() => {
    fetchBadges()
      .then((r: any) => {
        // Handle paginated response structure
        const badges = Array.isArray(r.data) ? r.data : (r.data?.data || [])
        setRows(badges as BadgeRow[])
      })
      .catch((e) => setError(e.message))
      .finally(() => setLoading(false))
  }, [])

  const filteredRows = Array.isArray(rows) 
    ? rows.filter(b => 
        (b.name?.toLowerCase() || '').includes(search.toLowerCase()) ||
        (b.description?.toLowerCase() || '').includes(search.toLowerCase())
      )
    : []

  return (
    <AdminShell>
      <h1 className="admin-page-title">Badges</h1>
      <p className="admin-page-subtitle">View and manage achievement badges.</p>

      <div className="admin-actions-bar" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '2rem', gap: '1rem' }}>
        <div className="admin-search-wrap" style={{ position: 'relative', flex: 1, maxWidth: '400px' }}>
          <input 
            type="text" 
            placeholder="Search badges..." 
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            style={{ width: '100%', padding: '0.7rem 1rem', borderRadius: '0.8rem', border: '1px solid var(--line)', background: 'white' }}
          />
        </div>
        <button className="btn btn-primary" style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
          <span>+</span> Create Badge
        </button>
      </div>

      {loading ? (
        <div className="admin-loading" style={{ padding: '4rem', textAlign: 'center', color: 'var(--muted)' }}>
          <div className="spinner" style={{ marginBottom: '1rem' }}>⌛</div>
          Loading badges…
        </div>
      ) : error ? (
        <div className="admin-card" style={{ border: '1px solid var(--danger)', color: 'var(--danger)', padding: '2rem', textAlign: 'center' }}>
          <h3 style={{ marginBottom: '0.5rem' }}>Error Loading Badges</h3>
          <p>{error}</p>
        </div>
      ) : filteredRows.length === 0 ? (
        <div className="admin-card" style={{ padding: '4rem', textAlign: 'center', color: 'var(--muted)', background: 'rgba(21,37,51,0.02)', borderStyle: 'dashed' }}>
          No badges found matching your search.
        </div>
      ) : (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(300px, 1fr))', gap: '1.5rem' }}>
          {filteredRows.map((b) => (
            <div key={b.id} className="admin-card badge-item-card" style={{ padding: '1.5rem', display: 'flex', gap: '1.2rem', alignItems: 'flex-start', position: 'relative' }}>
              <div className="badge-icon-wrap" style={{ 
                width: '64px', 
                height: '64px', 
                borderRadius: '1rem', 
                background: 'linear-gradient(135deg, rgba(19, 143, 149, 0.1), rgba(207, 78, 47, 0.1))',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontSize: '2rem',
                flexShrink: 0,
                border: '1px solid var(--line)'
              }}>
                {b.icon_url ? (
                  <img src={b.icon_url} alt={b.name} style={{ width: '80%', height: '80%', objectFit: 'contain' }} />
                ) : (
                  <span>🏅</span>
                )}
              </div>
              
              <div className="badge-content" style={{ flex: 1 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', marginBottom: '0.25rem' }}>
                  <h3 style={{ fontSize: '1.1rem', fontFamily: 'var(--font-heading)' }}>{b.name}</h3>
                  {b.is_premium && <span style={{ padding: '0.1rem 0.4rem', borderRadius: '4px', background: '#ffd700', color: '#8b4513', fontSize: '0.65rem', fontWeight: 800, textTransform: 'uppercase' }}>Pro</span>}
                </div>
                
                <p style={{ fontSize: '0.85rem', color: 'var(--muted)', marginBottom: '1rem', lineHeight: '1.4' }}>
                  {b.description}
                </p>
                
                <div className="badge-requirement" style={{ fontSize: '0.75rem', background: 'rgba(21,37,51,0.04)', padding: '0.5rem 0.7rem', borderRadius: '0.5rem', color: 'var(--muted)' }}>
                  <span style={{ fontWeight: 700 }}>Requirement:</span> {b.requirement_type.replace('_', ' ')} {b.requirement_value > 0 ? `(${b.requirement_value})` : ''}
                </div>
              </div>

              <div className="badge-actions" style={{ position: 'absolute', top: '1rem', right: '1rem', display: 'flex', gap: '0.4rem' }}>
                <button className="btn-icon" style={{ background: 'none', border: 'none', color: 'var(--muted)', cursor: 'pointer', fontSize: '1.1rem' }}>⚙️</button>
              </div>
            </div>
          ))}
        </div>
      )}
    </AdminShell>
  )
}
