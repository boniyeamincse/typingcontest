import { useEffect, useState } from 'react'
import { AdminShell } from '../../admin/AdminShell'
import '../../admin/AdminShell.css'
import { fetchContentItems } from '../../api/admin'

export default function AdminContentPage() {
  const [rows, setRows] = useState<Record<string, unknown>[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    fetchContentItems()
      .then((r) => setRows(r.data as Record<string, unknown>[]))
      .catch((e) => setError(e.message))
      .finally(() => setLoading(false))
  }, [])

  return (
    <AdminShell>
      <h1 className="admin-page-title">Content Manager</h1>
      <p className="admin-page-subtitle">Manage platform content items — typing texts, prompts, and categories.</p>

      {loading ? (
        <div className="admin-loading">Loading…</div>
      ) : error ? (
        <div className="admin-loading" style={{ color: 'var(--danger)' }}>{error}</div>
      ) : rows.length === 0 ? (
        <div className="admin-empty">No content items found.</div>
      ) : (
        <div className="admin-table-wrap">
          <table className="admin-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Title / Snippet</th>
                <th>Status</th>
                <th>Created</th>
              </tr>
            </thead>
            <tbody>
              {rows.map((row, i) => (
                <tr key={i}>
                  <td style={{ color: 'var(--muted)' }}>#{String(row.id ?? i + 1)}</td>
                  <td style={{ textTransform: 'capitalize', fontSize: '0.82rem' }}>{String(row.type ?? '—')}</td>
                  <td style={{ maxWidth: 320 }}>
                    <div style={{ fontWeight: 600, fontSize: '0.875rem', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                      {String(row.title ?? row.text ?? '—')}
                    </div>
                  </td>
                  <td>
                    <span className={`badge ${row.active ? 'badge--green' : 'badge--gray'}`}>
                      {row.active ? 'Active' : 'Inactive'}
                    </span>
                  </td>
                  <td style={{ color: 'var(--muted)', fontSize: '0.8rem' }}>
                    {row.created_at ? new Date(String(row.created_at)).toLocaleDateString() : '—'}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </AdminShell>
  )
}
