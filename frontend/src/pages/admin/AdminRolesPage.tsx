import { useEffect, useState } from 'react'
import { toast } from 'react-hot-toast'
import { AdminShell } from '../../admin/AdminShell'
import '../../admin/AdminShell.css'
import { assignRoles, fetchRoles } from '../../api/admin'

const KNOWN_ROLES = [
  'super_admin',
  'contest_admin',
  'user_moderator',
  'support_admin',
  'content_manager',
  'admin',
]

export default function AdminRolesPage() {
  const [availableRoles, setAvailableRoles] = useState<string[]>([])
  const [userId, setUserId] = useState('')
  const [selected, setSelected] = useState<string[]>([])
  const [busy, setBusy] = useState(false)

  useEffect(() => {
    fetchRoles()
      .then((r) => {
        const data = r.data as unknown
        if (Array.isArray(data) && typeof data[0] === 'string') {
          setAvailableRoles(data as string[])
          return
        }
        if (Array.isArray(data) && typeof data[0] === 'object' && data[0] !== null && 'name' in data[0]) {
          setAvailableRoles((data as { name: string }[]).map((role) => role.name))
          return
        }
        const payload = data as { roles?: string[] }
        setAvailableRoles(payload.roles ?? KNOWN_ROLES)
      })
      .catch(() => setAvailableRoles(KNOWN_ROLES))
  }, [])

  function toggle(role: string) {
    setSelected((prev) => prev.includes(role) ? prev.filter((r) => r !== role) : [...prev, role])
  }

  async function handleAssign() {
    if (!userId || !selected.length) {
      toast.error('Enter a user ID and select at least one role')
      return
    }
    setBusy(true)
    try {
      await assignRoles(Number(userId), selected)
      toast.success(`Roles updated for user #${userId}`)
      setUserId('')
      setSelected([])
    } catch (err) {
      toast.error(err instanceof Error ? err.message : 'Failed')
    } finally {
      setBusy(false)
    }
  }

  return (
    <AdminShell>
      <h1 className="admin-page-title">Role Management</h1>
      <p className="admin-page-subtitle">Assign admin roles to users. Super admin only.</p>

      <div className="admin-card" style={{ maxWidth: 480 }}>
        <h2 style={{ fontSize: '1rem', fontWeight: 700, marginBottom: 20 }}>Assign roles to user</h2>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
          <label className="admin-form-label">
            User ID
            <input
              type="number"
              min={1}
              value={userId}
              onChange={(e) => setUserId(e.target.value)}
              placeholder="123"
              className="admin-form-input"
            />
          </label>
          <div>
            <div style={{ fontSize: '0.82rem', fontWeight: 600, color: 'var(--muted)', marginBottom: 8 }}>Roles</div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
              {availableRoles.map((role) => (
                <label key={role} style={{ display: 'flex', alignItems: 'center', gap: 10, cursor: 'pointer', fontSize: '0.875rem' }}>
                  <input
                    type="checkbox"
                    checked={selected.includes(role)}
                    onChange={() => toggle(role)}
                  />
                  <span style={{ textTransform: 'capitalize' }}>{role.replace(/_/g, ' ')}</span>
                </label>
              ))}
            </div>
          </div>
          <button
            className="btn btn-primary"
            disabled={busy || !userId || !selected.length}
            onClick={handleAssign}
            style={{ alignSelf: 'flex-start' }}
          >
            {busy ? 'Saving…' : '✔ Assign roles'}
          </button>
        </div>
      </div>
    </AdminShell>
  )
}
