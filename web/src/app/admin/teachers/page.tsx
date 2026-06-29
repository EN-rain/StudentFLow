"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";

interface Teacher {
  id: number;
  employee_number: string;
  full_name?: string;
  department?: string;
  user?: { email: string; status: string };
}

export default function AdminTeachersPage() {
  const [teachers, setTeachers] = useState<Teacher[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<{ data: Teacher[] }>("/api/admin/teachers")
      .then((res) => setTeachers(res.data))
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading teachers...</p></div>;
  if (error) return <div className="sf-retry-banner">{error}</div>;

  return (
    <>
      <div className="page-header">
        <div>
          <h2>Teachers</h2>
          <p>Manage teacher accounts</p>
        </div>
      </div>
      <div className="card stat-card"><div className="card-body p-0">
        <table className="table table-hover mb-0">
          <thead className="table-light"><tr><th>Employee #</th><th>Name</th><th>Email</th><th>Department</th><th>Status</th></tr></thead>
          <tbody>
            {teachers.map((t) => (
              <tr key={t.id}>
                <td>{t.employee_number}</td>
                <td>{t.full_name || "—"}</td>
                <td><small>{t.user?.email || "—"}</small></td>
                <td>{t.department || "—"}</td>
                <td><span className={`badge bg-${t.user?.status === "active" ? "success" : "secondary"}`}>{t.user?.status || "—"}</span></td>
              </tr>
            ))}
            {teachers.length === 0 && <tr><td colSpan={5} className="text-center text-muted py-4">No teachers yet.</td></tr>}
          </tbody>
        </table>
      </div></div>
    </>
  );
}
