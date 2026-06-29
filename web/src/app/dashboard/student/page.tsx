"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import { useAuth } from "@/lib/auth";

interface DashboardData {
  student: { full_name: string };
  classes_count: number;
  announcements_count: number;
  assignments_count: number;
  pending_exams_count: number;
}

export default function StudentDashboard() {
  const { user } = useAuth();
  const [data, setData] = useState<DashboardData | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<{ data: DashboardData }>("/api/student/dashboard")
      .then((res) => setData(res.data))
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) {
    return (
      <div className="loading-screen">
        <div className="spinner-border" />
        <p>Loading dashboard...</p>
      </div>
    );
  }

  if (error || !data) {
    return (
      <div className="sf-retry-banner">
        {error || "Could not load data."} The server may be waking up.
      </div>
    );
  }

  return (
    <>
      <div className="page-header">
        <div>
          <h2>Student Dashboard</h2>
          <p>Your classes, assignments, grades, and upcoming exams at a glance.</p>
        </div>
        <div className="text-muted">Welcome, {data.student.full_name || user?.name}</div>
      </div>

      <div className="row g-3 mb-4">
        <div className="col-md-3">
          <div className="card stat-card"><div className="card-body">
            <div className="stat-label">Enrolled Classes</div>
            <div className="stat-value text-primary">{data.classes_count}</div>
          </div></div>
        </div>
        <div className="col-md-3">
          <div className="card stat-card"><div className="card-body">
            <div className="stat-label">Announcements</div>
            <div className="stat-value text-info">{data.announcements_count}</div>
          </div></div>
        </div>
        <div className="col-md-3">
          <div className="card stat-card"><div className="card-body">
            <div className="stat-label">Assignments</div>
            <div className="stat-value text-primary">{data.assignments_count}</div>
          </div></div>
        </div>
        <div className="col-md-3">
          <div className="card stat-card"><div className="card-body">
            <div className="stat-label">Pending Exams</div>
            <div className="stat-value text-warning">{data.pending_exams_count}</div>
          </div></div>
        </div>
      </div>
    </>
  );
}
