'use client';

import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useState,
} from "react";

import {
  AuthUser,
  LoginCredentials,
  RegisterCredentials,
  fetchCurrentUser,
  loginWithCredentials,
  logoutFromApi,
  registerWithCredentials,
} from "@/lib/auth-service";
import { clearAuthToken, getStoredAuthToken } from "@/lib/auth-storage";

interface AuthContextValue {
  user: AuthUser | null;
  initializing: boolean;
  refreshUser: () => Promise<void>;
  login: (credentials: LoginCredentials) => Promise<AuthUser>;
  register: (credentials: RegisterCredentials) => Promise<AuthUser>;
  logout: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

async function loadCurrentUser(setter: (user: AuthUser | null) => void) {
  try {
    const profile = await fetchCurrentUser();
    setter(profile);
  } catch (error) {
    clearAuthToken();
    setter(null);
  }
}

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [initializing, setInitializing] = useState(true);

  const refreshUser = useCallback(async () => {
    if (!getStoredAuthToken()) {
      setUser(null);
      return;
    }
    await loadCurrentUser(setUser);
  }, []);

  useEffect(() => {
    let mounted = true;
    (async () => {
      if (!getStoredAuthToken()) {
        if (mounted) {
          setInitializing(false);
        }
        return;
      }
      await loadCurrentUser((loadedUser) => {
        if (mounted) {
          setUser(loadedUser);
        }
      });
      if (mounted) {
        setInitializing(false);
      }
    })();

    return () => {
      mounted = false;
    };
  }, []);

  const handleLogin = useCallback(async (credentials: LoginCredentials) => {
    const authUser = await loginWithCredentials(credentials);
    setUser(authUser);
    return authUser;
  }, []);

  const handleRegister = useCallback(
    async (credentials: RegisterCredentials) => {
      const authUser = await registerWithCredentials(credentials);
      setUser(authUser);
      return authUser;
    },
    [],
  );

  const handleLogout = useCallback(async () => {
    await logoutFromApi();
    setUser(null);
  }, []);

  return (
    <AuthContext.Provider
      value={{
        user,
        initializing,
        refreshUser,
        login: handleLogin,
        register: handleRegister,
        logout: handleLogout,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error("useAuth must be used within AuthProvider");
  }
  return context;
}
