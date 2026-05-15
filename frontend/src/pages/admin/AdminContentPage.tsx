import { useEffect, useState } from 'react'
import { toast } from 'react-hot-toast'
import { AdminShell } from '../../admin/AdminShell'
import '../../admin/AdminShell.css'
import {
  deleteContentParagraph,
  fetchContentParagraphs,
  updateContentParagraph,
  adminFetch,
  type ContentParagraph,
  type ApiResponse,
} from '../../api/admin'

type Modal =
  | { type: 'create' }
  | { type: 'edit'; row: ContentParagraph }
  | { type: 'delete'; row: ContentParagraph }

const DIFFICULTIES = ['easy', 'medium', 'hard']

function ParagraphForm({
  initial,
  onSave,
  onCancel,
}: {
  initial?: ContentParagraph
  onSave: (data: { content: string; language: string; difficulty: string; source_label: string }) => Promise<void>
  onCancel: () => void
}) {
  const [content, setContent] = useState(initial?.content ?? '')
  const [language, setLanguage] = useState(initial?.language ?? 'en')
  const [difficulty, setDifficulty] = useState(initial?.difficulty ?? 'medium')
  const [sourceLabel, setSourceLabel] = useState(initial?.source_label ?? '')
  const [saving, setSaving] = useState(false)

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    setSaving(true)
    try {
      await onSave({ content, language, difficulty, source_label: sourceLabel })
    } finally {
      setSaving(false)
    }
  }

  return (
    <form onSubmit={(e) => void handleSubmit(e)} style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
      <div>
        <label style={{ display: 'block', marginBottom: 4, fontSize: '0.82rem', color: 'var(--muted)' }}>Content *</label>
        <textarea
          required
          rows={5}
          value={content}
          onChange={(e) => setContent(e.target.value)}
          style={{ width: '100%', padding: '0.75rem', borderRadius: '0.6rem', border: '1px solid var(--line)', resize: 'vertical', fontFamily: 'inherit' }}
        />
      </div>
      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '1rem' }}>
        <div>
          <label style={{ display: 'block', marginBottom: 4, fontSize: '0.82rem', color: 'var(--muted)' }}>Language</label>
          <input value={language} onChange={(e) => setLanguage(e.target.value)} style={{ width: '100%', padding: '0.6rem 0.8rem', borderRadius: '0.6rem', border: '1px solid var(--line)' }} />
        </div>
        <div>
          <label style={{ display: 'block', marginBottom: 4, fontSize: '0.82rem', color: 'var(--muted)' }}>Difficulty</label>
          <select value={difficulty} onChange={(e) => setDifficulty(e.target.value)} style={{ width: '100%', padding: '0.6rem 0.8rem', borderRadius: '0.6rem', border: '1px solid var(--line)' }}>
            {DIFFICULTIES.map((d) => <option key={d} value={d}>{d}</option>)}
          </select>
        </div>
        <div>
          <label style={{ display: 'block', marginBottom: 4, fontSize: '0.82rem', color: 'var(--muted)' }}>Source label</label>
          <input value={sourceLabel} onChange={(e) => setSourceLabel(e.target.value)} style={{ width: '100%', padding: '0.6rem 0.8rem', borderRadius: '0.6rem', border: '1px solid var(--line)' }} />
        </div>
      </div>
      <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '0.75rem', paddingTop: 4 }}>
        <button type="button" className="btn btn-secondary" onClick={onCancel} disabled={saving}>Cancel</button>
        <button type="submit" className="btn btn-primary" disabled={saving}>{saving ? 'Saving…' : 'Save paragraph'}</button>
      </div>
    </form>
  )
}

