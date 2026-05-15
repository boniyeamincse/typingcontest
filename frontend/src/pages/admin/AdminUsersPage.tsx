import { useEffect, useRef, useState } from 'react'
import { toast } from 'react-hot-toast'
import { AdminShell } from '../../admin/AdminShell'
import '../../admin/AdminShell.css'
import {
  banUser,
  fetchUsers,
  resetUserPassword,
  suspendUser,
  unbanUser,
  type AdminUser,
  type PaginatedResponse,
} from '../../api/admin'

type Modal =
  | { type: 'ban'; user: AdminUser }
  | { type: 'suspend'; user: AdminUser }
  | { type: 'reset'; user: AdminUser }
  | { type: 'unban'; user: AdminUser }

function PlanBadge({ plan }: { plan: string }) {
  const cls = plan === 'vip' ? 'badge--blue' : plan === 'pro' ? 'badge--green' : 'badge--gray'
  return <span className={`badge ${cls}`}>{plan}</span>
}

export default function AdminUsersPage() {
  const [data, setData] = useState<PaginatedResponse<AdminUser> | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [search, setSearch] = useState('')
  const [plan, setPlan] = useState('')
  const [page, setPage] = useState(1)
  const [modal, setModal] = useState<Modal | null>(null)
  const searchTimeout = useRef<ReturnType<typeof setTimeout>>(null)

  function load(p = page, s = search, pl = plan) {
    setLoading(true)
    setError('')
    const params: Record<string, string | number> = { page: p, per_page: 20 }
    if (s) params.search = s
    if (pl) params.plan = pl
    fetchUsers(params)
      .then((res) => setData(res.data))
      .catch((err) => setError(err.message))
      .finally(() => setLoading(false))
  }

  useEffect(() => { load() }, []) // eslint-disable-line

  function handleSearch(val: string) {
    setSearch(val)
    if (searchTimeout.current) clearTimeout(searchTimeout.current)
    searchTimeout.current = setTimeout(() => { setPage(1); load(1, val, plan) }, 400)
  }

  function handlePlan(val: string) {
    setPlan(val)
    setPage(1)
    load(1, search, val)
  }

  function handlePage(p: number) {
    setPage(p)
    load(p, search, plan)
  }

  async function doBan(userId: number, reason: string) {
    await banUser(userId, reason)
    toast.success('User banned')
    setModal(null)
    load()
  }

  async function doUnban(userId: number) {
    await unbanUser(userId)
    toast.success('User unbanned')
    setModal(null)
    load()
  }

  async function doSuspend(userId: number, until: string, reason: string) {
    await suspendUser(userId, until, reason)
    toast.success('User suspended')
    setModal(null)
    load()
  }

  async function doReset(userId: number, pw: string) {
    await resetUserPassword(userId, pw)
    toast.success('Password reset')
    setModal(null)
  }

  return (
    <AdminShell>
      <h1 className="admin-page-title">Users</h1>
      <p className="admin-page-subtitle">Search, manage bans, suspensions and passwords.</p>

      {/* ── Filters ─────────────────────────────────────────────────────── */}
      <div className="admin-filter-bar">
        <input
          type="search"
          placeholder="Search name / email / username…"
          value={search}
          onChange={(e) => handleSearch(e.target.value)}
          style={{ flex: '1 1 220px' }}
        />
        <select value={plan} onChange={(e) => handlePlan(e.target.value)}>
          <option value="">All plans</option>
          <option value="free">Free</option>
          <option value="pro">Pro</option>
          <option value="vip">VIP</option>
        </select>
        <button className="btn btn-secondary" onClick={() => load()}>Refresh</button>
      </div>

      {/* ── Table ───────────────────────────────────────────────────────── */}
      {loading ? (
        <div className="admin-loading">Loading…</div>
      ) : error ? (
        <div className="admin-loading" style={{ color: 'var(--danger)' }}>{error}</div>
      ) : !data?.data.length ? (
        <div className="admin-empty">No users found.</div>
      ) : (
        <>
          <div className="admin-table-wrap">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Plan</th>
                  <th>Status</th>
                  <th>Joined</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {data.data.map((u) => (
                  <tr key={u.id}>
                    <td style={{ color: 'var(--muted)', fontWeight: 600 }}>#{u.id}</td>
                    <td>
                      <div style={{ fontWeight: 600 }}>{u.name}</div>
                      <div style={{ fontSize: '0.75rem', color: 'var(--muted)' }}>@{u.username}</div>
                    </td>
                    <td style={{ color: 'var(--muted)' }}>{u.email}</td>
                    <td><PlanBadge plan={u.plan_type} /></td>
                    <td>
                      {u.is_banned ? (
                        <span className="badge badge--red">Banned</span>
                      ) : u.suspended_until ? (
                        <span className="badge badge--yellow">Suspended</span>
                      ) : (
                        <span className="badge badge--green">Active</span>
                      )}
                    </td>
                    <td style={{ color: 'var(--muted)', fontSize: '0.8rem' }}>
                      {new Date(u.created_at).toLocaleDateString()}
                    </td>
                    <td>
                      <div style={{ display: 'flex', gap: 5, flexWrap: 'wrap' }}>
                        {u.is_banned ? (
                          <button
                            className="btn btn-sm btn-ok"
                            onClick={() => setModal({ type: 'unban', user: u })}
                          >
                            Unban
                          </button>
                        ) : (
                          <button
                            className="btn btn-sm btn-danger"
                            onClick={() => setModal({ type: 'ban', user: u })}
                          >
                            Ban
                          </button>
                        )}
                        <button
                          className="btn btn-sm btn-secondary"
                          onClick={() => setModal({ type: 'suspend', user: u })}
                        >
                          Suspend
                        </button>
                        <button
                          className="btn btn-sm btn-secondary"
                          onClick={() => setModal({ type: 'reset', user: u })}
                        >
                          Reset PW
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
              Page {data.current_page} of {data.last_page} &nbsp;·&nbsp; {data.total} users
            </span>
            <button disabled={page >= data.last_page} onClick={() => handlePage(page + 1)}>Next →</button>
          </div>
        </>
      )}

      {/* ── Modals ──────────────────────────────────────────────────────── */}
      {modal ? (
        <ModalDispatch
          modal={modal}
          onClose={() => setModal(null)}
          onBan={doBan}
          onUnban={doUnban}
          onSuspend={doSuspend}
          onReset={doReset}
        />
      ) : null}
    </AdminShell>
  )
}

// ── Modal dispatcher ─────────────────────────────────────────────────────────

function ModalDispatch({
  modal,
  onClose,
  onBan,
  onUnban,
  onSuspend,
  onReset,
}: {
  modal: Modal
  onClose: () => void
  onBan: (id: number, reason: string) => void
  onUnban: (id: number) => void
  onSuspend: (id: number, until: string, reason: string) => void
  onReset: (id: number, pw: string) => void
}) {
  if (modal.type === 'ban') return <BanModal user={modal.user} onClose={onClose} onSubmit={onBan} />
  if (modal.type === 'unban') return <ConfirmModal title={`Unban ${modal.user.name}?`} onClose={onClose} onConfirm={() => onUnban(modal.user.id)} />
  if (modal.type === 'suspend') return <SuspendModal user={modal.user} onClose={onClose} onSubmit={onSuspend} />
  if (modal.type === 'reset') return <ResetPasswordModal user={modal.user} onClose={onClose} onSubmit={onReset} />
  return null
}

function BanModal({ user, onClose, onSubmit }: { user: AdminUser; onClose: () => void; onSubmit: (id: number, reason: string) => void }) {
  const [reason, setReason] = useState('')
  const [busy, setBusy] = useState(false)
  return (
    <div className="admin-modal-overlay" onClick={onClose}>
      <div className="admin-modal" onClick={(e) => e.stopPropagation()}>
        <h2>Ban {user.name}</h2>
        <label>Reason <textarea value={reason} onChange={(e) => setReason(e.target.value)} required /></label>
        <div className="admin-modal__actions">
          <button className="btn btn-secondary" onClick={onClose}>Cancel</button>
          <button className="btn btn-danger" disabled={busy || !reason} onClick={async () => { setBusy(true); await onSubmit(user.id, reason) }}>
            {busy ? 'Banning…' : 'Confirm ban'}
          </button>
        </div>
      </div>
    </div>
  )
}

function SuspendModal({ user, onClose, onSubmit }: { user: AdminUser; onClose: () => void; onSubmit: (id: number, until: string, reason: string) => void }) {
  const [until, setUntil] = useState('')
  const [reason, setReason] = useState('')
  const [busy, setBusy] = useState(false)
  return (
    <div className="admin-modal-overlay" onClick={onClose}>
      <div className="admin-modal" onClick={(e) => e.stopPropagation()}>
        <h2>Suspend {user.name}</h2>
        <label>Until <input type="date" value={until} onChange={(e) => setUntil(e.target.value)} required /></label>
        <label>Reason <textarea value={reason} onChange={(e) => setReason(e.target.value)} required /></label>
        <div className="admin-modal__actions">
          <button className="btn btn-secondary" onClick={onClose}>Cancel</button>
          <button className="btn btn-danger" disabled={busy || !until || !reason} onClick={async () => { setBusy(true); await onSubmit(user.id, until, reason) }}>
            {busy ? 'Suspending…' : 'Suspend'}
          </button>
        </div>
      </div>
    </div>
  )
}

function ResetPasswordModal({ user, onClose, onSubmit }: { user: AdminUser; onClose: () => void; onSubmit: (id: number, pw: string) => void }) {
  const [pw, setPw] = useState('')
  const [busy, setBusy] = useState(false)
  return (
    <div className="admin-modal-overlay" onClick={onClose}>
      <div className="admin-modal" onClick={(e) => e.stopPropagation()}>
        <h2>Reset password for {user.name}</h2>
        <label>New password <input type="password" value={pw} onChange={(e) => setPw(e.target.value)} minLength={8} required /></label>
        <div className="admin-modal__actions">
          <button className="btn btn-secondary" onClick={onClose}>Cancel</button>
          <button className="btn btn-primary" disabled={busy || pw.length < 8} onClick={async () => { setBusy(true); await onSubmit(user.id, pw) }}>
            {busy ? 'Resetting…' : 'Reset'}
          </button>
        </div>
      </div>
    </div>
  )
}

function ConfirmModal({ title, onClose, onConfirm }: { title: string; onClose: () => void; onConfirm: () => void }) {
  const [busy, setBusy] = useState(false)
  return (
    <div className="admin-modal-overlay" onClick={onClose}>
      <div className="admin-modal" onClick={(e) => e.stopPropagation()}>
        <h2>{title}</h2>
        <div className="admin-modal__actions">
          <button className="btn btn-secondary" onClick={onClose}>Cancel</button>
          <button className="btn btn-ok" disabled={busy} onClick={async () => { setBusy(true); onConfirm() }}>
            {busy ? 'Processing…' : 'Confirm'}
          </button>
        </div>
      </div>
    </div>
  )
}
