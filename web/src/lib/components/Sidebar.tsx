"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useAuth, type User } from "@/lib/auth";

function linkFor(path: string, current: string): string {
  return current.startsWith(path) ? "active" : "";
}

export default function Sidebar() {
  const { user, logout } = useAuth();
  const pathname = usePathname();

  if (!user) return null;

  const isStudent = user.role === "student";
  const isTeacher = user.role === "teacher";
  const isAdmin = user.role === "admin";
  const isStaff = isAdmin || isTeacher;

  return (
    <aside className="sf-sidebar">
      <div className="sf-sidebar-brand">
        <div>
          <strong>StudentFlow</strong>
          <div className="sf-sidebar-brand-subtitle">Learning portal</div>
        </div>
      </div>
      <nav className="sf-sidebar-nav">
        <Link href="/dashboard" className={linkFor("/dashboard", pathname)}>
          Dashboard
        </Link>

        {isStudent && (
          <>
            <Link href="/student/classes" className={linkFor("/student/classes", pathname)}>
              My Classes
            </Link>
            <Link href="/student/attendance" className={linkFor("/student/attendance", pathname)}>
              Attendance
            </Link>
            <Link href="/student/grades" className={linkFor("/student/grades", pathname)}>
              Grades
            </Link>
            <Link href="/student/assignments" className={linkFor("/student/assignments", pathname)}>
              Assignments
            </Link>
            <Link href="/student/exams" className={linkFor("/student/exams", pathname)}>
              Exams
            </Link>
            <Link href="/student/announcements" className={linkFor("/student/announcements", pathname)}>
              Announcements
            </Link>
            <Link href="/student/reports/profile" className={linkFor("/student/reports/profile", pathname)}>
              Reports
            </Link>
          </>
        )}

        {isStaff && (
          <>
            <Link href="/classes" className={linkFor("/classes", pathname)}>
              Classes
            </Link>
            <Link href="/students" className={linkFor("/students", pathname)}>
              Students
            </Link>
            <Link href="/attendance" className={linkFor("/attendance", pathname)}>
              Attendance
            </Link>
            <Link href="/grades" className={linkFor("/grades", pathname)}>
              Grades
            </Link>
            <Link href="/assignments" className={linkFor("/assignments", pathname)}>
              Assignments
            </Link>
            <Link href="/exams" className={linkFor("/exams", pathname)}>
              Exams
            </Link>
            <Link href="/announcements" className={linkFor("/announcements", pathname)}>
              Announcements
            </Link>
            <Link href="/reports" className={linkFor("/reports", pathname)}>
              Reports
            </Link>
          </>
        )}

        {isAdmin && (
          <>
            <Link href="/admin/teachers" className={linkFor("/admin/teachers", pathname)}>
              Teachers
            </Link>
            <Link href="/admin/settings" className={linkFor("/admin/settings", pathname)}>
              Settings
            </Link>
            <Link href="/admin/activity-logs" className={linkFor("/admin/activity-logs", pathname)}>
              Activity Logs
            </Link>
          </>
        )}

        <hr />

        <a
          href="#"
          className="sf-logout-link"
          onClick={(e) => {
            e.preventDefault();
            logout();
          }}
        >
          Logout
        </a>
      </nav>
    </aside>
  );
}
