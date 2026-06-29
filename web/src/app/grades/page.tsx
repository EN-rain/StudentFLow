"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import Link from "next/link";

interface GradeClass {
  id: number;
  class_name: string;
  subject: string;
  teacher?: { user?: { full_name?: string; name?: string } };
  grade_categories?: { id: number; items?: { id: number }[] }[];
}

export default function GradesPage() {
  const [classes, setClasses] = useState<GradeClass[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<GradeClass[]>("/api/classes")
      .then(setClasses)
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading grades...</p></div>;
  if (error) return <div className="sf-retry-banner">{error}</div>;

  return (
    <>
      <div className="page-header">
        <div>
          <h2>Grades</h2>
          <p>Manage grades per class</p>
        </div>
      </div>
      <div className="card stat-card">
        <div className="card-body p-0">
          <table className="table table-hover mb-0">
            <thead className="table-light">
              <tr><th>Class</th><th>Subject</th><th>Teacher</th><th>Categories</th><th></th></tr>
            </thead>
            <tbody>
              {classes.map((c) => (
                <tr key={c.id}>
                  <td><strong>{c.class_name}</strong></td>
                  <td>{c.subject}</td>
                  <td>{c.teacher?.user?.full_name || c.teacher?.user?.name || "—"}</td>
                  <td>{c.grade_categories?.length || 0}</td>
                  <td><Link href={`/grades/${c.id}`} className="btn btn-sm btn-outline-primary">Manage</Link></td>
                </tr>
              ))}
              {classes.length === 0 && <tr><td colSpan={5} className="text-center text-muted py-4">No classes yet.</td></tr>}
            </tbody>
          </table>
        </div>
      </div>
    </>
  );
}
