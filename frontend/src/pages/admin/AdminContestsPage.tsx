import { useEffect, useState } from 'react'
import { toast } from 'react-hot-toast'
import { AdminShell } from '../../admin/AdminShell'
import '../../admin/AdminShell.css'
import {
  cloneContest,
  fetchAdminContests,
  stopContest,
  type Contest,
  type PaginatedResponse,
} from '../../api/admin'

function StatusBadge({ status }: { status: string }) {
  const cls =
    status === 'active' ? 'badge--green'
    : status === 'pending' ? 'badge--yellow'
    : status === 'finished' ? 'badge--gray'
    : 'badge--red'
  return <span className={`badge ${cls}`}>{status}</span>
}

export default function AdminContestsPage2() {
  const [data, setData] = useState<PaginatedResponse<Contest> | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [statusFilter, setStatusFilter] = useState('')
  const [page, setPage] = useState(1)

  function load(p = page, s = statusFilter) {
    setLoading(true)
    setError('')
    const params: Record<string, string | number> = { page: p, per_page: 20 }
    if (s) params.status = s
    fetchAdminContests(params)
      .then((res) => setData(res.data))
      .catch((err) => setError(err.message))
      .finally(() => setLoading(false))
  }

  useEffect(() => { load() }, []) // eslint-disable-line

  function handleStatus(val: string) { setStatusFilter(val); setPage(1); load(1, val) }
  function handlePage(p: number) { setPage(p); load(p, statusFilter) }

  async function handleStop(id: number, title: string) {
    if (!confirm(`Stop contest "${title}"?`)) return
    try {
      await stopContest(id)
      toast.success('Contest stopped')
      load()
    } catch (err) {
      toast.error(err instanceof Error ? err.message : 'Failed')
    }
  }

  async function handleClone(id: number) {
    try {
      await cloneContest(id)
      toast.success('Contest cloned')
      load()
    } catch (err) {
      toast.error(err instanceof Error ? err.message : 'Failed')
    }
  }

  return (
    <AdminShell>
      <h1 className="admin-page-title">Contests</h1>
      <p className="admin-page-subtitle">Monitor, stop and clone contests.</p>

      <div className="admin-filter-bar">
        <select value={statusFilter} onChange={(e) => handleStatus(e.target.value)}>
          <option value="">All statuses</option>
          <option value="active">Active</option>
          <option value="pending">Pending</option>
          <option value="finished">Finished</option>
          <option value="cancelled">Cancelled</option>
        </select>
        <button className="btn btn-secondary" onClick={() => load()}>Refresh</button>
      </div>

      {loading ? (
        <div className="admin-loading">Loading…</div>
      ) : error ? (
        <div className="admin-loading" style={{ color: 'var(--danger)' }}>{error}</div>
      ) : !data?.data.length ? (
        <div className="admin-empty">No contests found.</div>
      ) : (
        <>
          <div className="admin-table-wrap">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Title</th>
                  <th>Status</th>
                  <th>Difficulty</th>
                  <th>Participants</th>
                  <th>Starts at</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {data.data.map((c) => (
                  <tr key={c.id}>
                    <td style={{ color: 'var(--muted)', fontWeight: 600 }}>#{c.id}</td>
                    <td style={{ fontWeight: 600 }}>{c.title}</td>
                    <td><StatusBadge status={c.status} /></td>
                    <td style={{ textTransform: 'capitalize', color: 'var(--muted)' }}>{c.difficulty}</td>
                    <td style={{ color: 'var(--muted)' }}>
                      {c.participants_count} / {c.max_participants}
                    </td>
                    <td style={{ color: 'var(--muted)', fontSize: '0.8rem' }}>
                      {new Date(c.starts_at).toLocaleString()}
                    </td>
                    <td>
                      <div style={{ display: 'flex', gap: 5 }}>
                        {c.status === 'active' && (
                          <button className="btn btn-sm btn-danger" onClick={() => handleStop(c.id, c.title)}>
                            Stop
                          </button>
                        )}
                        <button className="btn btn-sm btn-secondary" onClick={() => handleClone(c.id)}>
                          Clone
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          <div className="admin-pagination">
            <button disabled={page <= 1} onClick={() => handlePage(page - 1)}>← Prev</button>
            <span className="admin-pagination__info">
              Page {data.current_page} of {data.last_page} &nbsp;·&nbsp; {data.total} contests
            </span>
            <button disabled={page >= data.last_page} onClick={() => handlePage(page + 1)}>Next →</button>
          </div>
        </>
      )}
    </AdminShell>
  )
}
