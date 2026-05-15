import { useEffect, useState } from 'react'
import { AdminShell } from '../../admin/AdminShell'
import '../../admin/AdminShell.css'
import { fetchLiveMonitoring } from '../../api/admin'

type LiveData = {
  active_sessions?: number
  active_contests?: number
  live_players?: number
  players_per_contest?: { contest_id: number; title: string; count: number }[]
}

export default function AdminLiveMonitorPage() {
  const [data, setData] = useState<LiveData | null>(null)
  const [ts, setTs] = useState('')

  function load() {
    fetchLiveMonitoring()
      .then((r) => { setData(r.data as LiveData); setTs(new Date().toLocaleTimeString()) })
      .catch(() => {})
  }

  useEffect(() => { load(); const id = setInterval(load, 10_000); return () => clearInterval(id) }, [])

  return (
    <AdminShell>
      <h1 className="admin-page-title">Live Monitor</h1>
      <p className="admin-page-subtitle">
        Real-time active sessions and contest traffic. Auto-refreshes every 10 s.
        {ts && <span style={{ color: 'var(--muted)', marginLeft: 8, fontSize: '0.78rem' }}>Last update: {ts}</span>}
      </p>

      {!data ? (
        <div className="admin-loading">Waiting for data…</div>
      ) : (
        <>
          <div className="admin-stat-grid" style={{ marginBottom: 24 }}>
            {[
              { label: 'Active sessions', value: data.active_sessions ?? 0 },
              { label: 'Live contests', value: data.active_contests ?? 0 },
              { label: 'Live players', value: data.live_players ?? 0 },
            ].map(({ label, value }) => (
              <div key={label} className="admin-stat">
                <div className="admin-stat__label">{label}</div>
                <div className="admin-stat__value">{value.toLocaleString()}</div>
              </div>
            ))}
          </div>

          {data.players_per_contest && data.players_per_contest.length > 0 && (
            <div className="admin-table-wrap">
              <table className="admin-table">
                <thead>
                  <tr>
                    <th>Contest ID</th>
                    <th>Title</th>
                    <th>Live players</th>
                  </tr>
                </thead>
                <tbody>
                  {data.players_per_contest.map((row) => (
                    <tr key={row.contest_id}>
                      <td style={{ color: 'var(--muted)' }}>#{row.contest_id}</td>
                      <td style={{ fontWeight: 600 }}>{row.title}</td>
                      <td style={{ color: 'var(--focus)', fontWeight: 700 }}>{row.count}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </>
      )}
    </AdminShell>
  )
}
