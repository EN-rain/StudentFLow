"use client";

import { useEffect, useState } from "react";
import { api, buildAbsoluteUrl } from "@/lib/api";
import { useAuth } from "@/lib/auth";

export default function StudentProfileReportPage() {
  const { user } = useAuth();
  const [profile, setProfile] = useState<Record<string, unknown> | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<{ data: Record<string, unknown> }>("/api/student/profile")
      .then((res) => setProfile(res.data))
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading profile...</p></div>;
  if (error) return <div className="sf-retry-banner">{error}</div>;

  return (
    <>
      <div className="page-header">
        <div>
          <h2>My Profile Report</h2>
          <p>Student information summary</p>
        </div>
        <a href={buildAbsoluteUrl(`/api/reports/student-profile/pdf?student_id=${user?.student?.id ?? ""}`)} className="btn btn-outline-primary" target="_blank" rel="noopener">
          Download PDF
        </a>
      </div>
      <div className="card stat-card"><div className="card-body">
        <dl className="row mb-0">
          {profile && Object.entries(profile).map(([key, val]) => (
            key !== "classes" && key !== "id" ? (
              <div key={key} className="mb-2">
                <dt className="col-sm-3" style={{ display: "inline-block" }}>{key.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase())}</dt>
                <dd className="col-sm-9" style={{ display: "inline-block" }}>{String(val ?? "—")}</dd>
              </div>
            ) : null
          ))}
        </dl>
      </div></div>
    </>
  );
}
