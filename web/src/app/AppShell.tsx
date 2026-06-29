"use client";

import { useAuth } from "@/lib/auth";
import Sidebar from "@/lib/components/Sidebar";
import { usePathname, useRouter } from "next/navigation";
import { useEffect, type ReactNode } from "react";

const PUBLIC_ROUTES = ["/login", "/register", "/forgot-password", "/reset-password", "/teacher/setup"];

export default function AppShell({ children }: { children: ReactNode }) {
  const { user, loading } = useAuth();
  const pathname = usePathname();
  const router = useRouter();

  const isPublic = PUBLIC_ROUTES.some((r) => pathname.startsWith(r));

  useEffect(() => {
    if (loading) return;
    if (!user && !isPublic) {
      router.push("/login");
    }
  }, [user, loading, isPublic, router]);

  if (loading) {
    return (
      <div className="loading-screen">
        <div className="spinner-border" />
        <p>Loading StudentFlow...</p>
      </div>
    );
  }

  if (!user && isPublic) {
    return <>{children}</>;
  }

  if (!user) {
    return (
      <div className="loading-screen">
        <div className="spinner-border" />
        <p>Redirecting to login...</p>
      </div>
    );
  }

  return (
    <div className="sf-layout">
      <Sidebar />
      <div className="sf-main">
        <div className="sf-topbar">
          <span>
            <strong>{user.name}</strong> <span className="text-muted">({user.role})</span>
          </span>
        </div>
        <div className="sf-content">{children}</div>
      </div>
    </div>
  );
}
