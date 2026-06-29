"use client";

import { useState, type FormEvent } from "react";
import { useAuth } from "@/lib/auth";
import { useRouter, useSearchParams } from "next/navigation";
import Link from "next/link";

export default function LoginPage() {
  const { login, user } = useAuth();
  const router = useRouter();
  const searchParams = useSearchParams();
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState(searchParams.get("error") || "");
  const [busy, setBusy] = useState(false);

  if (user) {
    router.replace("/dashboard");
    return null;
  }

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError("");
    setBusy(true);
    try {
      await login(username, password);
      router.push("/dashboard");
    } catch (err: unknown) {
      const e = err as { message?: string; errors?: Record<string, string[]> };
      setError(e.errors?.username?.[0] || e.message || "Login failed.");
    } finally {
      setBusy(false);
    }
  };

  return (
    <div className="auth-page auth-page-login">
      <div className="auth-hero" aria-hidden="true">
        <div className="auth-hero-panel">
          <span className="auth-kicker">StudentFlow</span>
          <h2>Stay on top of classes, grades, and school updates.</h2>
          <p>One place for your academic flow, with the same visual tone as the mobile app.</p>
        </div>
      </div>
      <div className="auth-card">
        <h1>Sign in</h1>
        <p>Welcome back to StudentFlow</p>

        {error && <div className="alert alert-danger">{error}</div>}

        <form onSubmit={handleSubmit}>
          <div className="mb-3">
            <label className="form-label">Username or Email</label>
            <input
              type="text"
              name="username"
              className="form-control"
              value={username}
              onChange={(e) => setUsername(e.target.value)}
              required
              autoFocus
            />
          </div>
          <div className="mb-3">
            <label className="form-label">Password</label>
            <input
              type="password"
              name="password"
              className="form-control"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
            />
          </div>
          <button type="submit" className="btn btn-primary w-100" disabled={busy}>
            {busy ? "Signing in..." : "Sign in"}
          </button>
        </form>

        <div className="mt-3 text-center small">
          <Link href="/forgot-password">Forgot password?</Link>
          <br />
          <span className="text-muted">No account? </span>
          <Link href="/register">Sign up</Link>
        </div>
      </div>
    </div>
  );
}
