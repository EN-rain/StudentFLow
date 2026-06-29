"use client";

import { useEffect, useState, use } from "react";
import { api } from "@/lib/api";
import Link from "next/link";

interface Submission {
  id: number;
  status: string;
  score?: number;
  submitted_at?: string;
  attachment_link?: string;
  remarks?: string;
}

interface AssignmentDetail {
  id: number;
  title: string;
  description?: string;
  deadline?: string;
  date_assigned?: string;
  maximum_score: number;
  status: string;
  attachment_link?: string;
  school_class?: { id: number; class_name: string };
  submissions?: Submission[];
}

export default function StudentAssignmentDetailPage(props: { params: Promise<{ id: string }> }) {
  const { id } = use(props.params);
  const [a, setA] = useState<AssignmentDetail | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<{ data: AssignmentDetail }>(`/api/student/assignments/${id}`)
      .then((res) => setA(res.data))
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, [id]);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading assignment...</p></div>;
  if (error || !a) return <div className="sf-retry-banner">{error || "Not found."}</div>;

  const sub = a.submissions?.[0];
  const badgeColor = sub?.status === "Submitted" ? "success" : sub?.status === "Late" ? "warning" : "secondary";

  return (
    <>
      <div className="mb-3"><Link href="/student/assignments" className="text-decoration-none">&larr; Back to Assignments</Link></div>
      <div className="page-header">
        <div>
          <h2>{a.title}</h2>
          <p className="text-muted">{a.school_class?.class_name} &middot; Max score: {a.maximum_score}</p>
        </div>
        <span className={`badge fs-6 bg-${badgeColor}`}>{sub?.status || "Not submitted"}</span>
      </div>

      <div className="card stat-card mb-4"><div className="card-body">
        <h5 className="card-title">Description</h5>
        <p>{a.description || "—"}</p>
        <dl className="row mb-0">
          <dt className="col-sm-3">Assigned</dt><dd className="col-sm-9">{a.date_assigned ? new Date(a.date_assigned).toLocaleDateString() : "—"}</dd>
          <dt className="col-sm-3">Deadline</dt><dd className="col-sm-9">{a.deadline ? new Date(a.deadline).toLocaleDateString() : "—"}</dd>
          {a.attachment_link && <><dt className="col-sm-3">Link</dt><dd className="col-sm-9"><a href={a.attachment_link} target="_blank" rel="noopener noreferrer">{a.attachment_link}</a></dd></>}
        </dl>
      </div></div>

      {sub && (
        <div className="card stat-card"><div className="card-body">
          <h5 className="card-title">Your Submission</h5>
          <dl className="row mb-0">
            <dt className="col-sm-3">Status</dt><dd className="col-sm-9"><span className={`badge bg-${badgeColor}`}>{sub.status}</span></dd>
            {sub.score !== null && sub.score !== undefined && <><dt className="col-sm-3">Score</dt><dd className="col-sm-9">{sub.score} / {a.maximum_score}</dd></>}
            {sub.submitted_at && <><dt className="col-sm-3">Submitted</dt><dd className="col-sm-9">{new Date(sub.submitted_at).toLocaleString()}</dd></>}
            {sub.remarks && <><dt className="col-sm-3">Remarks</dt><dd className="col-sm-9">{sub.remarks}</dd></>}
          </dl>
        </div></div>
      )}
    </>
  );
}
