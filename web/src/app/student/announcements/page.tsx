"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import Link from "next/link";

export default function StudentAnnouncementsPage() {
  const [announcements, setAnnouncements] = useState<{ id: number; title: string; priority: string; publish_date?: string; school_class?: { class_name: string }; teacher?: { user?: { name?: string } } }[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<{ data: unknown[] }>("/api/student/announcements")
      .then((res) => setAnnouncements(res.data as typeof announcements))
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading announcements...</p></div>;
  if (error) return <div className="sf-retry-banner">{error}</div>;

  return (
    <>
      <div className="page-header"><h2>My Announcements</h2></div>
      <div className="card stat-card"><div className="card-body p-0">
        <table className="table table-hover mb-0">
          <thead className="table-light"><tr><th>Title</th><th>Class</th><th>Posted</th><th>Priority</th><th></th></tr></thead>
          <tbody>
            {announcements.map((a) => (
              <tr key={a.id}>
                <td><strong>{a.title}</strong></td>
                <td>{a.school_class?.class_name ? a.school_class.class_name : <span className="badge bg-info">All</span>}</td>
                <td><small>{a.publish_date ? new Date(a.publish_date).toLocaleDateString() : "—"}</small></td>
                <td><span className={`badge bg-${a.priority === "Urgent" ? "danger" : a.priority === "Important" ? "warning" : "secondary"}`}>{a.priority}</span></td>
                <td><Link href={`/student/announcements/${a.id}`} className="btn btn-sm btn-outline-primary">Read</Link></td>
              </tr>
            ))}
            {announcements.length === 0 && <tr><td colSpan={5} className="text-center text-muted py-4">No announcements.</td></tr>}
          </tbody>
        </table>
      </div></div>
    </>
  );
}
