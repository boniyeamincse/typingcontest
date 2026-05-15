import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
} from 'react'
import {
  adminLogin,
  adminLogout,
  getAdminToken,
  setAdminToken,
  type AdminUser,
} from '../api/admin'

type AdminAuthContextValue = {
  admin: AdminUser | null
  token: string | null
  loading: boolean
  isAdminAuthenticated: boolean
  hasRole: (role: string) => boolean
  login: (email: string, password: string) => Promise<void>
  logout: () => Promise<void>
}

const AdminAuthContext = createContext<AdminAuthContextValue | undefined>(undefined)

const ADMIN_USER_KEY = 'tc_admin_user'

export function AdminAuthProvider({ children }: { children: React.ReactNode }) {
  const [admin, setAdmin] = useState<AdminUser | null>(() => {
    try {
      const raw = localStorage.getItem(ADMIN_USER_KEY)
      return raw ? (JSON.parse(raw) as AdminUser) : null
    } catch {
      return null
    }
  })
  const [token, setToken] = useState<string | null>(getAdminToken)
  const [loading, setLoading] = useState(false)

  // Hydrate stored admin user on mount
  useEffect(() => {
    if (!getAdminToken()) {
      setAdmin(null)
      setToken(null)
    }
  }, [])

  const login = useCallback(async (email: string, password: string) => {
    setLoading(true)
    try {
      const { token: tok, user } = await adminLogin(email, password)
      setToken(tok)
      setAdmin(user)
      localStorage.setItem(ADMIN_USER_KEY, JSON.stringify(user))
    } finally {
      setLoading(false)
    }
  }, [])

  const logout = useCallback(async () => {
    await adminLogout()
    setAdminToken(null)
    setToken(null)
    setAdmin(null)
    localStorage.removeItem(ADMIN_USER_KEY)
  }, [])

  const hasRole = useCallback(
    (role: string) => {
      return admin?.roles?.some((r) => r.name === role) ?? false
    },
    [admin],
  )

  const value = useMemo<AdminAuthContextValue>(
    () => ({
      admin,
      token,
      loading,
      isAdminAuthenticated: !!token && !!admin,
      hasRole,
      login,
      logout,
    }),
    [admin, token, loading, hasRole, login, logout],
  )

  return <AdminAuthContext.Provider value={value}>{children}</AdminAuthContext.Provider>
}

export function useAdminAuth() {
  const ctx = useContext(AdminAuthContext)
  if (!ctx) throw new Error('useAdminAuth must be used inside AdminAuthProvider')
  return ctx
}
