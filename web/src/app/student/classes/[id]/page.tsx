"use client";

import { useEffect, useState, use } from "react";
import { api } from "@/lib/api";
import Link from "next/link";

export default function StudentClassDetailPage(props: { params: Promise<{ id: string }> }) {
  const { id } = use(props.params);
  const [c, setC] = useState<Record<string, unknown> | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<{ data: Record<string, unknown> }>(`/api/student/classes/${id}`)
      .then((res) => setC(res.data))
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, [id]);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading class...</p></div>;
  if (error || !c) return <div className="sf-retry-banner">{error || "Not found."}</div>;

  return (
    <>
      <div className="mb-3"><Link href="/student/classes" className="text-decoration-none">&larr; Back</Link></div>
      <div className="page-header"><h2>{String(c.class_name || "")}</h2></div>
      <div className="card stat-card"><div className="card-body">
        <dl className="row mb-0">
          <dt className="col-sm-3">Subject</dt><dd className="col-sm-9">{String(c.subject || "—")}</dd>
          <dt className="col-sm-3">Schedule</dt><dd className="col-sm-9">{String(c.schedule || "—")}</dd>
          <dt className="col-sm-3">Teacher</dt><dd className="col-sm-9">{String((c as Record<string, { user?: { name?: string } }>).teacher?.user?.name || "—")}</dd>
        </dl>
      </div></div>
    </>
  );
}
