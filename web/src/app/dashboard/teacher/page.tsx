"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";

interface Stats {
  total_classes: number;
  total_students: number;
  absent_today: number;
  pending_assignments: number;
  recent_announcements: { id: number; title: string; priority: string }[];
  recent_grades: { id: number; student?: { full_name?: string }; grade_item?: { title?: string }; score?: number }[];
}

export default function TeacherDashboard() {
  const [stats, setStats] = useState<Stats | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<{ data: Stats }>("/api/dashboard/stats")
      .then((res) => setStats(res.data))
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

  if (error || !stats) {
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
          <h2>Teacher Dashboard</h2>
          <p>Your classes at a glance</p>
        </div>
      </div>

      <div className="row g-3 mb-4">
        <div className="col-md-3">
          <div className="card stat-card"><div className="card-body">
            <div className="stat-label">My Classes</div>
            <div className="stat-value text-primary">{stats.total_classes}</div>
          </div></div>
        </div>
        <div className="col-md-3">
          <div className="card stat-card"><div className="card-body">
            <div className="stat-label">Total Students</div>
            <div className="stat-value text-info">{stats.total_students}</div>
          </div></div>
        </div>
        <div className="col-md-3">
          <div className="card stat-card"><div className="card-body">
            <div className="stat-label">Absent Today</div>
            <div className="stat-value text-danger">{stats.absent_today}</div>
          </div></div>
        </div>
        <div className="col-md-3">
          <div className="card stat-card"><div className="card-body">
            <div className="stat-label">Pending Assignments</div>
            <div className="stat-value text-warning">{stats.pending_assignments}</div>
          </div></div>
        </div>
      </div>

      <div className="row g-3">
        <div className="col-md-6">
          <div className="card stat-card"><div className="card-body">
            <h5 className="card-title">Recent Announcements</h5>
            {stats.recent_announcements.length === 0 && <p className="text-muted mb-0">None yet.</p>}
            {stats.recent_announcements.map((a) => (
              <div key={a.id} className="mb-1">
                <span className={`badge bg-${a.priority === "Urgent" ? "danger" : a.priority === "Important" ? "warning" : "secondary"} me-1`}>{a.priority}</span>
                {a.title}
              </div>
            ))}
          </div></div>
        </div>
        <div className="col-md-6">
          <div className="card stat-card"><div className="card-body">
            <h5 className="card-title">Recent Grades</h5>
            {stats.recent_grades.length === 0 && <p className="text-muted mb-0">None yet.</p>}
            {stats.recent_grades.map((g) => (
              <div key={g.id} className="mb-1 small">
                <strong>{g.student?.full_name || "Student"}</strong>: {g.grade_item?.title || "Item"} — {g.score ?? "—"}
              </div>
            ))}
          </div></div>
        </div>
      </div>
    </>
  );
}
