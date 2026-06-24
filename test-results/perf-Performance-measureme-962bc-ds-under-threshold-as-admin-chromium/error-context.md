# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: perf.spec.js >> Performance measurements >> /dashboard loads under threshold as admin
- Location: perf.spec.js:39:3

# Error details

```
Error: expect(received).toBeLessThan(expected)

Expected: < 3000
Received:   3792.7000000029802
```

# Page snapshot

```yaml
- generic [active] [ref=e1]:
  - navigation [ref=e2]:
    - generic [ref=e3]:
      - link "StudentFlow" [ref=e4] [cursor=pointer]:
        - /url: /dashboard
        - generic [ref=e5]: StudentFlow
      - generic [ref=e6]:
        - generic [ref=e7]:
          - generic [ref=e8]: 
          - text: Maria Santos
          - generic [ref=e9]: Admin
        - button " Logout" [ref=e11] [cursor=pointer]:
          - generic [ref=e12]: 
          - text: Logout
  - generic [ref=e14]:
    - complementary [ref=e15]:
      - list [ref=e17]:
        - listitem [ref=e18]:
          - link " Dashboard" [ref=e19] [cursor=pointer]:
            - /url: /dashboard
            - generic [ref=e20]: 
            - text: Dashboard
        - listitem [ref=e21]:
          - link " Classes" [ref=e22] [cursor=pointer]:
            - /url: /classes
            - generic [ref=e23]: 
            - text: Classes
        - listitem [ref=e24]:
          - link " Students" [ref=e25] [cursor=pointer]:
            - /url: /students
            - generic [ref=e26]: 
            - text: Students
        - listitem [ref=e27]:
          - link " Attendance" [ref=e28] [cursor=pointer]:
            - /url: /attendance
            - generic [ref=e29]: 
            - text: Attendance
        - listitem [ref=e30]:
          - link " Grades" [ref=e31] [cursor=pointer]:
            - /url: /grades
            - generic [ref=e32]: 
            - text: Grades
        - listitem [ref=e33]:
          - link " Assignments" [ref=e34] [cursor=pointer]:
            - /url: /assignments
            - generic [ref=e35]: 
            - text: Assignments
        - listitem [ref=e36]:
          - link " Exams" [ref=e37] [cursor=pointer]:
            - /url: /exams
            - generic [ref=e38]: 
            - text: Exams
        - listitem [ref=e39]:
          - link " Announcements" [ref=e40] [cursor=pointer]:
            - /url: /announcements
            - generic [ref=e41]: 
            - text: Announcements
        - listitem [ref=e42]:
          - link " Reports" [ref=e43] [cursor=pointer]:
            - /url: /reports
            - generic [ref=e44]: 
            - text: Reports
        - listitem [ref=e45]:
          - generic [ref=e46]: Administration
        - listitem [ref=e47]:
          - link " Teachers" [ref=e48] [cursor=pointer]:
            - /url: /admin/teachers
            - generic [ref=e49]: 
            - text: Teachers
        - listitem [ref=e50]:
          - link " Settings" [ref=e51] [cursor=pointer]:
            - /url: /admin/settings
            - generic [ref=e52]: 
            - text: Settings
        - listitem [ref=e53]:
          - link " Activity Logs" [ref=e54] [cursor=pointer]:
            - /url: /admin/activity-logs
            - generic [ref=e55]: 
            - text: Activity Logs
        - listitem [ref=e56]:
          - link " Change Password" [ref=e57] [cursor=pointer]:
            - /url: /change-password
            - generic [ref=e58]: 
            - text: Change Password
    - main [ref=e59]:
      - generic [ref=e60]:
        - generic [ref=e61]:
          - generic [ref=e62]:
            - heading " Administrator Dashboard" [level=2] [ref=e63]:
              - generic [ref=e64]: 
              - text: Administrator Dashboard
            - paragraph [ref=e65]: Operational view across students, classes, staffing, attendance, and announcements.
          - generic [ref=e66]: Welcome, Maria Santos
        - generic [ref=e67]:
          - generic [ref=e70]:
            - generic [ref=e71]: Total Students
            - generic [ref=e72]: "20"
          - generic [ref=e75]:
            - generic [ref=e76]: Total Classes
            - generic [ref=e77]: "3"
          - generic [ref=e80]:
            - generic [ref=e81]: Total Teachers
            - generic [ref=e82]: "3"
          - generic [ref=e85]:
            - generic [ref=e86]: Absent Today
            - generic [ref=e87]: "0"
          - generic [ref=e90]:
            - generic [ref=e91]: Pending Assignments
            - generic [ref=e92]: "2"
          - generic [ref=e95]:
            - generic [ref=e96]: Recent Announcements
            - generic [ref=e97]: "3"
        - generic [ref=e98]:
          - heading " Recent Announcements" [level=5] [ref=e100]:
            - generic [ref=e101]: 
            - text: Recent Announcements
          - list [ref=e103]:
            - listitem [ref=e104]:
              - generic [ref=e105]:
                - generic [ref=e106]: Java Project Consultation
                - text: Project consultation will be held after class on June 22. Bring your source code and project outline...
              - generic [ref=e107]: Important
            - listitem [ref=e108]:
              - generic [ref=e109]:
                - generic [ref=e110]: Quiz Schedule
                - text: Quiz 1 will be held on June 23. Review percentages, ratios, and interest calculations.
              - generic [ref=e111]: Normal
            - listitem [ref=e112]:
              - generic [ref=e113]:
                - generic [ref=e114]: Classroom Change
                - text: Friday's Ethics class will be held in Room 305 instead of Room 301.
              - generic [ref=e115]: Urgent
```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test';
  2  | import {
  3  |   loginAs,
  4  |   captureConsole,
  5  |   captureNetwork,
  6  |   expectNo5xx,
  7  |   measurePageLoad,
  8  |   annotateConsoleErrors,
  9  | } from './_helpers.js';
  10 | 
  11 | test.describe('Performance measurements', () => {
  12 |   let consoleErrors = [];
  13 |   let networkResponses = [];
  14 | 
  15 |   test.beforeEach(async ({ page }) => {
  16 |     consoleErrors = captureConsole(page);
  17 |     networkResponses = captureNetwork(page);
  18 |   });
  19 | 
  20 |   test.afterEach(async ({}, testInfo) => {
  21 |     annotateConsoleErrors(testInfo, consoleErrors);
  22 |   });
  23 | 
  24 |   async function assertLoadTime(page, threshold = 3000) {
  25 |     const timing = await measurePageLoad(page);
  26 |     test.info().annotations.push({ type: 'perf', description: JSON.stringify(timing) });
  27 |     if (timing) {
> 28 |       expect(timing.domContentLoaded).toBeLessThan(threshold);
     |                                       ^ Error: expect(received).toBeLessThan(expected)
  29 |       expect(timing.loadComplete).toBeLessThan(threshold);
  30 |     }
  31 |   }
  32 | 
  33 |   test('/login loads under threshold', async ({ page }) => {
  34 |     await page.goto('/login');
  35 |     expectNo5xx(networkResponses);
  36 |     await assertLoadTime(page);
  37 |   });
  38 | 
  39 |   test('/dashboard loads under threshold as admin', async ({ page }) => {
  40 |     await loginAs(page, 'admin');
  41 |     await page.goto('/dashboard');
  42 |     expectNo5xx(networkResponses);
  43 |     await assertLoadTime(page);
  44 |   });
  45 | 
  46 |   test('/admin/teachers loads under threshold as admin', async ({ page }) => {
  47 |     await loginAs(page, 'admin');
  48 |     await page.goto('/admin/teachers');
  49 |     expectNo5xx(networkResponses);
  50 |     await assertLoadTime(page);
  51 |   });
  52 | 
  53 |   test('/student/dashboard attempted load is captured', async ({ page }) => {
  54 |     // There is no /student/dashboard route; measure the resolved redirect target.
  55 |     await page.goto('/student');
  56 |     expectNo5xx(networkResponses);
  57 |     const timing = await measurePageLoad(page);
  58 |     test.info().annotations.push({ type: 'perf', description: JSON.stringify(timing) });
  59 |   });
  60 | 
  61 |   test('reports index loads under threshold as admin', async ({ page }) => {
  62 |     await loginAs(page, 'admin');
  63 |     await page.goto('/reports');
  64 |     expectNo5xx(networkResponses);
  65 |     await assertLoadTime(page);
  66 |   });
  67 | });
  68 | 
```