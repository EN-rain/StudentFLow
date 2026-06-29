"use client";

import { useEffect, useState, use } from "react";
import { api } from "@/lib/api";
import Link from "next/link";

export default function StudentAnnouncementDetailPage(props: { params: Promise<{ id: string }> }) {
  const { id } = use(props.params);
  const [a, setA] = useState<Record<string, unknown> | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<{ data: Record<string, unknown> }>(`/api/student/announcements/${id}`)
      .then((res) => setA(res.data))
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, [id]);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading announcement...</p></div>;
  if (error || !a) return <div className="sf-retry-banner">{error || "Not found."}</div>;

  return (
    <>
      <div className="mb-3"><Link href="/student/announcements" className="text-decoration-none">&larr; Back</Link></div>
      <div className="card stat-card"><div className="card-body">
        <h2>{String(a.title || "")}</h2>
        <p className="text-muted">Posted {a.publish_date ? new Date(String(a.publish_date)).toLocaleDateString() : "—"}</p>
        <hr />
        <div style={{ whiteSpace: "pre-wrap" }}>{String(a.message || "")}</div>
      </div></div>
    </>
  );
}
