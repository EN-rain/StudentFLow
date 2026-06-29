"use client";

import { useEffect, useState, use } from "react";
import { api } from "@/lib/api";
import Link from "next/link";

interface GradeCategoryDetail {
  id: number;
  category_name: string;
  percentage_weight: number;
  items?: { id: number; title: string; maximum_score: number; date_given?: string }[];
}

interface ClassDetail {
  id: number;
  class_name: string;
  subject: string;
  grade_categories?: GradeCategoryDetail[];
}

export default function GradeDetailPage(props: { params: Promise<{ classId: string }> }) {
  const { classId } = use(props.params);
  const [c, setC] = useState<ClassDetail | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<ClassDetail>(`/api/classes/${classId}`)
      .then(setC)
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, [classId]);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading grades...</p></div>;
  if (error || !c) return <div className="sf-retry-banner">{error || "Not found."}</div>;

  return (
    <>
      <div className="mb-3"><Link href="/grades" className="text-decoration-none">&larr; Back to Grades</Link></div>
      <div className="page-header">
        <div>
          <h2>{c.class_name} — Grades</h2>
          <p className="text-muted">{c.subject}</p>
        </div>
      </div>
      {(!c.grade_categories || c.grade_categories.length === 0) && (
        <div className="card stat-card"><div className="card-body text-center text-muted py-5">No grade categories yet.</div></div>
      )}
      {c.grade_categories?.map((cat) => (
        <div key={cat.id} className="card stat-card mb-3"><div className="card-body">
          <div className="d-flex justify-content-between align-items-center mb-3">
            <h5 className="card-title mb-0">{cat.category_name}</h5>
            <span className="badge bg-secondary">Weight: {cat.percentage_weight}%</span>
          </div>
          <div className="table-responsive">
            <table className="table table-hover mb-0">
              <thead className="table-light">
                <tr><th>Item</th><th>Date Given</th><th>Max Score</th></tr>
              </thead>
              <tbody>
                {cat.items?.map((item) => (
                  <tr key={item.id}>
                    <td>{item.title}</td>
                    <td><small>{item.date_given ? new Date(item.date_given).toLocaleDateString() : "—"}</small></td>
                    <td>{item.maximum_score}</td>
                  </tr>
                ))}
                {(!cat.items || cat.items.length === 0) && <tr><td colSpan={3} className="text-muted text-center py-3">No items.</td></tr>}
              </tbody>
            </table>
          </div>
        </div></div>
      ))}
    </>
  );
}
