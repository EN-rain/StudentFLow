"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import Link from "next/link";

interface Exam {
  id: number;
  title: string;
  available_from?: string;
  due_at?: string;
  duration_minutes?: number;
  maximum_score: number;
  status: string;
  school_class?: { id: number; class_name: string };
}

export default function ExamsPage() {
  const [exams, setExams] = useState<Exam[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<Exam[]>("/api/exams")
      .then(setExams)
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading exams...</p></div>;
  if (error) return <div className="sf-retry-banner">{error}</div>;

  return (
    <>
      <div className="page-header">
        <div>
          <h2>Exams</h2>
          <p>Manage exams</p>
        </div>
        <Link href="/exams/create" className="btn btn-primary">New Exam</Link>
      </div>
      <div className="card stat-card">
        <div className="card-body p-0">
          <table className="table table-hover mb-0">
            <thead className="table-light">
              <tr><th>Exam</th><th>Class</th><th>Available</th><th>Due</th><th>Duration</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
              {exams.map((e) => (
                <tr key={e.id}>
                  <td><strong>{e.title}</strong></td>
                  <td>{e.school_class?.class_name || "—"}</td>
                  <td><small>{e.available_from ? new Date(e.available_from).toLocaleString() : "—"}</small></td>
                  <td><small>{e.due_at ? new Date(e.due_at).toLocaleString() : "—"}</small></td>
                  <td>{e.duration_minutes ? `${e.duration_minutes} min` : "—"}</td>
                  <td><span className={`badge bg-${e.status === "published" ? "success" : e.status === "closed" ? "danger" : "secondary"}`}>{e.status}</span></td>
                  <td><Link href={`/exams/${e.id}`} className="btn btn-sm btn-outline-primary">View</Link></td>
                </tr>
              ))}
              {exams.length === 0 && <tr><td colSpan={7} className="text-center text-muted py-4">No exams yet.</td></tr>}
            </tbody>
          </table>
        </div>
      </div>
    </>
  );
}
