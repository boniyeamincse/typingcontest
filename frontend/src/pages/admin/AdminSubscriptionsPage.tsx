import { useEffect, useState } from 'react'
import { AdminShell } from '../../admin/AdminShell'
import '../../admin/AdminShell.css'
import { fetchSubscriptions } from '../../api/admin'

export default function AdminSubscriptionsPage() {
  const [rows, setRows] = useState<Record<string, unknown>[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [page, setPage] = useState(1)
  const [lastPage, setLastPage] = useState(1)
  const [total, setTotal] = useState(0)

  function load(p = page) {
    setLoading(true)
    fetchSubscriptions({ page: p, per_page: 20 })
      .then((r) => {
        const d = r.data as { data?: Record<string, unknown>[]; current_page?: number; last_page?: number; total?: number }
        setRows(d.data ?? [])
        setPage(d.current_page ?? p)
        setLastPage(d.last_page ?? 1)
        setTotal(d.total ?? 0)
      })
      .catch((e) => setError(e.message))
      .finally(() => setLoading(false))
  }

  useEffect(() => { load() }, []) // eslint-disable-line

  const COLS = ['id', 'user_id', 'plan', 'status', 'started_at', 'expires_at']

  return (
    <AdminShell>
      <h1 className="admin-page-title">Subscriptions</h1>
      <p className="admin-page-subtitle">View all active and expired subscription records.</p>

      {loading ? (
        <div className="admin-loading">Loading…</div>
      ) : error ? (
        <div className="admin-loading" style={{ color: 'var(--danger)' }}>{error}</div>
      ) : rows.length === 0 ? (
        <div className="admin-empty">No subscriptions found.</div>
      ) : (
        <>
          <div className="admin-table-wrap">
            <table className="admin-table">
              <thead>
                <tr>{COLS.map((c) => <th key={c}>{c.replace(/_/g, ' ')}</th>)}</tr>
              </thead>
              <tbody>
                {rows.map((row, i) => (
                  <tr key={i}>
                    {COLS.map((c) => (
                      <td key={c} style={{ fontSize: '0.82rem', color: 'var(--muted)' }}>
                        {c === 'status' ? (
                          <span className={`badge ${row[c] === 'active' ? 'badge--green' : 'badge--gray'}`}>{String(row[c] ?? '—')}</span>
                        ) : String(row[c] ?? '—')}
                      </td>
                    ))}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          <div className="admin-pagination">
            <button disabled={page <= 1} onClick={() => load(page - 1)}>← Prev</button>
            <span className="admin-pagination__info">Page {page} of {lastPage} · {total} records</span>
            <button disabled={page >= lastPage} onClick={() => load(page + 1)}>Next →</button>
          </div>
        </>
      )}
    </AdminShell>
  )
}
