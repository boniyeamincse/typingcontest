import { useEffect, useMemo, useState } from 'react'
import { AppShell } from '../components/AppShell'
import { useAuth } from '../auth/AuthContext'
import {
  cancelContest,
  createContest,
  deleteContest,
  listAdminContests,
  publishContest,
  type AdminContestInput,
  type Contest,
  type ContestStatus,
} from '../api/contests'
import './AdminContestsPage.css'

const STATUS_OPTIONS: ContestStatus[] = ['draft', 'published', 'active', 'finished', 'cancelled']

const TYPE_OPTIONS: Array<AdminContestInput['type']> = ['daily', 'weekly', 'monthly', 'special']

const INITIAL_FORM: AdminContestInput = {
  title: '',
  type: 'daily',
  typing_text_id: 1,
  start_time: '',
  end_time: '',
}

function statusLabel(status: string) {
  if (status === 'published') return 'Upcoming'
  if (status === 'active') return 'Live'
  return status.charAt(0).toUpperCase() + status.slice(1)
}

export default function AdminContestsPage() {
  const { token } = useAuth()
  const [items, setItems] = useState<Contest[]>([])
  const [statusFilter, setStatusFilter] = useState<ContestStatus | ''>('')
  const [page, setPage] = useState(1)
  const [lastPage, setLastPage] = useState(1)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [form, setForm] = useState<AdminContestInput>(INITIAL_FORM)
  const [creating, setCreating] = useState(false)
  const [actioningId, setActioningId] = useState<number | null>(null)

  const isUnauthorized = useMemo(
    () => error.toLowerCase().includes('unauthorized') || error.toLowerCase().includes('forbidden'),
    [error],
  )

  useEffect(() => {
    async function load() {
      if (!token) return

      setLoading(true)
      setError('')

      try {
        const res = await listAdminContests(token, {
          status: statusFilter || undefined,
          page,
        })
        setItems(res.data)
        setLastPage(res.last_page)
      } catch (e) {
        setError(e instanceof Error ? e.message : 'Failed to load admin contests')
      } finally {
        setLoading(false)
      }
    }

    load()
  }, [token, statusFilter, page])

  function updateForm<K extends keyof AdminContestInput>(key: K, value: AdminContestInput[K]) {
    setForm((prev) => ({ ...prev, [key]: value }))
  }

  async function onCreate(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault()
    if (!token) return

    setCreating(true)
    setError('')

    try {
      const created = await createContest(token, form)
      setItems((prev) => [created, ...prev])
      setForm(INITIAL_FORM)
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Failed to create contest')
    } finally {
      setCreating(false)
    }
  }

  async function onPublish(id: number) {
    if (!token) return

    setActioningId(id)
    setError('')

    try {
      await publishContest(id, token)
      setItems((prev) => prev.map((item) => (item.id === id ? { ...item, status: 'published' } : item)))
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Failed to publish contest')
    } finally {
      setActioningId(null)
    }
  }

  async function onCancel(id: number) {
    if (!token) return

    setActioningId(id)
    setError('')

    try {
      await cancelContest(id, token)
      setItems((prev) => prev.map((item) => (item.id === id ? { ...item, status: 'cancelled' } : item)))
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Failed to cancel contest')
    } finally {
      setActioningId(null)
    }
  }

  async function onDelete(id: number) {
    if (!token) return

    setActioningId(id)
    setError('')

    try {
      await deleteContest(id, token)
      setItems((prev) => prev.filter((item) => item.id !== id))
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Failed to delete contest')
    } finally {
      setActioningId(null)
    }
  }

  return (
    <AppShell
      title="Admin Contest Studio"
      subtitle="Create, publish, cancel, and clean up contests from one control surface."
    >
      <section className="admin-layout">
        <form className="admin-form surface-card" onSubmit={onCreate}>
          <h2>Create Contest</h2>
          <div className="admin-form__grid">
            <label>
              Title
              <input
                type="text"
                value={form.title}
                onChange={(e) => updateForm('title', e.target.value)}
                required
              />
            </label>

            <label>
              Type
              <select
                value={form.type}
                onChange={(e) => updateForm('type', e.target.value as AdminContestInput['type'])}
              >
                {TYPE_OPTIONS.map((option) => (
                  <option key={option} value={option}>
                    {option}
                  </option>
                ))}
              </select>
            </label>

            <label>
              Typing Text ID
              <input
                type="number"
                min={1}
                value={form.typing_text_id}
                onChange={(e) => updateForm('typing_text_id', Number(e.target.value))}
                required
              />
            </label>

            <label>
              Max Participants
              <input
                type="number"
                min={1}
                value={form.max_participants ?? ''}
                onChange={(e) =>
                  updateForm('max_participants', e.target.value ? Number(e.target.value) : undefined)
                }
              />
            </label>

            <label>
              Start Time
              <input
                type="datetime-local"
                value={form.start_time}
                onChange={(e) => updateForm('start_time', e.target.value)}
                required
              />
            </label>

            <label>
              End Time
              <input
                type="datetime-local"
                value={form.end_time}
                onChange={(e) => updateForm('end_time', e.target.value)}
                required
              />
            </label>
          </div>

          <label>
            Prize Description
            <textarea
              rows={3}
              value={form.prize_description ?? ''}
              onChange={(e) => updateForm('prize_description', e.target.value || undefined)}
              placeholder="Optional reward details"
            />
          </label>

          <button className="btn-primary" type="submit" disabled={creating}>
            {creating ? 'Creating...' : 'Create Contest'}
          </button>
        </form>

        <section className="admin-table-wrap surface-card">
          <div className="admin-table__head">
            <h2>Contest Manager</h2>
            <select
              value={statusFilter}
              onChange={(e) => {
                setStatusFilter(e.target.value as ContestStatus | '')
                setPage(1)
              }}
              aria-label="Filter admin contests by status"
            >
              <option value="">All statuses</option>
              {STATUS_OPTIONS.map((status) => (
                <option key={status} value={status}>
                  {statusLabel(status)}
                </option>
              ))}
            </select>
          </div>

          {error ? <p className="error-msg">{error}</p> : null}

          {loading ? (
            <p className="loading-msg">Loading contests...</p>
          ) : isUnauthorized ? (
            <p className="loading-msg">Admin role required for this page.</p>
          ) : items.length === 0 ? (
            <p className="loading-msg">No contests found.</p>
          ) : (
            <div className="admin-table-scroll">
              <table className="admin-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Type</th>
                    <th>Start</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {items.map((contest) => (
                    <tr key={contest.id}>
                      <td>{contest.id}</td>
                      <td>{contest.title}</td>
                      <td>{statusLabel(contest.status)}</td>
                      <td>{contest.type}</td>
                      <td>{contest.startsAt ? new Date(contest.startsAt).toLocaleString() : 'TBD'}</td>
                      <td>
                        <div className="admin-actions">
                          {contest.status === 'draft' ? (
                            <button
                              className="btn-secondary"
                              disabled={actioningId === contest.id}
                              onClick={() => onPublish(contest.id)}
                            >
                              Publish
                            </button>
                          ) : null}

                          {contest.status === 'published' || contest.status === 'active' ? (
                            <button
                              className="btn-secondary"
                              disabled={actioningId === contest.id}
                              onClick={() => onCancel(contest.id)}
                            >
                              Cancel
                            </button>
                          ) : null}

                          {contest.status === 'draft' ? (
                            <button
                              className="btn-secondary"
                              disabled={actioningId === contest.id}
                              onClick={() => onDelete(contest.id)}
                            >
                              Delete
                            </button>
                          ) : null}
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}

          {lastPage > 1 ? (
            <div className="admin-pagination">
              <button className="btn-secondary" disabled={page <= 1} onClick={() => setPage((p) => p - 1)}>
                Prev
              </button>
              <span>
                Page {page} of {lastPage}
              </span>
              <button
                className="btn-secondary"
                disabled={page >= lastPage}
                onClick={() => setPage((p) => p + 1)}
              >
                Next
              </button>
            </div>
          ) : null}
        </section>
      </section>
    </AppShell>
  )
}