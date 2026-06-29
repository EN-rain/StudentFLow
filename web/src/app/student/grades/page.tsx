"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import Link from "next/link";

export default function StudentGradesPage() {
  const [rows, setRows] = useState<{ class_name?: string; subject?: string; final?: number; letter?: string }[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<{ data: { id: number; class_name: string; title: string; score?: number; maximum_score?: number }[] }>("/api/student/grades")
      .then((res) => {
        const grouped: Record<string, { class_name: string; subject: string; scores: number[] }> = {};
        res.data.forEach((g) => {
          const key = g.class_name || "Unknown";
          if (!grouped[key]) grouped[key] = { class_name: key, subject: g.title || "", scores: [] };
          if (g.score !== undefined && g.maximum_score && g.maximum_score > 0) {
            grouped[key].scores.push(g.score / g.maximum_score * 100);
          }
        });
        setRows(
          Object.values(grouped).map((g) => {
            const avg = g.scores.length > 0 ? g.scores.reduce((a, b) => a + b, 0) / g.scores.length : 0;
            const letter = avg >= 90 ? "A" : avg >= 85 ? "B+" : avg >= 80 ? "B" : avg >= 75 ? "C+" : avg >= 70 ? "C" : avg >= 60 ? "D" : "F";
            return { class_name: g.class_name, subject: g.subject, final: Math.round(avg * 100) / 100, letter };
          })
        );
      })
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading grades...</p></div>;
  if (error) return <div className="sf-retry-banner">{error}</div>;

  return (
    <>
      <div className="page-header"><h2>My Grades</h2></div>
      <div className="card stat-card"><div className="card-body p-0">
        <table className="table table-hover mb-0">
          <thead className="table-light"><tr><th>Class</th><th>Final Grade</th><th></th></tr></thead>
          <tbody>
            {rows.map((r, i) => (
              <tr key={i}>
                <td><strong>{r.class_name}</strong></td>
                <td><span className={`badge bg-${(r.final ?? 0) >= 85 ? "success" : (r.final ?? 0) >= 75 ? "warning" : "danger"}`}>{r.final} ({r.letter})</span></td>
                <td><Link href={`/student/grades/${i}`} className="btn btn-sm btn-outline-primary">Detail</Link></td>
              </tr>
            ))}
            {rows.length === 0 && <tr><td colSpan={3} className="text-center text-muted py-4">No grades yet.</td></tr>}
          </tbody>
        </table>
      </div></div>
    </>
  );
}
