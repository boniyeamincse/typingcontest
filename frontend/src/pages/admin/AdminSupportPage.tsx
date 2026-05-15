import { useEffect, useState } from 'react'
import { toast } from 'react-hot-toast'
import { AdminShell } from '../../admin/AdminShell'
import '../../admin/AdminShell.css'
import {
  closeTicket,
  escalateTicket,
  fetchTickets,
  respondToTicket,
  type PaginatedResponse,
  type SupportTicket,
} from '../../api/admin'

function PriorityBadge({ priority }: { priority: string }) {
  const cls =
    priority === 'critical' ? 'badge--red'
    : priority === 'high' ? 'badge--yellow'
    : priority === 'medium' ? 'badge--blue'
    : 'badge--gray'
  return <span className={`badge ${cls}`}>{priority || 'low'}</span>
}

function StatusBadge({ status }: { status: string }) {
  const cls = status === 'open' ? 'badge--green' : status === 'closed' ? 'badge--gray' : 'badge--yellow'
  return <span className={`badge ${cls}`}>{status}</span>
}

export default function AdminSupportPage() {
  const [data, setData] = useState<PaginatedResponse<SupportTicket> | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [statusFilter, setStatusFilter] = useState('')
  const [page, setPage] = useState(1)
  const [respondModal, setRespondModal] = useState<SupportTicket | null>(null)

  function load(p = page, s = statusFilter) {
    setLoading(true)
    setError('')
    const params: Record<string, string | number> = { page: p, per_page: 20 }
    if (s) params.status = s
    fetchTickets(params)
      .then((res) => setData(res.data))
      .catch((err) => setError(err.message))
      .finally(() => setLoading(false))
  }

  useEffect(() => { load() }, []) // eslint-disable-line

  function handleStatus(val: string) { setStatusFilter(val); setPage(1); load(1, val) }
  function handlePage(p: number) { setPage(p); load(p, statusFilter) }

  async function handleClose(id: number) {
    if (!confirm('Close this ticket?')) return
    try { await closeTicket(id); toast.success('Ticket closed'); load() }
    catch (err) { toast.error(err instanceof Error ? err.message : 'Failed') }
  }

  async function handleEscalate(id: number) {
    try { await escalateTicket(id); toast.success('Ticket escalated'); load() }
    catch (err) { toast.error(err instanceof Error ? err.message : 'Failed') }
  }

  async function handleRespond(id: number, message: string) {
    try { await respondToTicket(id, message); toast.success('Response sent'); setRespondModal(null) }
    catch (err) { toast.error(err instanceof Error ? err.message : 'Failed') }
  }

  return (
    <AdminShell>
      <h1 className="admin-page-title">Support Tickets</h1>
      <p className="admin-page-subtitle">Manage and respond to user support requests.</p>

      <div className="admin-filter-bar">
        <select value={statusFilter} onChange={(e) => handleStatus(e.target.value)}>
          <option value="">All statuses</option>
          <option value="open">Open</option>
          <option value="in_progress">In progress</option>
          <option value="closed">Closed</option>
        </select>
        <button className="btn btn-secondary" onClick={() => load()}>Refresh</button>
      </div>

      {loading ? (
        <div className="admin-loading">Loading…</div>
      ) : error ? (
        <div className="admin-loading" style={{ color: 'var(--danger)' }}>{error}</div>
      ) : !data?.data.length ? (
        <div className="admin-empty">No tickets found.</div>
      ) : (
        <>
          <div className="admin-table-wrap">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Subject</th>
                  <th>User</th>
                  <th>Priority</th>
                  <th>Status</th>
                  <th>Created</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {data.data.map((t) => (
                  <tr key={t.id}>
                    <td style={{ color: 'var(--muted)', fontWeight: 600 }}>#{t.id}</td>
                    <td style={{ maxWidth: 240 }}>
                      <div style={{ fontWeight: 600, fontSize: '0.875rem' }}>{t.subject}</div>
                    </td>
                    <td style={{ color: 'var(--muted)', fontSize: '0.82rem' }}>
                      {t.user ? (
                        <>
                          <div style={{ fontWeight: 600, color: 'var(--ink)' }}>{t.user.name}</div>
                          <div>{t.user.email}</div>
                        </>
                      ) : '—'}
                    </td>
                    <td><PriorityBadge priority={t.priority} /></td>
                    <td><StatusBadge status={t.status} /></td>
                    <td style={{ color: 'var(--muted)', fontSize: '0.8rem' }}>
                      {new Date(t.created_at).toLocaleDateString()}
                    </td>
                    <td>
                      <div style={{ display: 'flex', gap: 5, flexWrap: 'wrap' }}>
                        {t.status !== 'closed' && (
                          <>
                            <button className="btn btn-sm btn-secondary" onClick={() => setRespondModal(t)}>
                              Respond
                            </button>
                            <button className="btn btn-sm btn-ok" onClick={() => handleClose(t.id)}>
                              Close
                            </button>
                            <button className="btn btn-sm btn-danger" onClick={() => handleEscalate(t.id)}>
                              Escalate
                            </button>
                          </>
                        )}
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
              Page {data.current_page} of {data.last_page} &nbsp;·&nbsp; {data.total} tickets
            </span>
            <button disabled={page >= data.last_page} onClick={() => handlePage(page + 1)}>Next →</button>
          </div>
        </>
      )}

      {respondModal ? (
        <RespondModal
          ticket={respondModal}
          onClose={() => setRespondModal(null)}
          onSubmit={handleRespond}
        />
      ) : null}
    </AdminShell>
  )
}

function RespondModal({
  ticket,
  onClose,
  onSubmit,
}: {
  ticket: SupportTicket
  onClose: () => void
  onSubmit: (id: number, message: string) => void
}) {
  const [message, setMessage] = useState('')
  const [busy, setBusy] = useState(false)

  return (
    <div className="admin-modal-overlay" onClick={onClose}>
      <div className="admin-modal" onClick={(e) => e.stopPropagation()}>
        <h2>Respond to #{ticket.id}</h2>
        <p style={{ fontSize: '0.85rem', color: 'var(--muted)' }}>{ticket.subject}</p>
        <label>
          Message
          <textarea
            value={message}
            onChange={(e) => setMessage(e.target.value)}
            rows={4}
            placeholder="Type your response…"
          />
        </label>
        <div className="admin-modal__actions">
          <button className="btn btn-secondary" onClick={onClose}>Cancel</button>
          <button
            className="btn btn-primary"
            disabled={busy || !message.trim()}
            onClick={async () => { setBusy(true); await onSubmit(ticket.id, message) }}
          >
            {busy ? 'Sending…' : 'Send response'}
          </button>
        </div>
      </div>
    </div>
  )
}
