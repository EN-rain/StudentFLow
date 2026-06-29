"use client";

import { useAuth } from "@/lib/auth";
import { useRouter } from "next/navigation";
import { useEffect } from "react";

export default function DashboardRedirect() {
  const { user, loading } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (loading || !user) return;
    const role = user.role;
    router.replace(role === "admin" ? "/dashboard/admin" : role === "teacher" ? "/dashboard/teacher" : "/dashboard/student");
  }, [user, loading, router]);

  return (
    <div className="loading-screen">
      <div className="spinner-border" />
      <p>Loading dashboard...</p>
    </div>
  );
}
