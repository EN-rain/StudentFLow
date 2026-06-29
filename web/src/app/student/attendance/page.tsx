"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";

export default function StudentAttendancePage() {
  const [records, setRecords] = useState<{ id: number; attendance_date: string; status: string; remarks?: string; school_class?: { class_name: string } }[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<{ data: unknown[] }>("/api/student/attendance")
      .then((res) => setRecords(res.data as typeof records))
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading attendance...</p></div>;
  if (error) return <div className="sf-retry-banner">{error}</div>;

  return (
    <>
      <div className="page-header"><h2>My Attendance</h2></div>
      <div className="card stat-card"><div className="card-body p-0">
        <table className="table table-hover mb-0">
          <thead className="table-light"><tr><th>Class</th><th>Date</th><th>Status</th><th>Remarks</th></tr></thead>
          <tbody>
            {records.map((r) => (
              <tr key={r.id}>
                <td>{r.school_class?.class_name || "—"}</td>
                <td><small>{new Date(r.attendance_date).toLocaleDateString()}</small></td>
                <td><span className={`badge bg-${r.status === "Present" ? "success" : r.status === "Late" ? "warning" : r.status === "Absent" ? "danger" : "info"}`}>{r.status}</span></td>
                <td><small className="text-muted">{r.remarks || "—"}</small></td>
              </tr>
            ))}
            {records.length === 0 && <tr><td colSpan={4} className="text-center text-muted py-4">No records.</td></tr>}
          </tbody>
        </table>
      </div></div>
    </>
  );
}
