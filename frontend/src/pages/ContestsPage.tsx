import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { listContests } from '../api/contests'
import type { Contest, ContestStatus, ContestType, PaginatedResponse } from '../api/contests'
import { AppShell } from '../components/AppShell'
import './ContestsPage.css'

const STATUS_LABELS: Record<ContestStatus, string> = {
  draft: 'Draft',
  published: 'Upcoming',
  active: 'Live',
  finished: 'Finished',
  completed: 'Ended',
  cancelled: 'Cancelled',
}

const TYPE_LABELS: Record<ContestType, string> = {
  daily: 'Daily',
  weekly: 'Weekly',
  monthly: 'Monthly',
  special: 'Special',
  speed: 'Speed',
}

function formatStatusLabel(status: ContestStatus): string {
  return STATUS_LABELS[status] ?? status.charAt(0).toUpperCase() + status.slice(1)
}

function formatTypeLabel(type: ContestType): string {
  return TYPE_LABELS[type] ?? type.charAt(0).toUpperCase() + type.slice(1)
}

export default function ContestsPage() {
  const [data, setData] = useState<PaginatedResponse<Contest> | null>(null)
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(true)
  const [filterType, setFilterType] = useState<ContestType | ''>('')
  const [filterStatus, setFilterStatus] = useState<ContestStatus | ''>('')
  const [page, setPage] = useState(1)

  useEffect(() => {
    setLoading(true)
    setError('')
    listContests({
      type: filterType || undefined,
      status: filterStatus || undefined,
      page,
    })
      .then(setData)
      .catch((e: unknown) =>
        setError(e instanceof Error ? e.message : 'Failed to load contests'),
      )
      .finally(() => setLoading(false))
  }, [filterType, filterStatus, page])

  return (
    <AppShell
      title="Contest Lobby"
      subtitle="Filter by mode or status, then open a room to review details and join."
      actions={
        <Link to="/dashboard" className="btn-secondary">
          Dashboard
        </Link>
      }
    >
      <div className="contests-page surface-card">
        <div className="contests-filters">
          <select
            value={filterType}
            onChange={(e) => {
              setFilterType(e.target.value as ContestType | '')
              setPage(1)
            }}
            aria-label="Filter by type"
          >
            <option value="">All types</option>
            {(Object.keys(TYPE_LABELS) as ContestType[]).map((t) => (
              <option key={t} value={t}>
                {TYPE_LABELS[t]}
              </option>
            ))}
          </select>

          <select
            value={filterStatus}
            onChange={(e) => {
              setFilterStatus(e.target.value as ContestStatus | '')
              setPage(1)
            }}
            aria-label="Filter by status"
          >
            <option value="">All statuses</option>
            {(['published', 'active', 'finished', 'completed'] as ContestStatus[]).map((s) => (
              <option key={s} value={s}>
                {STATUS_LABELS[s]}
              </option>
            ))}
          </select>
        </div>

        {error && <p className="error-msg">{error}</p>}

        {loading ? (
          <p className="loading-msg">Loading contests...</p>
        ) : data?.data.length === 0 ? (
          <p className="empty-msg">No contests found.</p>
        ) : (
          <>
            <div className="contests-grid">
              {data?.data.map((c) => (
                <Link key={c.id} to={`/contests/${c.id}`} className="contest-card">
                  <div className="contest-card__header">
                    <span className={`badge badge--${c.status}`}>{formatStatusLabel(c.status)}</span>
                    <span className="badge badge--type">{formatTypeLabel(c.type)}</span>
                  </div>
                  <h2 className="contest-card__title">{c.title}</h2>
                  <p className="contest-card__meta">{c.durationSeconds}s typing round</p>
                  {c.startsAt ? (
                    <p className="contest-card__date">Starts {new Date(c.startsAt).toLocaleString()}</p>
                  ) : (
                    <p className="contest-card__date">Start time to be announced</p>
                  )}
                </Link>
              ))}
            </div>

            {data && data.last_page > 1 ? (
              <div className="pagination">
                <button className="btn-secondary" disabled={page <= 1} onClick={() => setPage((p) => p - 1)}>
                  Prev
                </button>
                <span>
                  Page {page} of {data.last_page}
                </span>
                <button
                  className="btn-secondary"
                  disabled={page >= data.last_page}
                  onClick={() => setPage((p) => p + 1)}
                >
                  Next
                </button>
              </div>
            ) : null}
          </>
        )}
      </div>
    </AppShell>
  )
}
