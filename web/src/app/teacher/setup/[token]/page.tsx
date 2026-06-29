"use client";

import { api } from "@/lib/api";
import Link from "next/link";
import { useParams, useRouter, useSearchParams } from "next/navigation";
import { useState, type FormEvent } from "react";

export default function TeacherSetupPage() {
  const params = useParams<{ token: string }>();
  const router = useRouter();
  const searchParams = useSearchParams();
  const [email, setEmail] = useState(searchParams.get("email") || "");
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [confirm, setConfirm] = useState("");
  const [error, setError] = useState("");
  const [message, setMessage] = useState("");
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
      const res = await api.post<{ message: string }>("/api/session/teacher/setup", {
        token: params.token,
        email,
        username,
        password,
        password_confirmation: confirm,
      });
      setMessage(res.message);
      window.setTimeout(() => router.push("/login"), 1200);
    } catch (err: unknown) {
      const e = err as { message?: string; errors?: Record<string, string[]> };
      const msgs = e.errors ? Object.values(e.errors).flat().join("; ") : e.message;
      setError(msgs || "Teacher setup failed.");
    } finally {
      setBusy(false);
    }
  };

  return (
    <div className="auth-page">
      <div className="auth-card">
        <h1>Teacher Setup</h1>
        <p>Choose your username and password to activate your teacher account.</p>

        {message && <div className="alert alert-success">{message}</div>}
        {error && <div className="alert alert-danger">{error}</div>}

        <form onSubmit={handleSubmit}>
          <div className="mb-3">
            <label className="form-label">Email</label>
            <input type="email" className="form-control" value={email} onChange={(e) => setEmail(e.target.value)} required />
          </div>
          <div className="mb-3">
            <label className="form-label">Username</label>
            <input type="text" className="form-control" value={username} onChange={(e) => setUsername(e.target.value)} required autoFocus />
          </div>
          <div className="mb-3">
            <label className="form-label">Password</label>
            <input type="password" className="form-control" value={password} onChange={(e) => setPassword(e.target.value)} required minLength={8} />
          </div>
          <div className="mb-3">
            <label className="form-label">Confirm Password</label>
            <input type="password" className="form-control" value={confirm} onChange={(e) => setConfirm(e.target.value)} required minLength={8} />
          </div>
          <button type="submit" className="btn btn-primary w-100" disabled={busy}>
            {busy ? "Completing setup..." : "Complete setup"}
          </button>
        </form>

        <div className="mt-3 text-center small">
          <Link href="/login">Back to sign in</Link>
        </div>
      </div>
    </div>
  );
}
