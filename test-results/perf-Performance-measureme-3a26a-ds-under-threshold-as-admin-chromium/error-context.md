# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: perf.spec.js >> Performance measurements >> reports index loads under threshold as admin
- Location: perf.spec.js:61:3

# Error details

```
Error: expect(received).toBeLessThan(expected)

Expected: < 3000
Received:   10157.89999999851
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
        - heading " Reports" [level=2] [ref=e62]:
          - generic [ref=e63]: 
          - text: Reports
        - paragraph [ref=e64]: Pick a class to generate reports. Each report is available as PDF, CSV, and printable HTML.
        - generic [ref=e65]:
          - generic [ref=e68]:
            - heading "BSIT 1B" [level=5] [ref=e69]
            - paragraph [ref=e70]: Mathematics in the Modern World · 7 students
            - generic [ref=e71]:
              - group [ref=e72]:
                - link " Attendance" [ref=e73] [cursor=pointer]:
                  - /url: /reports/attendance?class_id=2
                  - generic [ref=e74]: 
                  - text: Attendance
                - link " CSV" [ref=e75] [cursor=pointer]:
                  - /url: /reports/attendance/csv?class_id=2
                  - generic [ref=e76]: 
                  - text: CSV
                - link " PDF" [ref=e77] [cursor=pointer]:
                  - /url: /reports/attendance/pdf?class_id=2
                  - generic [ref=e78]: 
                  - text: PDF
              - group [ref=e79]:
                - link " Grades" [ref=e80] [cursor=pointer]:
                  - /url: /reports/grades?class_id=2
                  - generic [ref=e81]: 
                  - text: Grades
                - link " CSV" [ref=e82] [cursor=pointer]:
                  - /url: /reports/grades/csv?class_id=2
                  - generic [ref=e83]: 
                  - text: CSV
                - link " PDF" [ref=e84] [cursor=pointer]:
                  - /url: /reports/grades/pdf?class_id=2
                  - generic [ref=e85]: 
                  - text: PDF
              - group [ref=e86]:
                - link " Performance" [ref=e87] [cursor=pointer]:
                  - /url: /reports/class-performance?class_id=2
                  - generic [ref=e88]: 
                  - text: Performance
                - link " CSV" [ref=e89] [cursor=pointer]:
                  - /url: /reports/class-performance/csv?class_id=2
                  - generic [ref=e90]: 
                  - text: CSV
                - link " PDF" [ref=e91] [cursor=pointer]:
                  - /url: /reports/class-performance/pdf?class_id=2
                  - generic [ref=e92]: 
                  - text: PDF
              - group [ref=e93]:
                - link " Missing" [ref=e94] [cursor=pointer]:
                  - /url: /reports/missing-assignments?class_id=2
                  - generic [ref=e95]: 
                  - text: Missing
                - link " Failing" [ref=e96] [cursor=pointer]:
                  - /url: /reports/failing-grades?class_id=2
                  - generic [ref=e97]: 
                  - text: Failing
                - link " Absences" [ref=e98] [cursor=pointer]:
                  - /url: /reports/frequent-absences?class_id=2
                  - generic [ref=e99]: 
                  - text: Absences
              - group [ref=e100]:
                - link " Sample Profile" [ref=e101] [cursor=pointer]:
                  - /url: /reports/student-profile?student_id=8
                  - generic [ref=e102]: 
                  - text: Sample Profile
          - generic [ref=e105]:
            - heading "BSIT 2A" [level=5] [ref=e106]
            - paragraph [ref=e107]: Object-Oriented Programming · 7 students
            - generic [ref=e108]:
              - group [ref=e109]:
                - link " Attendance" [ref=e110] [cursor=pointer]:
                  - /url: /reports/attendance?class_id=1
                  - generic [ref=e111]: 
                  - text: Attendance
                - link " CSV" [ref=e112] [cursor=pointer]:
                  - /url: /reports/attendance/csv?class_id=1
                  - generic [ref=e113]: 
                  - text: CSV
                - link " PDF" [ref=e114] [cursor=pointer]:
                  - /url: /reports/attendance/pdf?class_id=1
                  - generic [ref=e115]: 
                  - text: PDF
              - group [ref=e116]:
                - link " Grades" [ref=e117] [cursor=pointer]:
                  - /url: /reports/grades?class_id=1
                  - generic [ref=e118]: 
                  - text: Grades
                - link " CSV" [ref=e119] [cursor=pointer]:
                  - /url: /reports/grades/csv?class_id=1
                  - generic [ref=e120]: 
                  - text: CSV
                - link " PDF" [ref=e121] [cursor=pointer]:
                  - /url: /reports/grades/pdf?class_id=1
                  - generic [ref=e122]: 
                  - text: PDF
              - group [ref=e123]:
                - link " Performance" [ref=e124] [cursor=pointer]:
                  - /url: /reports/class-performance?class_id=1
                  - generic [ref=e125]: 
                  - text: Performance
                - link " CSV" [ref=e126] [cursor=pointer]:
                  - /url: /reports/class-performance/csv?class_id=1
                  - generic [ref=e127]: 
                  - text: CSV
                - link " PDF" [ref=e128] [cursor=pointer]:
                  - /url: /reports/class-performance/pdf?class_id=1
                  - generic [ref=e129]: 
                  - text: PDF
              - group [ref=e130]:
                - link " Missing" [ref=e131] [cursor=pointer]:
                  - /url: /reports/missing-assignments?class_id=1
                  - generic [ref=e132]: 
                  - text: Missing
                - link " Failing" [ref=e133] [cursor=pointer]:
                  - /url: /reports/failing-grades?class_id=1
                  - generic [ref=e134]: 
                  - text: Failing
                - link " Absences" [ref=e135] [cursor=pointer]:
                  - /url: /reports/frequent-absences?class_id=1
                  - generic [ref=e136]: 
                  - text: Absences
              - group [ref=e137]:
                - link " Sample Profile" [ref=e138] [cursor=pointer]:
                  - /url: /reports/student-profile?student_id=1
                  - generic [ref=e139]: 
                  - text: Sample Profile
          - generic [ref=e142]:
            - heading "BSIT 3A" [level=5] [ref=e143]
            - paragraph [ref=e144]: Ethics · 6 students
            - generic [ref=e145]:
              - group [ref=e146]:
                - link " Attendance" [ref=e147] [cursor=pointer]:
                  - /url: /reports/attendance?class_id=3
                  - generic [ref=e148]: 
                  - text: Attendance
                - link " CSV" [ref=e149] [cursor=pointer]:
                  - /url: /reports/attendance/csv?class_id=3
                  - generic [ref=e150]: 
                  - text: CSV
                - link " PDF" [ref=e151] [cursor=pointer]:
                  - /url: /reports/attendance/pdf?class_id=3
                  - generic [ref=e152]: 
                  - text: PDF
              - group [ref=e153]:
                - link " Grades" [ref=e154] [cursor=pointer]:
                  - /url: /reports/grades?class_id=3
                  - generic [ref=e155]: 
                  - text: Grades
                - link " CSV" [ref=e156] [cursor=pointer]:
                  - /url: /reports/grades/csv?class_id=3
                  - generic [ref=e157]: 
                  - text: CSV
                - link " PDF" [ref=e158] [cursor=pointer]:
                  - /url: /reports/grades/pdf?class_id=3
                  - generic [ref=e159]: 
                  - text: PDF
              - group [ref=e160]:
                - link " Performance" [ref=e161] [cursor=pointer]:
                  - /url: /reports/class-performance?class_id=3
                  - generic [ref=e162]: 
                  - text: Performance
                - link " CSV" [ref=e163] [cursor=pointer]:
                  - /url: /reports/class-performance/csv?class_id=3
                  - generic [ref=e164]: 
                  - text: CSV
                - link " PDF" [ref=e165] [cursor=pointer]:
                  - /url: /reports/class-performance/pdf?class_id=3
                  - generic [ref=e166]: 
                  - text: PDF
              - group [ref=e167]:
                - link " Missing" [ref=e168] [cursor=pointer]:
                  - /url: /reports/missing-assignments?class_id=3
                  - generic [ref=e169]: 
                  - text: Missing
                - link " Failing" [ref=e170] [cursor=pointer]:
                  - /url: /reports/failing-grades?class_id=3
                  - generic [ref=e171]: 
                  - text: Failing
                - link " Absences" [ref=e172] [cursor=pointer]:
                  - /url: /reports/frequent-absences?class_id=3
                  - generic [ref=e173]: 
                  - text: Absences
              - group [ref=e174]:
                - link " Sample Profile" [ref=e175] [cursor=pointer]:
                  - /url: /reports/student-profile?student_id=15
                  - generic [ref=e176]: 
                  - text: Sample Profile
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