// Playwright E2E configuration for StudentFlow QA audit (Step 2)
// @ts-check
import { defineConfig, devices } from '@playwright/test';

// Export seeded credentials into process.env so specs can read them.
process.env.STUDENTFLOW_ADMIN_EMAIL = process.env.STUDENTFLOW_ADMIN_EMAIL || 'admin@studentflow.local';
process.env.STUDENTFLOW_ADMIN_PASSWORD = process.env.STUDENTFLOW_ADMIN_PASSWORD || 'AdminPass123!';
process.env.STUDENTFLOW_TEACHER_EMAIL = process.env.STUDENTFLOW_TEACHER_EMAIL || 'john.reyes@studentflow.local';
process.env.STUDENTFLOW_TEACHER_PASSWORD = process.env.STUDENTFLOW_TEACHER_PASSWORD || 'TeacherPass123!';
process.env.STUDENTFLOW_STUDENT_EMAIL = process.env.STUDENTFLOW_STUDENT_EMAIL || 'aaron.villanueva@studentflow.local';
process.env.STUDENTFLOW_STUDENT_PASSWORD = process.env.STUDENTFLOW_STUDENT_PASSWORD || 'StudentPass123!';

export default defineConfig({
  testDir: './',
  expect: {
    timeout: 5000,
  },
  // Failures are NOT retried; each failure is recorded once.
  retries: 0,
  workers: 1,
  timeout: 60000,
  reporter: 'list',
  use: {
    baseURL: 'http://127.0.0.1:8000',
    headless: true,
    viewport: { width: 1280, height: 720 },
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
  },
  // Chromium-only project for this audit step.
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
