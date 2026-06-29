"use client";

import { useEffect, useState, use } from "react";
import { api } from "@/lib/api";
import Link from "next/link";

interface Assignment {
  id: number;
  title: string;
  description?: string;
  deadline?: string;
  date_assigned?: string;
  maximum_score: number;
  status: string;
  attachment_link?: string;
  school_class?: { id: number; class_name: string };
  submissions?: { id: number; student_id: number; status: string; score?: number; submitted_at?: string; student?: { full_name?: string } }[];
}

export default function AssignmentDetailPage(props: { params: Promise<{ id: string }> }) {
  const { id } = use(props.params);
  const [a, setA] = useState<Assignment | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<Assignment>(`/api/assignments/${id}`)
      .then(setA)
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, [id]);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading assignment...</p></div>;
  if (error || !a) return <div className="sf-retry-banner">{error || "Not found."}</div>;

  return (
    <>
      <div className="mb-3"><Link href="/assignments" className="text-decoration-none">&larr; Back to Assignments</Link></div>
      <div className="page-header">
        <div>
          <h2>{a.title}</h2>
          <p className="text-muted">{a.school_class?.class_name} &middot; Max score: {a.maximum_score}</p>
        </div>
      </div>

      <div className="card stat-card mb-4"><div className="card-body">
        <h5 className="card-title">Description</h5>
        <p>{a.description || "—"}</p>
        <dl className="row mb-0">
          <dt className="col-sm-3">Assigned</dt><dd className="col-sm-9">{a.date_assigned ? new Date(a.date_assigned).toLocaleDateString() : "—"}</dd>
          <dt className="col-sm-3">Deadline</dt><dd className="col-sm-9">{a.deadline ? new Date(a.deadline).toLocaleDateString() : "—"}</dd>
          <dt className="col-sm-3">Status</dt><dd className="col-sm-9"><span className={`badge bg-${a.status === "published" ? "success" : "secondary"}`}>{a.status}</span></dd>
          {a.attachment_link && <><dt className="col-sm-3">Link</dt><dd className="col-sm-9"><a href={a.attachment_link} target="_blank" rel="noopener">{a.attachment_link}</a></dd></>}
        </dl>
      </div></div>

      <div className="card stat-card"><div className="card-body">
        <h5 className="card-title">Submissions ({a.submissions?.length || 0})</h5>
        {(!a.submissions || a.submissions.length === 0) && <p className="text-muted mb-0">No submissions yet.</p>}
        {a.submissions?.map((s) => (
          <div key={s.id} className="d-flex justify-content-between align-items-center mb-2">
            <span><strong>{s.student?.full_name || "Student #" + s.student_id}</strong></span>
            <span>
              <span className={`badge bg-${s.status === "Submitted" ? "success" : s.status === "Late" ? "warning" : "secondary"} me-2`}>{s.status}</span>
              {s.score !== null && <span className="badge bg-primary">{s.score}</span>}
            </span>
          </div>
        ))}
      </div></div>
    </>
  );
}
