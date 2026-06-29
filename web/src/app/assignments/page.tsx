"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import Link from "next/link";

interface Assignment {
  id: number;
  title: string;
  description?: string;
  deadline?: string;
  maximum_score: number;
  status: string;
  school_class?: { id: number; class_name: string };
}

export default function AssignmentsPage() {
  const [assignments, setAssignments] = useState<Assignment[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<Assignment[]>("/api/assignments")
      .then(setAssignments)
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading assignments...</p></div>;
  if (error) return <div className="sf-retry-banner">{error}</div>;

  return (
    <>
      <div className="page-header">
        <div>
          <h2>Assignments</h2>
          <p>Manage assignments</p>
        </div>
        <Link href="/assignments/create" className="btn btn-primary">New Assignment</Link>
      </div>
      <div className="card stat-card">
        <div className="card-body p-0">
          <table className="table table-hover mb-0">
            <thead className="table-light">
              <tr><th>Title</th><th>Class</th><th>Deadline</th><th>Max Score</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
              {assignments.map((a) => (
                <tr key={a.id}>
                  <td><strong>{a.title}</strong></td>
                  <td>{a.school_class?.class_name || "—"}</td>
                  <td><small>{a.deadline ? new Date(a.deadline).toLocaleDateString() : "—"}</small></td>
                  <td>{a.maximum_score}</td>
                  <td><span className={`badge bg-${a.status === "published" ? "success" : "secondary"}`}>{a.status}</span></td>
                  <td><Link href={`/assignments/${a.id}`} className="btn btn-sm btn-outline-primary">View</Link></td>
                </tr>
              ))}
              {assignments.length === 0 && <tr><td colSpan={6} className="text-center text-muted py-4">No assignments yet.</td></tr>}
            </tbody>
          </table>
        </div>
      </div>
    </>
  );
}
