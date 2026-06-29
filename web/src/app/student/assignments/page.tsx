"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import Link from "next/link";

interface AssignmentData {
  id: number;
  title: string;
  deadline?: string;
  school_class?: { class_name: string };
  submissions?: { status: string }[];
}

export default function StudentAssignmentsPage() {
  const [assignments, setAssignments] = useState<AssignmentData[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<{ data: AssignmentData[] }>("/api/student/assignments")
      .then((res) => setAssignments(res.data))
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading assignments...</p></div>;
  if (error) return <div className="sf-retry-banner">{error}</div>;

  return (
    <>
      <div className="page-header"><h2>My Assignments</h2></div>
      <div className="card stat-card"><div className="card-body p-0">
        <table className="table table-hover mb-0">
          <thead className="table-light"><tr><th>Assignment</th><th>Class</th><th>Deadline</th><th>Status</th><th></th></tr></thead>
          <tbody>
            {assignments.map((a) => {
              const sub = a.submissions?.[0];
              const badgeColor = sub?.status === "Submitted" ? "success" : sub?.status === "Late" ? "warning" : "secondary";
              return (
                <tr key={a.id}>
                  <td><strong>{a.title}</strong></td>
                  <td>{a.school_class?.class_name || "—"}</td>
                  <td><small>{a.deadline ? new Date(a.deadline).toLocaleDateString() : "—"}</small></td>
                  <td><span className={`badge bg-${badgeColor}`}>{sub?.status || "Not submitted"}</span></td>
                  <td><Link href={`/student/assignments/${a.id}`} className="btn btn-sm btn-outline-primary">View</Link></td>
                </tr>
              );
            })}
            {assignments.length === 0 && <tr><td colSpan={5} className="text-center text-muted py-4">No assignments.</td></tr>}
          </tbody>
        </table>
      </div></div>
    </>
  );
}
