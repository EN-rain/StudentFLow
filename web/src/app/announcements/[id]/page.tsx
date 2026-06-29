"use client";

import { useEffect, useState, use } from "react";
import { api } from "@/lib/api";
import Link from "next/link";

interface Announcement {
  id: number;
  title: string;
  message: string;
  priority: string;
  publish_date?: string;
  expiration_date?: string;
  school_class?: { id: number; class_name: string };
  teacher?: { user?: { full_name?: string; name?: string } };
}

export default function AnnouncementDetailPage(props: { params: Promise<{ id: string }> }) {
  const { id } = use(props.params);
  const [a, setA] = useState<Announcement | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<Announcement>(`/api/announcements/${id}`)
      .then(setA)
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, [id]);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading announcement...</p></div>;
  if (error || !a) return <div className="sf-retry-banner">{error || "Not found."}</div>;

  const badge = (p: string) => {
    const map: Record<string, string> = { Urgent: "danger", Important: "warning", Normal: "secondary" };
    return <span className={`badge bg-${map[p] || "secondary"} fs-6`}>{p}</span>;
  };

  return (
    <>
      <div className="mb-3"><Link href="/announcements" className="text-decoration-none">&larr; Back to Announcements</Link></div>
      <div className="card stat-card"><div className="card-body">
        <div className="d-flex justify-content-between align-items-start mb-3">
          <h2 className="mb-0">{a.title}</h2>
          {badge(a.priority)}
        </div>
        <p className="text-muted mb-3">
          {a.school_class ? a.school_class.class_name : <span className="badge bg-info">All Classes</span>}
          &middot; {a.teacher?.user?.full_name || a.teacher?.user?.name || "—"}
          &middot; Posted {a.publish_date ? new Date(a.publish_date).toLocaleDateString() : "—"}
          {a.expiration_date && <> &middot; Expires {new Date(a.expiration_date).toLocaleDateString()}</>}
        </p>
        <hr />
        <div style={{ whiteSpace: "pre-wrap" }}>{a.message}</div>
      </div></div>
    </>
  );
}
