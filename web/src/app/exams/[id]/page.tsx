"use client";

import { useEffect, useState, use } from "react";
import { api } from "@/lib/api";
import Link from "next/link";

interface ExamDetail {
  id: number;
  title: string;
  instructions?: string;
  available_from?: string;
  due_at?: string;
  duration_minutes?: number;
  maximum_score: number;
  status: string;
  school_class?: { id: number; class_name: string };
  questions?: { id: number; prompt: string; type: string; points: number }[];
}

export default function ExamDetailPage(props: { params: Promise<{ id: string }> }) {
  const { id } = use(props.params);
  const [e, setE] = useState<ExamDetail | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<ExamDetail>(`/api/exams/${id}`)
      .then(setE)
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, [id]);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading exam...</p></div>;
  if (error || !e) return <div className="sf-retry-banner">{error || "Not found."}</div>;

  return (
    <>
      <div className="mb-3"><Link href="/exams" className="text-decoration-none">&larr; Back to Exams</Link></div>
      <div className="page-header">
        <div>
          <h2>{e.title}</h2>
          <p className="text-muted">{e.school_class?.class_name} &middot; Max score: {e.maximum_score}</p>
        </div>
      </div>

      <div className="card stat-card mb-4"><div className="card-body">
        <h5 className="card-title">Exam Details</h5>
        <dl className="row mb-0">
          <dt className="col-sm-3">Instructions</dt><dd className="col-sm-9">{e.instructions || "—"}</dd>
          <dt className="col-sm-3">Available</dt><dd className="col-sm-9">{e.available_from ? new Date(e.available_from).toLocaleString() : "—"}</dd>
          <dt className="col-sm-3">Due</dt><dd className="col-sm-9">{e.due_at ? new Date(e.due_at).toLocaleString() : "—"}</dd>
          <dt className="col-sm-3">Duration</dt><dd className="col-sm-9">{e.duration_minutes ? `${e.duration_minutes} min` : "—"}</dd>
          <dt className="col-sm-3">Status</dt><dd className="col-sm-9"><span className={`badge bg-${e.status === "published" ? "success" : e.status === "closed" ? "danger" : "secondary"}`}>{e.status}</span></dd>
        </dl>
      </div></div>

      <div className="card stat-card"><div className="card-body">
        <h5 className="card-title">Questions ({e.questions?.length || 0})</h5>
        {(!e.questions || e.questions.length === 0) && <p className="text-muted mb-0">No questions yet.</p>}
        {e.questions?.map((q, i) => (
          <div key={q.id} className="mb-2">
            <strong>{i + 1}.</strong> {q.prompt}
            <span className="text-muted ms-2">({q.points} pts, {q.type})</span>
          </div>
        ))}
      </div></div>
    </>
  );
}
