"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import Link from "next/link";

interface Announcement {
  id: number;
  title: string;
  message?: string;
  priority: string;
  publish_date?: string;
  expiration_date?: string;
  school_class?: { id: number; class_name: string };
  teacher?: { user?: { full_name?: string; name?: string } };
}

export default function AnnouncementsPage() {
  const [announcements, setAnnouncements] = useState<Announcement[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<Announcement[]>("/api/announcements")
      .then(setAnnouncements)
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading announcements...</p></div>;
  if (error) return <div className="sf-retry-banner">{error}</div>;

  const badge = (p: string) => {
    const map: Record<string, string> = { Urgent: "danger", Important: "warning", Normal: "secondary" };
    return <span className={`badge bg-${map[p] || "secondary"}`}>{p}</span>;
  };

  return (
    <>
      <div className="page-header">
        <div>
          <h2>Announcements</h2>
          <p>Manage announcements</p>
        </div>
        <Link href="/announcements/create" className="btn btn-primary">New Announcement</Link>
      </div>
      <div className="card stat-card">
        <div className="card-body p-0">
          <table className="table table-hover mb-0">
            <thead className="table-light">
              <tr><th>Title</th><th>Class</th><th>Posted By</th><th>Posted</th><th>Priority</th><th></th></tr>
            </thead>
            <tbody>
              {announcements.map((a) => (
                <tr key={a.id}>
                  <td><strong>{a.title}</strong></td>
                  <td>{a.school_class?.class_name ? a.school_class.class_name : <span className="badge bg-info">All Classes</span>}</td>
                  <td>{a.teacher?.user?.full_name || a.teacher?.user?.name || "—"}</td>
                  <td><small>{a.publish_date ? new Date(a.publish_date).toLocaleDateString() : "—"}</small></td>
                  <td>{badge(a.priority)}</td>
                  <td><Link href={`/announcements/${a.id}`} className="btn btn-sm btn-outline-primary">Read</Link></td>
                </tr>
              ))}
              {announcements.length === 0 && <tr><td colSpan={6} className="text-center text-muted py-4">No announcements yet.</td></tr>}
            </tbody>
          </table>
        </div>
      </div>
    </>
  );
}
