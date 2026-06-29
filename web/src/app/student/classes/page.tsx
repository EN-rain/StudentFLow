"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import Link from "next/link";
import { useAuth } from "@/lib/auth";

interface EnrolledClass {
  id: number;
  class_name: string;
  subject: string;
  grade_level: string;
  school_year: string;
  semester: string;
  schedule?: string;
  room?: string;
  teacher?: { user?: { name?: string } };
}

export default function StudentClassesPage() {
  const { user } = useAuth();
  const [classes, setClasses] = useState<EnrolledClass[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<{ data: EnrolledClass[] }>("/api/student/classes")
      .then((res) => setClasses(res.data))
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading classes...</p></div>;
  if (error) return <div className="sf-retry-banner">{error}</div>;

  return (
    <>
      <div className="page-header">
        <h2>My Classes</h2>
      </div>
      <div className="card stat-card mb-4"><div className="card-body">
        <p className="mb-0 text-muted">Showing {classes.length} enrolled class{classes.length === 1 ? "" : "es"}.</p>
      </div></div>
      <div className="card stat-card"><div className="card-body p-0">
        <table className="table table-hover mb-0">
          <thead className="table-light">
            <tr><th>Class</th><th>Subject</th><th>Teacher</th><th>Schedule</th><th></th></tr>
          </thead>
          <tbody>
            {classes.map((c) => (
              <tr key={c.id}>
                <td><strong>{c.class_name}</strong><br /><small className="text-muted">{c.grade_level} &middot; {c.semester}</small></td>
                <td>{c.subject}</td>
                <td>{c.teacher?.user?.name || "—"}</td>
                <td><small>{c.schedule || "—"}</small></td>
                <td><Link href={`/student/classes/${c.id}`} className="btn btn-sm btn-outline-primary">View</Link></td>
              </tr>
            ))}
            {classes.length === 0 && <tr><td colSpan={5} className="text-center text-muted py-4">No enrolled classes.</td></tr>}
          </tbody>
        </table>
      </div></div>
    </>
  );
}
