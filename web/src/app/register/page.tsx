"use client";

import { useState, type FormEvent } from "react";
import { useAuth } from "@/lib/auth";
import { useRouter } from "next/navigation";
import Link from "next/link";

export default function RegisterPage() {
  const { register, user } = useAuth();
  const router = useRouter();
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [confirm, setConfirm] = useState("");
  const [error, setError] = useState("");
  const [busy, setBusy] = useState(false);

  if (user) {
    router.replace("/dashboard");
    return null;
  }

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError("");
    if (password !== confirm) {
      setError("Passwords do not match.");
      return;
    }
    setBusy(true);
    try {
      await register(name, email, password, confirm);
      router.push("/dashboard");
    } catch (err: unknown) {
      const e = err as { message?: string; errors?: Record<string, string[]> };
      const msgs = e.errors ? Object.values(e.errors).flat().join("; ") : e.message;
      setError(msgs || "Registration failed.");
    } finally {
      setBusy(false);
    }
  };

  return (
    <div className="auth-page">
      <div className="auth-card">
        <h1>Sign up</h1>
        <p>Create your StudentFlow account</p>

        {error && <div className="alert alert-danger">{error}</div>}

        <form onSubmit={handleSubmit}>
          <div className="mb-3">
            <label className="form-label">Full Name</label>
            <input type="text" name="name" className="form-control" value={name} onChange={(e) => setName(e.target.value)} required autoComplete="name" autoFocus />
          </div>
          <div className="mb-3">
            <label className="form-label">Email</label>
            <input type="email" name="email" className="form-control" value={email} onChange={(e) => setEmail(e.target.value)} required autoComplete="email" />
          </div>
          <div className="mb-3">
            <label className="form-label">Password (min 8 characters)</label>
            <input type="password" name="password" className="form-control" value={password} onChange={(e) => setPassword(e.target.value)} required minLength={8} autoComplete="new-password" />
          </div>
          <div className="mb-3">
            <label className="form-label">Confirm Password</label>
            <input type="password" name="password_confirmation" className="form-control" value={confirm} onChange={(e) => setConfirm(e.target.value)} required minLength={8} autoComplete="new-password" />
          </div>
          <button type="submit" className="btn btn-primary w-100" disabled={busy}>
            {busy ? "Creating account..." : "Sign up"}
          </button>
        </form>

        <div className="mt-3 text-center small">
          <span className="text-muted">Already have an account? </span>
          <Link href="/login">Sign in</Link>
        </div>
      </div>
    </div>
  );
}
