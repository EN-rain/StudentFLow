"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";

interface ActivityLog {
  id: number;
  action: string;
  entity_type?: string;
  entity_id?: number;
  user?: { name: string; email: string };
  created_at?: string;
  ip_address?: string;
}

export default function AdminActivityLogsPage() {
  const [logs, setLogs] = useState<ActivityLog[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<{ data: ActivityLog[] }>("/api/admin/activity-logs")
      .then((res) => setLogs(res.data))
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading logs...</p></div>;
  if (error) return <div className="sf-retry-banner">{error}</div>;

  return (
    <>
      <div className="page-header">
        <div>
          <h2>Activity Logs</h2>
          <p>System audit trail</p>
        </div>
      </div>
      <div className="card stat-card"><div className="card-body p-0">
        <table className="table table-hover mb-0">
          <thead className="table-light"><tr><th>User</th><th>Action</th><th>Entity</th><th>IP</th><th>Time</th></tr></thead>
          <tbody>
            {logs.map((l) => (
              <tr key={l.id}>
                <td><small>{l.user?.name || "—"}</small></td>
                <td><code>{l.action}</code></td>
                <td><small>{l.entity_type ? `${l.entity_type}#${l.entity_id}` : "—"}</small></td>
                <td><small>{l.ip_address || "—"}</small></td>
                <td><small>{l.created_at ? new Date(l.created_at).toLocaleString() : "—"}</small></td>
              </tr>
            ))}
            {logs.length === 0 && <tr><td colSpan={5} className="text-center text-muted py-4">No logs yet.</td></tr>}
          </tbody>
        </table>
      </div></div>
    </>
  );
}
