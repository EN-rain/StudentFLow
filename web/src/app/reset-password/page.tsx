"use client";

import { useState, type FormEvent, use } from "react";
import { api } from "@/lib/api";
import Link from "next/link";

export default function ResetPasswordPage(props: {
  searchParams?: Promise<{ token?: string; email?: string }>;
}) {
  const sp = use(props.searchParams ?? Promise.resolve<{ token?: string; email?: string }>({}));
  const [token] = useState(sp.token || "");
  const [email, setEmail] = useState(sp.email || "");
  const [password, setPassword] = useState("");
  const [confirm, setConfirm] = useState("");
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");
  const [busy, setBusy] = useState(false);

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError("");
    setMessage("");
    if (password !== confirm) {
      setError("Passwords do not match.");
      return;
    }
    setBusy(true);
    try {
      const res = await api.post<{ message: string }>("/api/auth/reset-password", {
        token,
        email,
        password,
        password_confirmation: confirm,
      });
      setMessage(res.message);
    } catch (err: unknown) {
      const e = err as { message?: string; errors?: Record<string, string[]> };
      const msgs = e.errors ? Object.values(e.errors).flat().join("; ") : e.message;
      setError(msgs || "Reset failed.");
    } finally {
      setBusy(false);
    }
  };

  if (!token) {
    return (
      <div className="auth-page">
        <div className="auth-card">
          <h1>Invalid Link</h1>
          <p>This password reset link is invalid or expired.</p>
          <Link href="/forgot-password">Request a new reset link</Link>
        </div>
      </div>
    );
  }

  return (
    <div className="auth-page">
      <div className="auth-card">
        <h1>Reset Password</h1>
        <p>Choose a new password for {email}</p>

        {message && <div className="alert alert-success">{message}</div>}
        {error && <div className="alert alert-danger">{error}</div>}

        <form onSubmit={handleSubmit}>
          <div className="mb-3">
            <label className="form-label">Email</label>
            <input type="email" className="form-control" value={email} onChange={(e) => setEmail(e.target.value)} required />
          </div>
          <div className="mb-3">
            <label className="form-label">New Password (min 8)</label>
            <input type="password" className="form-control" value={password} onChange={(e) => setPassword(e.target.value)} required minLength={8} />
          </div>
          <div className="mb-3">
            <label className="form-label">Confirm Password</label>
            <input type="password" className="form-control" value={confirm} onChange={(e) => setConfirm(e.target.value)} required minLength={8} />
          </div>
          <button type="submit" className="btn btn-primary w-100" disabled={busy}>
            {busy ? "Resetting..." : "Reset Password"}
          </button>
        </form>

        <div className="mt-3 text-center small">
          <Link href="/login">Back to sign in</Link>
        </div>
      </div>
    </div>
  );
}
