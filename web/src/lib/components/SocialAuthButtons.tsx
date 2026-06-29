"use client";

import { api } from "@/lib/api";
import { useRouter } from "next/navigation";
import { useEffect, useRef, useState } from "react";

declare global {
  interface Window {
    google?: {
      accounts: {
        id: {
          initialize: (options: {
            client_id: string;
            callback: (response: { credential?: string }) => void;
          }) => void;
          renderButton: (
            parent: HTMLElement,
            options: Record<string, string | number | boolean>
          ) => void;
        };
      };
    };
  }
}

export default function SocialAuthButtons() {
  const router = useRouter();
  const googleRef = useRef<HTMLDivElement | null>(null);
  const [error, setError] = useState("");

  useEffect(() => {
    const clientId = process.env.NEXT_PUBLIC_GOOGLE_CLIENT_ID;
    if (!clientId || !window.google || !googleRef.current) {
      return;
    }

    googleRef.current.innerHTML = "";
    window.google.accounts.id.initialize({
      client_id: clientId,
      callback: async ({ credential }) => {
        if (!credential) {
          setError("Google sign-in failed.");
          return;
        }

        try {
          await api.csrf();
          await api.post("/api/session/google", { id_token: credential });
          router.push("/dashboard");
        } catch (err: unknown) {
          const e = err as { message?: string; errors?: Record<string, string[]> };
          const msg = e.errors ? Object.values(e.errors).flat().join("; ") : e.message;
          setError(msg || "Google sign-in failed.");
        }
      },
    });
    window.google.accounts.id.renderButton(googleRef.current, {
      theme: "outline",
      size: "large",
      width: 320,
      text: "continue_with",
      shape: "pill",
    });
  }, [router]);

  return (
    <>
      <div className="auth-divider"><span>or continue with</span></div>
      <a
        className="btn btn-outline-secondary w-100 d-flex justify-content-center align-items-center gap-2"
        href={`${process.env.NEXT_PUBLIC_API_BASE_URL || "http://localhost:8000"}/api/session/github/redirect`}
      >
        <i className="bi bi-github" />
        <span>Continue with GitHub</span>
      </a>
      <div className="mt-2 d-flex justify-content-center">
        <div ref={googleRef} />
      </div>
      {error && <div className="alert alert-danger mt-3">{error}</div>}
    </>
  );
}
