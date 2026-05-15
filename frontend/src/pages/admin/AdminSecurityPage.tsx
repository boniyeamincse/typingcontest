import { useEffect, useState } from 'react'
import { toast } from 'react-hot-toast'
import { AdminShell } from '../../admin/AdminShell'
import '../../admin/AdminShell.css'
import { blockIp, fetchCheatingUsers, fetchSuspiciousLogins, type AdminUser, type PaginatedResponse } from '../../api/admin'

type Tab = 'cheating' | 'logins' | 'block'

export default function AdminSecurityPage() {
  const [tab, setTab] = useState<Tab>('cheating')
  const [cheating, setCheating] = useState<PaginatedResponse<AdminUser> | null>(null)
  const [logins, setLogins] = useState<PaginatedResponse<unknown> | null>(null)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  const [blockIpVal, setBlockIpVal] = useState('')
  const [blockReason, setBlockReason] = useState('')
  const [blocking, setBlocking] = useState(false)

  useEffect(() => {
    if (tab === 'cheating' && !cheating) {
      setLoading(true)
      fetchCheatingUsers()
        .then((r) => setCheating(r.data))
        .catch((e) => setError(e.message))
        .finally(() => setLoading(false))
    }
    if (tab === 'logins' && !logins) {
      setLoading(true)
      fetchSuspiciousLogins()
        .then((r) => setLogins(r.data as PaginatedResponse<unknown>))
        .catch((e) => setError(e.message))
        .finally(() => setLoading(false))
    }
  }, [tab]) // eslint-disable-line

  async function handleBlock() {
    if (!blockIpVal || !blockReason) return
    setBlocking(true)
    try {
      await blockIp(blockIpVal, blockReason)
      toast.success(`Blocked ${blockIpVal}`)
      setBlockIpVal('')
      setBlockReason('')
    } catch (err) {
      toast.error(err instanceof Error ? err.message : 'Failed')
    } finally {
      setBlocking(false)
    }
  }

  return (
    <AdminShell>
      <h1 className="admin-page-title">Security Center</h1>
      <p className="admin-page-subtitle">Cheating detection, suspicious logins and IP blocks.</p>

      <div className="admin-filter-bar">
        {(['cheating', 'logins', 'block'] as Tab[]).map((t) => (
          <button
            key={t}
            className={`btn ${tab === t ? 'btn-primary' : 'btn-secondary'}`}
            onClick={() => setTab(t)}
          >
            {t === 'cheating' ? '🚩 Cheating users' : t === 'logins' ? '🔐 Suspicious logins' : '🚫 Block IP'}
          </button>
        ))}
      </div>

      {loading ? <div className="admin-loading">Loading…</div> : null}
      {error ? <div className="admin-loading" style={{ color: 'var(--danger)' }}>{error}</div> : null}

      {tab === 'cheating' && !loading && cheating && (
        <DataList
          label="Cheating users"
          rows={cheating.data as unknown as Record<string, unknown>[]}
          columns={['id', 'user_id', 'contest_id', 'severity', 'flagged_at']}
        />
      )}

      {tab === 'logins' && !loading && logins && (
        <DataList
          label="Suspicious logins"
          rows={logins.data as unknown as Record<string, unknown>[]}
          columns={['id', 'user_id', 'ip_address', 'status', 'logged_in_at']}
        />
      )}

      {tab === 'block' && (
        <div className="admin-card" style={{ maxWidth: 480 }}>
          <h2 style={{ fontSize: '1rem', fontWeight: 700, marginBottom: 16 }}>Block IP address</h2>
          <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
            <label style={{ display: 'flex', flexDirection: 'column', gap: 5, fontSize: '0.82rem', fontWeight: 600, color: 'var(--muted)' }}>
              IP address
              <input value={blockIpVal} onChange={(e) => setBlockIpVal(e.target.value)} placeholder="192.168.1.1" style={{ padding: '8px 12px', borderRadius: 9, border: '1px solid rgba(21,37,51,0.18)', fontSize: '0.875rem' }} />
            </label>
            <label style={{ display: 'flex', flexDirection: 'column', gap: 5, fontSize: '0.82rem', fontWeight: 600, color: 'var(--muted)' }}>
              Reason
              <input value={blockReason} onChange={(e) => setBlockReason(e.target.value)} placeholder="Brute force attempt…" style={{ padding: '8px 12px', borderRadius: 9, border: '1px solid rgba(21,37,51,0.18)', fontSize: '0.875rem' }} />
            </label>
            <button className="btn btn-danger" disabled={blocking || !blockIpVal || !blockReason} onClick={handleBlock}>
              {blocking ? 'Blocking…' : 'Block IP'}
            </button>
          </div>
        </div>
      )}
    </AdminShell>
  )
}

function DataList({ label, rows, columns }: { label: string; rows: Record<string, unknown>[]; columns: string[] }) {
  if (!rows.length) return <div className="admin-empty">No {label.toLowerCase()} found.</div>
  return (
    <div className="admin-table-wrap">
      <table className="admin-table">
        <thead>
          <tr>{columns.map((c) => <th key={c}>{c.replace(/_/g, ' ')}</th>)}</tr>
        </thead>
        <tbody>
          {rows.map((row, i) => (
            <tr key={i}>
              {columns.map((c) => <td key={c} style={{ color: 'var(--muted)', fontSize: '0.82rem' }}>{String(row[c] ?? '—')}</td>)}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}
