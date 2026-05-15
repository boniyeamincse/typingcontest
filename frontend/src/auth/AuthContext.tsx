import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
} from 'react'
import {
  getStoredToken,
  login as apiLogin,
  logout as apiLogout,
  me as apiMe,
  register as apiRegister,
  setStoredToken,
  type AuthUser,
} from '../api/http'

type LoginInput = {
  email: string
  password: string
}

type RegisterInput = {
  username: string
  name: string
  email: string
  password: string
  password_confirmation: string
}

type AuthContextValue = {
  user: AuthUser | null
  token: string | null
  loading: boolean
  isAuthenticated: boolean
  login: (input: LoginInput) => Promise<void>
  register: (input: RegisterInput) => Promise<void>
  logout: () => Promise<void>
  refreshMe: () => Promise<void>
}

const AuthContext = createContext<AuthContextValue | undefined>(undefined)

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null)
  const [token, setToken] = useState<string | null>(getStoredToken())
  const [loading, setLoading] = useState(true)

  const refreshMe = useCallback(async () => {
    if (!token) {
      setUser(null)
      return
    }

    const result = await apiMe(token)
    setUser(result.user)
  }, [token])

  useEffect(() => {
    async function bootstrap() {
      try {
        await refreshMe()
      } catch {
        setStoredToken(null)
        setToken(null)
        setUser(null)
      } finally {
        setLoading(false)
      }
    }

    bootstrap()
  }, [refreshMe])

  const login = useCallback(async (input: LoginInput) => {
    const result = await apiLogin(input)
    setStoredToken(result.token)
    setToken(result.token)
    setUser(result.user)
  }, [])

  const register = useCallback(async (input: RegisterInput) => {
    const result = await apiRegister(input)
    setStoredToken(result.token)
    setToken(result.token)
    setUser(result.user)
  }, [])

  const logout = useCallback(async () => {
    try {
      if (token) {
        await apiLogout(token)
      }
    } finally {
      setStoredToken(null)
      setToken(null)
      setUser(null)
    }
  }, [token])

  const value = useMemo<AuthContextValue>(
    () => ({
      user,
      token,
      loading,
      isAuthenticated: Boolean(user && token),
      login,
      register,
      logout,
      refreshMe,
    }),
    [loading, login, logout, refreshMe, token, user, register],
  )

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth(): AuthContextValue {
  const context = useContext(AuthContext)

  if (!context) {
    throw new Error('useAuth must be used inside AuthProvider')
  }

  return context
}
