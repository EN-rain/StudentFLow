"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";

interface Setting {
  setting_key: string;
  setting_value: string;
  label?: string;
}

export default function AdminSettingsPage() {
  const [settings, setSettings] = useState<Setting[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<{ data: Setting[] }>("/api/admin/settings")
      .then((res) => setSettings(res.data))
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading settings...</p></div>;
  if (error) return <div className="sf-retry-banner">{error}</div>;

  return (
    <>
      <div className="page-header">
        <div>
          <h2>School Settings</h2>
          <p>Configure system settings</p>
        </div>
      </div>
      <div className="card stat-card"><div className="card-body p-0">
        <table className="table table-hover mb-0">
          <thead className="table-light"><tr><th>Key</th><th>Value</th><th>Label</th></tr></thead>
          <tbody>
            {settings.map((s) => (
              <tr key={s.setting_key}>
                <td><code>{s.setting_key}</code></td>
                <td>{s.setting_value}</td>
                <td className="text-muted">{s.label || "—"}</td>
              </tr>
            ))}
            {settings.length === 0 && <tr><td colSpan={3} className="text-center text-muted py-4">No settings.</td></tr>}
          </tbody>
        </table>
      </div></div>
    </>
  );
}
