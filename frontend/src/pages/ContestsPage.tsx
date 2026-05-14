import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { listContests } from '../api/contests'
import type { Contest, ContestStatus, ContestType, PaginatedResponse } from '../api/contests'
import './ContestsPage.css'

const STATUS_LABELS: Record<ContestStatus, string> = {
  draft: 'Draft',
  published: 'Upcoming',
  active: 'Live',
  completed: 'Ended',
}

const TYPE_LABELS: Record<ContestType, string> = {
  daily: 'Daily',
  weekly: 'Weekly',
  monthly: 'Monthly',
  special: 'Special',
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
    <div className="contests-page">
      <header className="contests-header">
        <h1>Contests</h1>
        <Link to="/dashboard" className="back-link">← Dashboard</Link>
      </header>

      <div className="contests-filters">
        <select
          value={filterType}
          onChange={(e) => { setFilterType(e.target.value as ContestType | ''); setPage(1) }}
          aria-label="Filter by type"
        >
          <option value="">All types</option>
          {(Object.keys(TYPE_LABELS) as ContestType[]).map((t) => (
            <option key={t} value={t}>{TYPE_LABELS[t]}</option>
          ))}
        </select>

        <select
          value={filterStatus}
          onChange={(e) => { setFilterStatus(e.target.value as ContestStatus | ''); setPage(1) }}
          aria-label="Filter by status"
        >
          <option value="">All statuses</option>
          {(['published', 'active', 'completed'] as ContestStatus[]).map((s) => (
            <option key={s} value={s}>{STATUS_LABELS[s]}</option>
          ))}
        </select>
      </div>

      {error && <p className="error-msg">{error}</p>}

      {loading ? (
        <p className="loading-msg">Loading contests…</p>
      ) : data?.data.length === 0 ? (
        <p className="empty-msg">No contests found.</p>
      ) : (
        <>
          <div className="contests-grid">
            {data?.data.map((c) => (
              <Link key={c.id} to={`/contests/${c.id}`} className="contest-card">
                <div className="contest-card__header">
                  <span className={`badge badge--${c.status}`}>{STATUS_LABELS[c.status]}</span>
                  <span className="badge badge--type">{TYPE_LABELS[c.type]}</span>
                </div>
                <h2 className="contest-card__title">{c.title}</h2>
                <p className="contest-card__meta">{c.duration_seconds}s</p>
                {c.starts_at && (
                  <p className="contest-card__date">
                    {new Date(c.starts_at).toLocaleDateString()}
                  </p>
                )}
              </Link>
            ))}
          </div>

          {data && data.last_page > 1 && (
            <div className="pagination">
              <button disabled={page <= 1} onClick={() => setPage((p) => p - 1)}>Prev</button>
              <span>{page} / {data.last_page}</span>
              <button disabled={page >= data.last_page} onClick={() => setPage((p) => p + 1)}>Next</button>
            </div>
          )}
        </>
      )}
    </div>
  )
}
