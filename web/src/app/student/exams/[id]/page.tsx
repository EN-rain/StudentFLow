"use client";

import { useEffect, useState, use } from "react";
import { api } from "@/lib/api";
import Link from "next/link";

interface ExamAttemptData {
  id: number;
  status: string;
  score?: number;
  started_at?: string;
  submitted_at?: string;
  exam: {
    id: number;
    title: string;
    instructions?: string;
    available_from?: string;
    due_at?: string;
    duration_minutes?: number;
    maximum_score: number;
    school_class?: { class_name: string };
    questions?: { id: number; prompt: string; type: string; points: number }[];
  };
}

export default function StudentExamDetailPage(props: { params: Promise<{ id: string }> }) {
  const { id } = use(props.params);
  const [attempt, setAttempt] = useState<ExamAttemptData | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<{ data: ExamAttemptData }>(`/api/student/exams/${id}`)
      .then((res) => setAttempt(res.data))
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, [id]);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading exam...</p></div>;
  if (error || !attempt) return <div className="sf-retry-banner">{error || "Not found."}</div>;

  const e = attempt.exam;
  const badgeColor = attempt.status === "submitted" ? "success" : attempt.status === "in_progress" ? "warning" : "info";

  return (
    <>
      <div className="mb-3"><Link href="/student/exams" className="text-decoration-none">&larr; Back to Exams</Link></div>
      <div className="page-header">
        <div>
          <h2>{e.title}</h2>
          <p className="text-muted">{e.school_class?.class_name} &middot; Max score: {e.maximum_score}</p>
        </div>
        <span className={`badge fs-6 bg-${badgeColor}`}>{attempt.status}</span>
      </div>

      {attempt.status === "submitted" && attempt.score !== null && attempt.score !== undefined && (
        <div className="alert alert-info">Your score: <strong>{attempt.score}</strong> / {e.maximum_score}</div>
      )}

      <div className="card stat-card mb-4"><div className="card-body">
        <h5 className="card-title">Instructions</h5>
        <p>{e.instructions || "—"}</p>
        <dl className="row mb-0">
          <dt className="col-sm-3">Duration</dt><dd className="col-sm-9">{e.duration_minutes ? `${e.duration_minutes} min` : "—"}</dd>
          <dt className="col-sm-3">Due</dt><dd className="col-sm-9">{e.due_at ? new Date(e.due_at).toLocaleString() : "—"}</dd>
          {attempt.started_at && <><dt className="col-sm-3">Started</dt><dd className="col-sm-9">{new Date(attempt.started_at).toLocaleString()}</dd></>}
          {attempt.submitted_at && <><dt className="col-sm-3">Submitted</dt><dd className="col-sm-9">{new Date(attempt.submitted_at).toLocaleString()}</dd></>}
        </dl>
      </div></div>

      {e.questions && e.questions.length > 0 && (
        <div className="card stat-card"><div className="card-body">
          <h5 className="card-title">Questions ({e.questions.length})</h5>
          {e.questions.map((q, i) => (
            <div key={q.id} className="mb-2">
              <strong>{i + 1}.</strong> {q.prompt}
              <span className="text-muted ms-2">({q.points} pts, {q.type})</span>
            </div>
          ))}
        </div></div>
      )}
    </>
  );
}
