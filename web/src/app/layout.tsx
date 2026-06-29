import type { Metadata } from "next";
import Script from "next/script";
import "./globals.css";
import { AuthProvider } from "@/lib/auth";
import AppShell from "./AppShell";

export const metadata: Metadata = {
  title: "StudentFlow",
  description: "Student Management System",
};

const assetBase = process.env.NEXT_PUBLIC_API_BASE_URL || "http://localhost:8000";

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en">
      <head>
        <link rel="icon" href={`${assetBase}/favicon.ico`} sizes="any" />
        <link rel="icon" href={`${assetBase}/favicon.png`} type="image/png" />
        <link
          rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        />
      </head>
      <body>
        <Script src="https://accounts.google.com/gsi/client" strategy="afterInteractive" />
        <AuthProvider>
          <AppShell>{children}</AppShell>
        </AuthProvider>
      </body>
    </html>
  );
}
