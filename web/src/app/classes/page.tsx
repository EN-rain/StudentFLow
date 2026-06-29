"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import Link from "next/link";

interface SchoolClass {
  id: number;
  class_name: string;
  subject: string;
  section: string;
  grade_level: string;
  schedule?: string;
  room?: string;
  status: string;
  teacher?: { user?: { name?: string } };
}

export default function ClassesPage() {
  const [classes, setClasses] = useState<SchoolClass[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<SchoolClass[]>("/api/classes")
      .then(setClasses)
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading classes...</p></div>;
  if (error) return <div className="sf-retry-banner">{error}</div>;

  return (
    <>
      <div className="page-header">
        <div>
          <h2>Classes</h2>
          <p>Manage school classes</p>
        </div>
        <Link href="/classes/create" className="btn btn-primary">New Class</Link>
      </div>
      <div className="card stat-card">
        <div className="card-body p-0">
          <table className="table table-hover mb-0">
            <thead className="table-light">
              <tr><th>Class</th><th>Subject</th><th>Schedule</th><th>Room</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
              {classes.map((c) => (
                <tr key={c.id}>
                  <td><strong>{c.class_name}</strong><br /><small className="text-muted">{c.grade_level} &middot; {c.section}</small></td>
                  <td>{c.subject}</td>
                  <td><small>{c.schedule || "—"}</small></td>
                  <td>{c.room || "—"}</td>
                  <td><span className={`badge bg-${c.status === "active" ? "success" : "secondary"}`}>{c.status}</span></td>
                  <td><Link href={`/classes/${c.id}`} className="btn btn-sm btn-outline-primary">View</Link></td>
                </tr>
              ))}
              {classes.length === 0 && <tr><td colSpan={6} className="text-center text-muted py-4">No classes yet.</td></tr>}
            </tbody>
          </table>
        </div>
      </div>
    </>
  );
}