export default function AdminContentPage() {
  const [rows, setRows] = useState<ContentParagraph[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [search, setSearch] = useState('')
  const [difficulty, setDifficulty] = useState('')
  const [modal, setModal] = useState<Modal | null>(null)

  function load() {
    setLoading(true)
    setError('')
    const params: Record<string, string> = {}
    if (difficulty) params.difficulty = difficulty
    fetchContentParagraphs(params)
      .then((r) => setRows(Array.isArray(r.data) ? r.data : []))
      .catch((e: Error) => setError(e.message))
      .finally(() => setLoading(false))
  }

  useEffect(() => { load() }, [difficulty]) // eslint-disable-line

  async function handleCreate(data: { content: string; language: string; difficulty: string; source_label: string }) {
    await adminFetch<ApiResponse<ContentParagraph>>('POST', '/admin/content/paragraphs', data)
    toast.success('Paragraph created')
    setModal(null)
    load()
  }

  async function handleEdit(id: number, data: Partial<ContentParagraph>) {
    await updateContentParagraph(id, data)
    toast.success('Paragraph updated')
    setModal(null)
    load()
  }

  async function handleDelete(id: number) {
    await deleteContentParagraph(id)
    toast.success('Paragraph deleted')
    setModal(null)
    load()
  }

  const filtered = rows.filter((r) =>
    !search || r.content.toLowerCase().includes(search.toLowerCase()) || (r.source_label ?? '').toLowerCase().includes(search.toLowerCase()),
  )

  return (
    <AdminShell>
      <h1 className="admin-page-title">Content Manager</h1>
      <p className="admin-page-subtitle">Manage typing text paragraphs — create, edit, filter by difficulty.</p>

      {/* Actions bar */}
      <div style={{ display: 'flex', gap: '1rem', marginBottom: '1.5rem', alignItems: 'center', flexWrap: 'wrap' }}>
        <input
          type="text"
          placeholder="Search paragraphs…"
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          style={{ flex: 1, minWidth: 220, padding: '0.65rem 1rem', borderRadius: '0.7rem', border: '1px solid var(--line)' }}
        />
        <select
          value={difficulty}
          onChange={(e) => setDifficulty(e.target.value)}
          style={{ padding: '0.65rem 1rem', borderRadius: '0.7rem', border: '1px solid var(--line)' }}
        >
          <option value="">All difficulties</option>
          {DIFFICULTIES.map((d) => <option key={d} value={d}>{d}</option>)}
        </select>
        <button className="btn btn-primary" onClick={() => setModal({ type: 'create' })}>+ New Paragraph</button>
      </div>

      {/* Create / Edit Modal */}
      {(modal?.type === 'create' || modal?.type === 'edit') && (
        <div className="admin-modal-overlay" onClick={() => setModal(null)}>
          <div className="admin-modal" onClick={(e) => e.stopPropagation()} style={{ maxWidth: 640 }}>
            <h2 className="admin-modal__title">{modal.type === 'create' ? 'New Paragraph' : 'Edit Paragraph'}</h2>
            <ParagraphForm
              initial={modal.type === 'edit' ? modal.row : undefined}
              onSave={modal.type === 'create'
                ? handleCreate
                : (data) => handleEdit((modal as { type: 'edit'; row: ContentParagraph }).row.id, data)}
              onCancel={() => setModal(null)}
            />
          </div>
        </div>
      )}

      {/* Delete Confirmation */}
      {modal?.type === 'delete' && (
        <div className="admin-modal-overlay" onClick={() => setModal(null)}>
          <div className="admin-modal" onClick={(e) => e.stopPropagation()} style={{ maxWidth: 420 }}>
            <h2 className="admin-modal__title">Delete Paragraph</h2>
            <p style={{ marginBottom: '1.5rem', color: 'var(--muted)' }}>
              Are you sure you want to delete this paragraph? This cannot be undone.
            </p>
            <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '0.75rem' }}>
              <button className="btn btn-secondary" onClick={() => setModal(null)}>Cancel</button>
              <button
                className="btn btn-danger"
                onClick={() => void handleDelete((modal as { type: 'delete'; row: ContentParagraph }).row.id)}
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      )}

      {loading ? (
        <div className="admin-loading">Loading…</div>
      ) : error ? (
        <div className="admin-loading" style={{ color: 'var(--danger)' }}>{error}</div>
      ) : filtered.length === 0 ? (
        <div className="admin-empty">No paragraphs found.</div>
      ) : (
        <div className="admin-table-wrap">
          <table className="admin-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Snippet</th>
                <th>Words</th>
                <th>Language</th>
                <th>Difficulty</th>
                <th>Source</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {filtered.map((row) => (
                <tr key={row.id}>
                  <td style={{ color: 'var(--muted)' }}>#{row.id}</td>
                  <td style={{ maxWidth: 340 }}>
                    <div style={{ overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', fontSize: '0.85rem' }}>
                      {row.content}
                    </div>
                  </td>
                  <td style={{ color: 'var(--muted)', fontSize: '0.82rem' }}>{row.word_count}</td>
                  <td style={{ fontSize: '0.82rem' }}>{row.language}</td>
                  <td>
                    <span className={`badge ${row.difficulty === 'easy' ? 'badge--green' : row.difficulty === 'hard' ? 'badge--red' : 'badge--blue'}`}>
                      {row.difficulty}
                    </span>
                  </td>
                  <td style={{ color: 'var(--muted)', fontSize: '0.8rem' }}>{row.source_label ?? '—'}</td>
                  <td style={{ color: 'var(--muted)', fontSize: '0.8rem' }}>
                    {new Date(row.created_at).toLocaleDateString()}
                  </td>
                  <td>
                    <div style={{ display: 'flex', gap: '0.4rem' }}>
                      <button className="btn btn-xs btn-secondary" onClick={() => setModal({ type: 'edit', row })}>Edit</button>
                      <button className="btn btn-xs btn-danger" onClick={() => setModal({ type: 'delete', row })}>Del</button>
                    </div>
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
