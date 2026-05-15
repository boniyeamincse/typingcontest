import { useState } from 'react'
import { toast } from 'react-hot-toast'
import { AdminShell } from '../../admin/AdminShell'
import '../../admin/AdminShell.css'
import { sendNotificationToTarget } from '../../api/admin'

export default function AdminNotificationsPage() {
  const [title, setTitle] = useState('')
  const [message, setMessage] = useState('')
  const [target, setTarget] = useState<'all' | 'pro' | 'free'>('all')
  const [busy, setBusy] = useState(false)

  async function handleSend() {
    if (!title || !message) { toast.error('Title and message are required'); return }
    setBusy(true)
    try {
      await sendNotificationToTarget(title, message, target)
      toast.success('Notification sent')
      setTitle('')
      setMessage('')
    } catch (err) {
      toast.error(err instanceof Error ? err.message : 'Failed')
    } finally {
      setBusy(false)
    }
  }

  return (
    <AdminShell>
      <h1 className="admin-page-title">Notifications</h1>
      <p className="admin-page-subtitle">Broadcast platform-wide notifications to users.</p>

      <div className="admin-card" style={{ maxWidth: 560 }}>
        <h2 style={{ fontSize: '1rem', fontWeight: 700, marginBottom: 20 }}>Send notification</h2>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
          <label className="admin-form-label">
            Title
            <input
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              placeholder="Platform maintenance at 10 PM…"
              className="admin-form-input"
            />
          </label>
          <label className="admin-form-label">
            Message
            <textarea
              value={message}
              onChange={(e) => setMessage(e.target.value)}
              rows={4}
              placeholder="Describe the notification in detail…"
              className="admin-form-input"
            />
          </label>
          <label className="admin-form-label">
            Target audience
            <select
              value={target}
              onChange={(e) => setTarget(e.target.value as 'all' | 'pro' | 'free')}
              className="admin-form-input"
            >
              <option value="all">All users</option>
              <option value="pro">Pro subscribers</option>
              <option value="free">Free users</option>
            </select>
          </label>
          <button
            className="btn btn-primary"
            disabled={busy || !title || !message}
            onClick={handleSend}
            style={{ alignSelf: 'flex-start' }}
          >
            {busy ? 'Sending…' : '📢 Send notification'}
          </button>
        </div>
      </div>
    </AdminShell>
  )
}
