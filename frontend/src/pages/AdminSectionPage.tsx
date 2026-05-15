import { Link, Navigate, useParams } from 'react-router-dom'
import { AppShell } from '../components/AppShell'
import { getAdminSection } from '../admin/adminSections'
import './AdminSectionPage.css'

export default function AdminSectionPage() {
  const { slug } = useParams<{ slug: string }>()

  if (!slug) {
    return <Navigate to="/admin/dashboard" replace />
  }

  const section = getAdminSection(slug)

  if (!section) {
    return <Navigate to="/admin/dashboard" replace />
  }

  return (
    <AppShell
      title={`Admin • ${section.title}`}
      subtitle="Dedicated module page with submenu and capability overview."
      actions={
        <>
          {slug === 'contests' ? (
            <Link to="/admin/contests" className="btn-primary">
              Open Contest Studio
            </Link>
          ) : null}
          <Link to="/admin/dashboard" className="btn-secondary">
            Back to Admin Dashboard
          </Link>
        </>
      }
    >
      <div className="admin-section-layout">
        <section className="admin-section-card surface-card">
          <h2>{section.title}</h2>

          {section.submenus?.length ? (
            <>
              <h3>Submenus</h3>
              <div className="admin-section-tags">
                {section.submenus.map((item) => (
                  <span key={item}>{item}</span>
                ))}
              </div>
            </>
          ) : null}

          {section.features?.length ? (
            <>
              <h3>Features</h3>
              <ul>
                {section.features.map((feature) => (
                  <li key={feature}>{feature}</li>
                ))}
              </ul>
            </>
          ) : null}

          {section.notes?.length ? (
            <>
              <h3>Notes</h3>
              <ul>
                {section.notes.map((note) => (
                  <li key={note}>{note}</li>
                ))}
              </ul>
            </>
          ) : null}
        </section>

        <section className="admin-section-meta surface-card">
          <h3>Module Status</h3>
          <p>This is a dedicated route scaffold for the {section.title} module.</p>
          <p>Next implementation phase can connect backend APIs and module-specific CRUD screens.</p>
        </section>
      </div>
    </AppShell>
  )
}