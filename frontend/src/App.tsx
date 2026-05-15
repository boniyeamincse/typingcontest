import { type ReactNode } from 'react'
import { Navigate, Route, Routes } from 'react-router-dom'
import { Toaster } from 'react-hot-toast'
import { AuthProvider } from './auth/AuthContext'
import { AdminAuthProvider, useAdminAuth } from './admin/AdminAuthContext'
import { ProtectedRoute } from './components/ProtectedRoute'
import { LandingPage } from './pages/LandingPage'
import { DashboardPage } from './pages/DashboardPage'
import { LoginPage } from './pages/LoginPage'
import { RegisterPage } from './pages/RegisterPage'
import ContestsPage from './pages/ContestsPage'
import ContestDetailPage from './pages/ContestDetailPage'
import ContestLobbyPage from './pages/ContestLobbyPage'
import TypingArenaPage from './pages/TypingArenaPage'
import ProfilePage from './pages/ProfilePage'
import LeaderboardPage from './pages/LeaderboardPage'
// Admin pages
import AdminLoginPage from './pages/AdminLoginPage'
import AdminOverviewPage from './pages/admin/AdminOverviewPage'
import AdminUsersPage from './pages/admin/AdminUsersPage'
import AdminContestsPage from './pages/admin/AdminContestsPage'
import AdminSupportPage from './pages/admin/AdminSupportPage'
import AdminPaymentsPage from './pages/admin/AdminPaymentsPage'
import AdminSecurityPage from './pages/admin/AdminSecurityPage'
import AdminLeaderboardPage from './pages/admin/AdminLeaderboardPage'
import AdminReportsPage from './pages/admin/AdminReportsPage'
import AdminNotificationsPage from './pages/admin/AdminNotificationsPage'
import AdminRolesPage from './pages/admin/AdminRolesPage'
import AdminSystemPage from './pages/admin/AdminSystemPage'
import AdminCmsPage from './pages/admin/AdminCmsPage'
import AdminLiveMonitorPage from './pages/admin/AdminLiveMonitorPage'
import AdminSubscriptionsPage from './pages/admin/AdminSubscriptionsPage'
import AdminBadgesPage from './pages/admin/AdminBadgesPage'
import AdminContentPage from './pages/admin/AdminContentPage'
import AdminAdvertisementsPage from './pages/admin/AdminAdvertisementsPage'
import AdminSponsorsPage from './pages/admin/AdminSponsorsPage'
import AdminApiManagementPage from './pages/admin/AdminApiManagementPage'
import AdminBackupMaintenancePage from './pages/admin/AdminBackupMaintenancePage'
import AdminInfraMonitoringPage from './pages/admin/AdminInfraMonitoringPage'
import AdminSectionPage from './pages/AdminSectionPage'
import './App.css'

function AdminProtectedRoute({ children }: { children: ReactNode }) {
  const { isAdminAuthenticated, loading } = useAdminAuth()
  if (loading) return null
  if (!isAdminAuthenticated) {
    return <Navigate to="/admin/login" replace />
  }
  return <>{children}</>
}

