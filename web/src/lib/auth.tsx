"use client";

import {
  createContext,
  useContext,
  useState,
  useEffect,
  useCallback,
  type ReactNode,
} from "react";
import { api } from "./api";

export interface User {
  id: number;
  username: string;
  name: string;
  email: string;
  role: "admin" | "teacher" | "student";
  status: string;
  classroom_verified?: boolean;
  google_linked?: boolean;
  github_linked?: boolean;
  github_username?: string | null;
  avatar_url?: string | null;
  teacher?: {
    id: number;
    employee_number: string;
    full_name: string;
    department: string;
  } | null;
  student?: {
    id: number;
    student_number: string;
    full_name: string;
  } | null;
}

interface AuthContextType {
  user: User | null;
  loading: boolean;
  login: (username: string, password: string) => Promise<void>;
  register: (name: string, email: string, password: string, passwordConfirmation: string) => Promise<void>;
  logout: () => Promise<void>;
  refresh: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | null>(null);
const SESSION_BOOTSTRAP_TIMEOUT_MS = 8000;

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  const refresh = useCallback(async () => {
    try {
      const res = await Promise.race([
        api.get<{ user: User }>("/api/session/me"),
        new Promise<never>((_, reject) =>
          setTimeout(() => reject(new Error("Session bootstrap timed out.")), SESSION_BOOTSTRAP_TIMEOUT_MS)
        ),
      ]);
      setUser(res.user);
    } catch {
      setUser(null);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    refresh();
  }, [refresh]);

  const login = useCallback(async (username: string, password: string) => {
    await api.csrf();
    const res = await api.post<{ user: User; message: string }>("/api/session/login", {
      username,
      password,
    });
    setUser(res.user);
  }, []);

  const register = useCallback(
    async (name: string, email: string, password: string, passwordConfirmation: string) => {
      await api.csrf();
      const res = await api.post<{ user: User; message: string }>("/api/session/register", {
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
      });
      setUser(res.user);
    },
    []
  );

  const logout = useCallback(async () => {
    await api.csrf();
    await api.post("/api/session/logout");
    setUser(null);
  }, []);

  return (
    <AuthContext.Provider value={{ user, loading, login, register, logout, refresh }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth(): AuthContextType {
  const ctx = useContext(AuthContext);
  if (!ctx) {
    throw new Error("useAuth must be used within an AuthProvider");
  }
  return ctx;
}
