import { useEffect, useState } from 'react'
import { AdminShell } from '../../admin/AdminShell'
import '../../admin/AdminShell.css'
import { fetchApiManagementOverview } from '../../api/admin'

type ApiLogItem = {
  id?: number
  method?: string
  path?: string
  status_code?: number
  created_at?: string
}

type ApiManagementOverview = {
  recent_logs?: { data?: ApiLogItem[] }
  rate_limits?: Record<string, string>
  token_monitoring?: { note?: string; checked_at?: string }
}

export default function AdminApiManagementPage() {
  const [data, setData] = useState<ApiManagementOverview | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    fetchApiManagementOverview()
      .then((res) => setData(res.data as ApiManagementOverview))
      .catch((err) => setError(err.message))
      .finally(() => setLoading(false))
  }, [])

  return (
    <AdminShell>
      <h1 className="admin-page-title">API Management</h1>
      <p className="admin-page-subtitle">Logs, rate limits, and token monitoring placeholder module.</p>

      {loading ? <div className="admin-loading">Loading...</div> : null}
      {error ? <div className="admin-loading" style={{ color: 'var(--danger)' }}>{error}</div> : null}

      {!loading && !error && data ? (
        <>
          <div className="admin-card" style={{ marginBottom: 20 }}>
            <h2 style={{ marginTop: 0 }}>Rate limits</h2>
            <div style={{ display: 'grid', gap: 8 }}>
              {Object.entries(data.rate_limits ?? {}).map(([key, value]) => (
                <div key={key} style={{ display: 'flex', justifyContent: 'space-between' }}>
                  <span>{key}</span>
                  <strong>{value}</strong>
                </div>
              ))}
            </div>
          </div>

          <div className="admin-card" style={{ marginBottom: 20 }}>
            <h2 style={{ marginTop: 0 }}>Token monitoring</h2>
            <p style={{ margin: 0 }}>{data.token_monitoring?.note ?? 'No token note available.'}</p>
            <p style={{ margin: '8px 0 0', color: 'var(--muted)', fontSize: '0.82rem' }}>
              Checked at: {data.token_monitoring?.checked_at ?? 'n/a'}
            </p>
          </div>

          <div className="admin-card">
            <h2 style={{ marginTop: 0 }}>Recent API logs</h2>
            <div className="admin-table-wrap">
              <table className="admin-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Method</th>
                    <th>Path</th>
                    <th>Status</th>
                    <th>At</th>
                  </tr>
                </thead>
                <tbody>
                  {(data.recent_logs?.data ?? []).map((item) => (
                    <tr key={item.id ?? `${item.method}-${item.path}-${item.created_at}`}>
                      <td>{item.id ?? '-'}</td>
                      <td>{item.method ?? '-'}</td>
                      <td>{item.path ?? '-'}</td>
                      <td>{item.status_code ?? '-'}</td>
                      <td>{item.created_at ?? '-'}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </>
      ) : null}
    </AdminShell>
  )
}
