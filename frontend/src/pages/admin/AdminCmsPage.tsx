import { useEffect, useState } from 'react'
import { toast } from 'react-hot-toast'
import { AdminShell } from '../../admin/AdminShell'
import '../../admin/AdminShell.css'
import { fetchCmsBanners, fetchCmsPages, saveCmsPage } from '../../api/admin'

type CmsPage = { id?: number; slug: string; title: string; content: string; updated_at?: string }
type CmsBanner = { id?: number; title: string; image_url: string; target_url: string; active: boolean }

export default function AdminCmsPage() {
  const [pages, setPages] = useState<CmsPage[]>([])
  const [banners, setBanners] = useState<CmsBanner[]>([])
  const [editingPage, setEditingPage] = useState<CmsPage | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    Promise.allSettled([
      fetchCmsPages().then((r) => setPages(r.data as CmsPage[])),
      fetchCmsBanners().then((r) => setBanners(r.data as CmsBanner[])),
    ]).finally(() => setLoading(false))
  }, [])

  async function handleSavePage(page: CmsPage) {
    try {
      await saveCmsPage(page.slug, page.title, page.content, true)
      toast.success(`Page "${page.slug}" saved`)
      setEditingPage(null)
      fetchCmsPages().then((r) => setPages(r.data as CmsPage[]))
    } catch (err) {
      toast.error(err instanceof Error ? err.message : 'Failed')
    }
  }

  return (
    <AdminShell>
      <h1 className="admin-page-title">CMS / Content</h1>
      <p className="admin-page-subtitle">Edit static pages and manage homepage banners.</p>

      {loading ? <div className="admin-loading">Loading…</div> : (
        <>
          <h2 style={{ fontFamily: 'Chakra Petch, sans-serif', fontSize: '0.9rem', color: 'var(--muted)', marginBottom: 10, textTransform: 'uppercase', letterSpacing: '0.05em' }}>Static pages</h2>
          {pages.length === 0 ? (
            <div className="admin-empty">No CMS pages found.</div>
          ) : (
            <div className="admin-table-wrap" style={{ marginBottom: 32 }}>
              <table className="admin-table">
                <thead>
                  <tr>
                    <th>Slug</th>
                    <th>Title</th>
                    <th>Last updated</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {pages.map((p, i) => (
                    <tr key={i}>
                      <td><code style={{ fontSize: '0.78rem' }}>{p.slug}</code></td>
                      <td style={{ fontWeight: 600 }}>{p.title}</td>
                      <td style={{ color: 'var(--muted)', fontSize: '0.8rem' }}>
                        {p.updated_at ? new Date(p.updated_at).toLocaleDateString() : '—'}
                      </td>
                      <td>
                        <button className="btn btn-sm btn-secondary" onClick={() => setEditingPage(p)}>Edit</button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}

          <h2 style={{ fontFamily: 'Chakra Petch, sans-serif', fontSize: '0.9rem', color: 'var(--muted)', marginBottom: 10, textTransform: 'uppercase', letterSpacing: '0.05em' }}>Banners</h2>
          {banners.length === 0 ? (
            <div className="admin-empty">No banners configured.</div>
          ) : (
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(240px,1fr))', gap: 16 }}>
              {banners.map((b, i) => (
                <div key={i} className="admin-card">
                  <div style={{ fontWeight: 700, marginBottom: 4 }}>{b.title}</div>
                  <div style={{ fontSize: '0.78rem', color: 'var(--muted)', marginBottom: 8, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{b.target_url}</div>
                  <span className={`badge ${b.active ? 'badge--green' : 'badge--gray'}`}>{b.active ? 'Active' : 'Inactive'}</span>
                </div>
              ))}
            </div>
          )}
        </>
      )}

      {editingPage ? (
        <EditPageModal page={editingPage} onClose={() => setEditingPage(null)} onSave={handleSavePage} />
      ) : null}
    </AdminShell>
  )
}

function EditPageModal({ page, onClose, onSave }: { page: CmsPage; onClose: () => void; onSave: (p: CmsPage) => void }) {
  const [title, setTitle] = useState(page.title)
  const [content, setContent] = useState(page.content)
  const [busy, setBusy] = useState(false)

  return (
    <div className="admin-modal-overlay" onClick={onClose}>
      <div className="admin-modal" style={{ maxWidth: 600, width: '95vw' }} onClick={(e) => e.stopPropagation()}>
        <h2>Edit — {page.slug}</h2>
        <label>
          Title
          <input value={title} onChange={(e) => setTitle(e.target.value)} />
        </label>
        <label>
          Content (HTML/Markdown)
          <textarea rows={10} value={content} onChange={(e) => setContent(e.target.value)} style={{ fontFamily: 'monospace', fontSize: '0.8rem' }} />
        </label>
        <div className="admin-modal__actions">
          <button className="btn btn-secondary" onClick={onClose}>Cancel</button>
          <button
            className="btn btn-primary"
            disabled={busy}
            onClick={async () => { setBusy(true); await onSave({ ...page, title, content }) }}
          >
            {busy ? 'Saving…' : 'Save page'}
          </button>
        </div>
      </div>
    </div>
  )
}
