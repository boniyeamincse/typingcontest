import { Navigate, Route, Routes } from 'react-router-dom'
import { AuthProvider } from './auth/AuthContext'
import { ProtectedRoute } from './components/ProtectedRoute'
import { DashboardPage } from './pages/DashboardPage'
import { LoginPage } from './pages/LoginPage'
import { RegisterPage } from './pages/RegisterPage'
import ContestsPage from './pages/ContestsPage'
import ContestDetailPage from './pages/ContestDetailPage'
import TypingArenaPage from './pages/TypingArenaPage'
import AdminContestsPage from './pages/AdminContestsPage'
import AdminDashboardPage from './pages/AdminDashboardPage'
import './App.css'

function App() {
  return (
    <AuthProvider>
      <Routes>
        <Route path="/" element={<Navigate to="/dashboard" replace />} />
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />
        <Route
          path="/dashboard"
          element={
            <ProtectedRoute>
              <DashboardPage />
            </ProtectedRoute>
          }
        />
        <Route
          path="/admin/dashboard"
          element={
            <ProtectedRoute>
              <AdminDashboardPage />
            </ProtectedRoute>
          }
        />
        <Route
          path="/admin/contests"
          element={
            <ProtectedRoute>
              <AdminContestsPage />
            </ProtectedRoute>
          }
        />
        <Route path="/contests" element={<ContestsPage />} />
        <Route path="/contests/:id" element={<ContestDetailPage />} />
        <Route
          path="/contests/:id/play"
          element={
            <ProtectedRoute>
              <TypingArenaPage />
            </ProtectedRoute>
          }
        />
        <Route path="*" element={<Navigate to="/dashboard" replace />} />
      </Routes>
    </AuthProvider>
  )
}

export default App
