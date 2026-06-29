"use client";

import { useEffect, useState, use } from "react";
import { api } from "@/lib/api";
import Link from "next/link";
import { useAuth } from "@/lib/auth";

interface Student {
  id: number;
  student_number: string;
  full_name?: string;
  first_name: string;
  last_name: string;
  email: string;
  gender?: string;
  birth_date?: string;
  contact_number?: string;
  address?: string;
  guardian_name?: string;
  guardian_contact?: string;
  status: string;
  classes?: { id: number; class_name: string }[];
  user?: { isClassroomVerified?: boolean; google_id?: string; github_id?: string };
}

export default function StudentDetailPage(props: { params: Promise<{ id: string }> }) {
  const { id } = use(props.params);
  const { user } = useAuth();
  const [s, setS] = useState<Student | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<Student>(`/api/students/${id}`)
      .then(setS)
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, [id]);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading student...</p></div>;
  if (error || !s) return <div className="sf-retry-banner">{error || "Student not found."}</div>;

  const isAdmin = user?.role === "admin";

  return (
    <>
      <div className="page-header">
        <div>
          <h2>{s.full_name || `${s.first_name} ${s.last_name}`}</h2>
          <p className="text-muted">{s.student_number} &middot; {s.email}</p>
        </div>
        {isAdmin && <Link href={`/students/${s.id}/edit`} className="btn btn-outline-secondary">Edit</Link>}
      </div>

      {s.user?.isClassroomVerified && <div className="alert alert-info d-flex justify-content-between align-items-center">
        <span><strong>Verified student</strong><br /><small>Google: {s.user.google_id ? "linked" : "not linked"} &middot; GitHub: {s.user.github_id ? "linked" : "not linked"}</small></span>
      </div>}

      <div className="row g-3 mb-3">
        <div className="col-md-3"><div className="card stat-card"><div className="card-body">
          <div className="stat-label">Gender</div><div>{s.gender || "—"}</div>
        </div></div></div>
        <div className="col-md-3"><div className="card stat-card"><div className="card-body">
          <div className="stat-label">Birth Date</div><div>{s.birth_date ? new Date(s.birth_date).toLocaleDateString() : "—"}</div>
        </div></div></div>
        <div className="col-md-3"><div className="card stat-card"><div className="card-body">
          <div className="stat-label">Contact</div><div>{s.contact_number || "—"}</div>
        </div></div></div>
        <div className="col-md-3"><div className="card stat-card"><div className="card-body">
          <div className="stat-label">Status</div>
          <span className={`badge bg-${s.status === "active" ? "success" : "secondary"}`}>{s.status}</span>
        </div></div></div>
      </div>

      <div className="row g-3">
        <div className="col-md-6"><div className="card stat-card"><div className="card-body">
          <div className="stat-label">Address</div><div>{s.address || "—"}</div>
        </div></div></div>
        <div className="col-md-6"><div className="card stat-card"><div className="card-body">
          <div className="stat-label">Guardian</div><div>{s.guardian_name || "—"} ({s.guardian_contact || "—"})</div>
        </div></div></div>
      </div>

      <div className="card stat-card mt-3"><div className="card-body">
        <h5 className="card-title">Enrolled Classes ({s.classes?.length || 0})</h5>
        {(!s.classes || s.classes.length === 0) && <p className="text-muted mb-0">No classes.</p>}
        {s.classes?.map((c) => (
          <div key={c.id} className="mb-1">
            <Link href={`/classes/${c.id}`}>{c.class_name}</Link>
          </div>
        ))}
      </div></div>
    </>
  );
}
