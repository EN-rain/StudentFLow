"use client";

import { useEffect, useState, use } from "react";
import { api } from "@/lib/api";
import Link from "next/link";

interface SchoolClass {
  id: number;
  class_name: string;
  subject: string;
  section: string;
  grade_level: string;
  school_year: string;
  semester: string;
  schedule?: string;
  room?: string;
  status: string;
  join_code?: string;
  teacher?: { user?: { name?: string }; full_name?: string };
  students?: { id: number; full_name?: string; student_number?: string; pivot?: { status?: string } }[];
}

export default function ClassDetailPage(props: { params: Promise<{ id: string }> }) {
  const { id } = use(props.params);
  const [c, setC] = useState<SchoolClass | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<SchoolClass>(`/api/classes/${id}`)
      .then(setC)
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, [id]);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading class...</p></div>;
  if (error || !c) return <div className="sf-retry-banner">{error || "Class not found."}</div>;

  return (
    <>
      <div className="mb-3"><Link href="/classes" className="text-decoration-none">&larr; Back to Classes</Link></div>
      <div className="page-header">
        <div>
          <h2>{c.class_name}</h2>
          <p className="text-muted">{c.subject} &middot; {c.grade_level} &middot; {c.section}</p>
        </div>
        <Link href={`/classes/${id}/edit`} className="btn btn-outline-primary">Edit</Link>
      </div>

      <div className="row g-3 mb-4">
        <div className="col-md-6">
          <div className="card stat-card"><div className="card-body">
            <h5 className="card-title">Class Information</h5>
            <dl className="row mb-0">
              <dt className="col-sm-4">Subject</dt><dd className="col-sm-8">{c.subject}</dd>
              <dt className="col-sm-4">Schedule</dt><dd className="col-sm-8">{c.schedule || "—"}</dd>
              <dt className="col-sm-4">Room</dt><dd className="col-sm-8">{c.room || "—"}</dd>
              <dt className="col-sm-4">Teacher</dt><dd className="col-sm-8">{c.teacher?.user?.name || c.teacher?.full_name || "—"}</dd>
              <dt className="col-sm-4">Join Code</dt><dd className="col-sm-8"><code>{c.join_code || "—"}</code></dd>
              <dt className="col-sm-4">Status</dt><dd className="col-sm-8"><span className={`badge bg-${c.status === "active" ? "success" : "secondary"}`}>{c.status}</span></dd>
            </dl>
          </div></div>
        </div>
        <div className="col-md-6">
          <div className="card stat-card"><div className="card-body">
            <h5 className="card-title">Enrolled Students ({c.students?.length || 0})</h5>
            {(!c.students || c.students.length === 0) && <p className="text-muted mb-0">No students enrolled.</p>}
            {c.students?.map((s) => (
              <div key={s.id} className="mb-1 small d-flex justify-content-between">
                <span><Link href={`/students/${s.id}`}>{s.full_name || "Unknown"}</Link></span>
                <span className="text-muted">{s.student_number} <span className={`badge bg-${s.pivot?.status === "enrolled" ? "success" : "secondary"}`}>{s.pivot?.status}</span></span>
              </div>
            ))}
          </div></div>
        </div>
      </div>
    </>
  );
}
