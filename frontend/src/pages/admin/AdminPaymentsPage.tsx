import { useEffect, useState } from 'react'
import { AdminShell } from '../../admin/AdminShell'
import '../../admin/AdminShell.css'
import { fetchPayments, type Payment, type PaginatedResponse } from '../../api/admin'

export default function AdminPaymentsPage() {
  const [data, setData] = useState<PaginatedResponse<Payment> | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [page, setPage] = useState(1)

  function load(p = page) {
    setLoading(true)
    setError('')
    fetchPayments({ page: p, per_page: 20 })
      .then((res) => setData(res.data))
      .catch((err) => setError(err.message))
      .finally(() => setLoading(false))
  }

  useEffect(() => { load() }, []) // eslint-disable-line

  function handlePage(p: number) { setPage(p); load(p) }

  function statusCls(s: string) {
    return s === 'paid' ? 'badge--green' : s === 'refunded' ? 'badge--blue' : s === 'failed' ? 'badge--red' : 'badge--yellow'
  }

  return (
    <AdminShell>
      <h1 className="admin-page-title">Payments</h1>
      <p className="admin-page-subtitle">Review payment history and verify transactions.</p>

      <div className="admin-filter-bar">
        <button className="btn btn-secondary" onClick={() => load()}>Refresh</button>
      </div>

      {loading ? (
        <div className="admin-loading">Loading…</div>
      ) : error ? (
        <div className="admin-loading" style={{ color: 'var(--danger)' }}>{error}</div>
      ) : !data?.data.length ? (
        <div className="admin-empty">No payments found.</div>
      ) : (
        <>
          <div className="admin-table-wrap">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>User</th>
                  <th>Amount</th>
                  <th>Final</th>
                  <th>Status</th>
                  <th>Gateway</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                {data.data.map((p) => (
                  <tr key={p.id}>
                    <td style={{ color: 'var(--muted)', fontWeight: 600 }}>#{p.id}</td>
                    <td style={{ color: 'var(--muted)', fontSize: '0.82rem' }}>
                      {p.user ? (
                        <>
                          <div style={{ fontWeight: 600, color: 'var(--ink)' }}>{p.user.name}</div>
                          <div>{p.user.email}</div>
                        </>
                      ) : `User #${p.user_id}`}
                    </td>
                    <td>${Number(p.amount).toFixed(2)}</td>
                    <td style={{ fontWeight: 700 }}>${Number(p.final_amount).toFixed(2)}</td>
                    <td><span className={`badge ${statusCls(p.status)}`}>{p.status}</span></td>
                    <td style={{ color: 'var(--muted)', textTransform: 'capitalize' }}>{p.gateway}</td>
                    <td style={{ color: 'var(--muted)', fontSize: '0.8rem' }}>
                      {new Date(p.created_at).toLocaleDateString()}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          <div className="admin-pagination">
            <button disabled={page <= 1} onClick={() => handlePage(page - 1)}>← Prev</button>
            <span className="admin-pagination__info">
              Page {data.current_page} of {data.last_page} &nbsp;·&nbsp; {data.total} payments
            </span>
            <button disabled={page >= data.last_page} onClick={() => handlePage(page + 1)}>Next →</button>
          </div>
        </>
      )}
    </AdminShell>
  )
}
