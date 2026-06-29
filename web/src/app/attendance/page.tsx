"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import Link from "next/link";

interface AttendanceRecord {
  id: number;
  school_class?: { id: number; class_name: string };
  attendance_date: string;
  status: string;
  remarks?: string;
}

export default function AttendancePage() {
  const [records, setRecords] = useState<AttendanceRecord[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<AttendanceRecord[]>("/api/attendance")
      .then(setRecords)
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading attendance...</p></div>;
  if (error) return <div className="sf-retry-banner">{error}</div>;

  const badge = (status: string) => {
    const map: Record<string, string> = { Present: "success", Late: "warning", Absent: "danger", Excused: "info" };
    return <span className={`badge bg-${map[status] || "secondary"}`}>{status}</span>;
  };

  return (
    <>
      <div className="page-header">
        <div>
          <h2>Attendance</h2>
          <p>Track and manage student attendance</p>
        </div>
      </div>
      <div className="card stat-card">
        <div className="card-body p-0">
          <table className="table table-hover mb-0">
            <thead className="table-light">
              <tr><th>Class</th><th>Date</th><th>Status</th><th>Remarks</th></tr>
            </thead>
            <tbody>
              {records.map((r) => (
                <tr key={r.id}>
                  <td><Link href={`/classes/${r.school_class?.id}`}>{r.school_class?.class_name || "—"}</Link></td>
                  <td><small>{new Date(r.attendance_date).toLocaleDateString()}</small></td>
                  <td>{badge(r.status)}</td>
                  <td><small className="text-muted">{r.remarks || "—"}</small></td>
                </tr>
              ))}
              {records.length === 0 && <tr><td colSpan={4} className="text-center text-muted py-4">No attendance records yet.</td></tr>}
            </tbody>
          </table>
        </div>
      </div>
    </>
  );
}