function App() {
  return (
    <AdminAuthProvider>
      <AuthProvider>
        <Toaster position="top-right" toastOptions={{ duration: 3500 }} />
        <Routes>
          {/* ── Public ───────────────────────────────────────────────── */}
          <Route path="/" element={<LandingPage />} />
          <Route path="/login" element={<LoginPage />} />
          <Route path="/register" element={<RegisterPage />} />

          {/* ── User ─────────────────────────────────────────────────── */}
          <Route path="/dashboard" element={<ProtectedRoute><DashboardPage /></ProtectedRoute>} />
          <Route path="/contests" element={<ContestsPage />} />
          <Route path="/contests/:id" element={<ContestDetailPage />} />
          <Route path="/contests/:id/lobby" element={<ProtectedRoute><ContestLobbyPage /></ProtectedRoute>} />
          <Route path="/contests/:id/play" element={<ProtectedRoute><TypingArenaPage /></ProtectedRoute>} />
          <Route path="/profile" element={<ProtectedRoute><ProfilePage /></ProtectedRoute>} />
          <Route path="/leaderboard" element={<LeaderboardPage />} />

          {/* ── Admin auth ───────────────────────────────────────────── */}
          <Route path="/admin/login" element={<AdminLoginPage />} />

          {/* ── Admin protected ──────────────────────────────────────── */}
          <Route path="/admin" element={<AdminProtectedRoute><AdminOverviewPage /></AdminProtectedRoute>} />
          <Route path="/admin/dashboard" element={<AdminProtectedRoute><AdminOverviewPage /></AdminProtectedRoute>} />
          <Route path="/admin/overview" element={<AdminProtectedRoute><AdminOverviewPage /></AdminProtectedRoute>} />
          <Route path="/admin/users" element={<AdminProtectedRoute><AdminUsersPage /></AdminProtectedRoute>} />
          <Route path="/admin/contests" element={<AdminProtectedRoute><AdminContestsPage /></AdminProtectedRoute>} />
          <Route path="/admin/support" element={<AdminProtectedRoute><AdminSupportPage /></AdminProtectedRoute>} />
          <Route path="/admin/payments" element={<AdminProtectedRoute><AdminPaymentsPage /></AdminProtectedRoute>} />
          <Route path="/admin/security" element={<AdminProtectedRoute><AdminSecurityPage /></AdminProtectedRoute>} />
          <Route path="/admin/leaderboard" element={<AdminProtectedRoute><AdminLeaderboardPage /></AdminProtectedRoute>} />
          <Route path="/admin/reports" element={<AdminProtectedRoute><AdminReportsPage /></AdminProtectedRoute>} />
          <Route path="/admin/notifications" element={<AdminProtectedRoute><AdminNotificationsPage /></AdminProtectedRoute>} />
          <Route path="/admin/roles" element={<AdminProtectedRoute><AdminRolesPage /></AdminProtectedRoute>} />
          <Route path="/admin/system" element={<AdminProtectedRoute><AdminSystemPage /></AdminProtectedRoute>} />
          <Route path="/admin/cms" element={<AdminProtectedRoute><AdminCmsPage /></AdminProtectedRoute>} />
          <Route path="/admin/live" element={<AdminProtectedRoute><AdminLiveMonitorPage /></AdminProtectedRoute>} />
          <Route path="/admin/subscriptions" element={<AdminProtectedRoute><AdminSubscriptionsPage /></AdminProtectedRoute>} />
          <Route path="/admin/badges" element={<AdminProtectedRoute><AdminBadgesPage /></AdminProtectedRoute>} />
          <Route path="/admin/content" element={<AdminProtectedRoute><AdminContentPage /></AdminProtectedRoute>} />
          <Route path="/admin/advertisements" element={<AdminProtectedRoute><AdminAdvertisementsPage /></AdminProtectedRoute>} />
          <Route path="/admin/sponsors" element={<AdminProtectedRoute><AdminSponsorsPage /></AdminProtectedRoute>} />
          <Route path="/admin/api-management" element={<AdminProtectedRoute><AdminApiManagementPage /></AdminProtectedRoute>} />
          <Route path="/admin/backup-maintenance" element={<AdminProtectedRoute><AdminBackupMaintenancePage /></AdminProtectedRoute>} />
          <Route path="/admin/system-monitoring" element={<AdminProtectedRoute><AdminInfraMonitoringPage /></AdminProtectedRoute>} />
          <Route path="/admin/section/:slug" element={<AdminProtectedRoute><AdminSectionPage /></AdminProtectedRoute>} />

          <Route path="*" element={<Navigate to="/dashboard" replace />} />
        </Routes>
      </AuthProvider>
    </AdminAuthProvider>
  )
}

export default App
