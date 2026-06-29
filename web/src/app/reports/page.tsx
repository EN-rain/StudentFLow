"use client";

import Link from "next/link";

const REPORTS = [
  { type: "student-profile", label: "Student Profile", desc: "View individual student details" },
  { type: "attendance", label: "Attendance Report", desc: "Attendance summary by class" },
  { type: "grades", label: "Grades Report", desc: "Grade summaries" },
  { type: "class-performance", label: "Class Performance", desc: "Performance across classes" },
  { type: "missing-assignments", label: "Missing Assignments", desc: "Students with missing work" },
  { type: "failing-grades", label: "Failing Grades", desc: "Students below passing threshold" },
  { type: "frequent-absences", label: "Frequent Absences", desc: "Students with high absence rates" },
];

export default function ReportsPage() {
  return (
    <>
      <div className="page-header">
        <div>
          <h2>Reports</h2>
          <p>Generate and download reports</p>
        </div>
      </div>
      <div className="row g-3">
        {REPORTS.map((r) => (
          <div className="col-md-4" key={r.type}>
            <div className="card stat-card"><div className="card-body">
              <h5 className="card-title">{r.label}</h5>
              <p className="text-muted small">{r.desc}</p>
              <div className="d-flex gap-2">
                <Link href={`/reports/${r.type}`} className="btn btn-sm btn-outline-primary">View</Link>
                <Link href={`/reports/${r.type}/pdf`} className="btn btn-sm btn-outline-secondary">PDF</Link>
                <Link href={`/reports/${r.type}/csv`} className="btn btn-sm btn-outline-secondary">CSV</Link>
              </div>
            </div></div>
          </div>
        ))}
      </div>
    </>
  );
}
