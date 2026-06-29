"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import Link from "next/link";

interface Student {
  id: number;
  student_number: string;
  full_name?: string;
  first_name: string;
  last_name: string;
  email: string;
  gender?: string;
  status: string;
  classes?: { id: number; class_name: string }[];
  user?: { isClassroomVerified?: boolean; google_id?: string; github_id?: string };
}

export default function StudentsPage() {
  const [students, setStudents] = useState<Student[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    api
      .get<Student[]>("/api/students")
      .then(setStudents)
      .catch((err: Error) => setError(err.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="loading-screen"><div className="spinner-border" /><p>Loading students...</p></div>;
  if (error) return <div className="sf-retry-banner">{error}</div>;

  return (
    <>
      <div className="page-header">
        <div>
          <h2>Students</h2>
          <p>Manage student profiles</p>
        </div>
        <Link href="/students/create" className="btn btn-primary">New Student</Link>
      </div>
      <div className="card stat-card">
        <div className="card-body p-0">
          <table className="table table-hover mb-0">
            <thead className="table-light">
              <tr><th>#</th><th>Student Number</th><th>Name</th><th>Email</th><th>Classes</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
              {students.map((s, i) => (
                <tr key={s.id}>
                  <td>{i + 1}</td>
                  <td>{s.student_number}</td>
                  <td><Link href={`/students/${s.id}`}>{s.full_name || `${s.first_name} ${s.last_name}`}</Link></td>
                  <td><small>{s.email}</small></td>
                  <td>{s.classes?.length || 0}</td>
                  <td><span className={`badge bg-${s.status === "active" ? "success" : "secondary"}`}>{s.status}</span></td>
                  <td><Link href={`/students/${s.id}`} className="btn btn-sm btn-outline-primary">View</Link></td>
                </tr>
              ))}
              {students.length === 0 && <tr><td colSpan={7} className="text-center text-muted py-4">No students yet.</td></tr>}
            </tbody>
          </table>
        </div>
      </div>
    </>
  );
}
