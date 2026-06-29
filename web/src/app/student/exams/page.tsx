"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import Link from "next/link";

interface ExamData {
  id: number;
  title: string;
  available_from?: string;
  due_at?: string;
  duration_minutes?: number;
  maximum_score: number;
  status: string;
  school_class?: { class_name: string };
  attempts?: { status: string; score?: number }[];
}

export default function StudentExamsPage() {
  const [exams, setExams] = useState<ExamData[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<{ data: ExamData[] }>("/api/student/exams")
      .then((res) => setExams(res.data))
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading exams...</p></div>;
  if (error) return <div className="sf-retry-banner">{error}</div>;

  return (
    <>
      <div className="page-header"><h2>My Exams</h2></div>
      <div className="card stat-card"><div className="card-body p-0">
        <table className="table table-hover mb-0">
          <thead className="table-light"><tr><th>Exam</th><th>Class</th><th>Due</th><th>Status</th><th></th></tr></thead>
          <tbody>
            {exams.map((e) => {
              const attempt = e.attempts?.[0];
              const badgeColor = attempt?.status === "submitted" ? "success" : attempt?.status === "in_progress" ? "warning" : "info";
              return (
                <tr key={e.id}>
                  <td><strong>{e.title}</strong></td>
                  <td>{e.school_class?.class_name || "—"}</td>
                  <td><small>{e.due_at ? new Date(e.due_at).toLocaleString() : "—"}</small></td>
                  <td><span className={`badge bg-${badgeColor}`}>{attempt?.status || "Not started"}</span></td>
                  <td><Link href={`/student/exams/${e.id}`} className="btn btn-sm btn-outline-primary">View</Link></td>
                </tr>
              );
            })}
            {exams.length === 0 && <tr><td colSpan={5} className="text-center text-muted py-4">No exams.</td></tr>}
          </tbody>
        </table>
      </div></div>
    </>
  );
}
