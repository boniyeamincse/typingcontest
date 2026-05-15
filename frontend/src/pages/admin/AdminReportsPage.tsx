import { useEffect, useState } from 'react'
import { toast } from 'react-hot-toast'
import { AdminShell } from '../../admin/AdminShell'
import '../../admin/AdminShell.css'
import { fetchReports, queueReport } from '../../api/admin'

export default function AdminReportsPage() {
  const [reports, setReports] = useState<unknown[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [reportType, setReportType] = useState('subscriptions_summary')
  const [dateFrom, setDateFrom] = useState('')
  const [dateTo, setDateTo] = useState('')
  const [queueing, setQueueing] = useState(false)

  useEffect(() => {
    fetchReports()
      .then((r) => setReports(r.data as unknown[]))
      .catch((e) => setError(e.message))
      .finally(() => setLoading(false))
  }, [])

  async function handleQueue() {
    if (!dateFrom || !dateTo) { toast.error('Select date range'); return }
    setQueueing(true)
    try {
      await queueReport(reportType, dateFrom, dateTo)
      toast.success('Report queued for generation')
    } catch (err) {
      toast.error(err instanceof Error ? err.message : 'Failed')
    } finally {
      setQueueing(false)
    }
  }

  return (
    <AdminShell>
      <h1 className="admin-page-title">Reports & Analytics</h1>
      <p className="admin-page-subtitle">Generate and download platform analytics reports.</p>

      {/* ── Queue new report ──────────────────────────────────────────────── */}
      <div className="admin-card" style={{ marginBottom: 24 }}>
        <h2 style={{ fontSize: '1rem', fontWeight: 700, marginBottom: 16 }}>Queue new report</h2>
        <div style={{ display: 'flex', gap: 10, flexWrap: 'wrap', alignItems: 'flex-end' }}>
          <label style={{ display: 'flex', flexDirection: 'column', gap: 5, fontSize: '0.82rem', fontWeight: 600, color: 'var(--muted)' }}>
            Type
            <select value={reportType} onChange={(e) => setReportType(e.target.value)} style={{ padding: '8px 12px', borderRadius: 9, border: '1px solid rgba(21,37,51,0.18)', fontSize: '0.875rem', color: 'var(--ink)', background: '#fff' }}>
              <option value="subscriptions_summary">Subscriptions Summary</option>
              <option value="payments_summary">Payments Summary</option>
              <option value="user_activity">User Activity</option>
              <option value="contest_analytics">Contest Analytics</option>
            </select>
          </label>
          <label style={{ display: 'flex', flexDirection: 'column', gap: 5, fontSize: '0.82rem', fontWeight: 600, color: 'var(--muted)' }}>
            From
            <input type="date" value={dateFrom} onChange={(e) => setDateFrom(e.target.value)} style={{ padding: '8px 12px', borderRadius: 9, border: '1px solid rgba(21,37,51,0.18)', fontSize: '0.875rem' }} />
          </label>
          <label style={{ display: 'flex', flexDirection: 'column', gap: 5, fontSize: '0.82rem', fontWeight: 600, color: 'var(--muted)' }}>
            To
            <input type="date" value={dateTo} onChange={(e) => setDateTo(e.target.value)} style={{ padding: '8px 12px', borderRadius: 9, border: '1px solid rgba(21,37,51,0.18)', fontSize: '0.875rem' }} />
          </label>
          <button className="btn btn-primary" onClick={handleQueue} disabled={queueing} style={{ alignSelf: 'flex-end', paddingBottom: 9 }}>
            {queueing ? 'Queuing…' : 'Queue report'}
          </button>
        </div>
      </div>

      {/* ── Report list ───────────────────────────────────────────────────── */}
      {loading ? (
        <div className="admin-loading">Loading…</div>
      ) : error ? (
        <div className="admin-loading" style={{ color: 'var(--danger)' }}>{error}</div>
      ) : !reports.length ? (
        <div className="admin-empty">No reports generated yet.</div>
      ) : (
        <div className="admin-table-wrap">
          <table className="admin-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Status</th>
                <th>Generated</th>
              </tr>
            </thead>
            <tbody>
              {(reports as Record<string, unknown>[]).map((r, i) => (
                <tr key={i}>
                  <td style={{ color: 'var(--muted)' }}>#{String(r.id ?? i + 1)}</td>
                  <td style={{ textTransform: 'capitalize' }}>{String(r.report_type ?? '—').replace(/_/g, ' ')}</td>
                  <td>
                    <span className={`badge ${r.status === 'done' ? 'badge--green' : 'badge--yellow'}`}>
                      {String(r.status ?? 'pending')}
                    </span>
                  </td>
                  <td style={{ color: 'var(--muted)', fontSize: '0.8rem' }}>
                    {r.created_at ? new Date(String(r.created_at)).toLocaleDateString() : '—'}
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
