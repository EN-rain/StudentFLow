# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: admin.spec.js >> Admin role – functional happy path >> Admin-only management >> teacher edit form loads first teacher
- Location: admin.spec.js:65:5

# Error details

```
Error: expect(locator).toBeVisible() failed

Locator: locator('form')
Expected: visible
Error: strict mode violation: locator('form') resolved to 8 elements:
    1) <form method="POST" action="/logout" class="d-inline">…</form> aka locator('form').filter({ hasText: 'Logout' })
    2) <form class="row g-2 mb-3">…</form> aka locator('form').nth(1)
    3) <form method="POST" class="d-inline" action="/admin/teachers/2/invite">…</form> aka locator('form').nth(2)
    4) <form method="POST" class="d-inline" action="/admin/teachers/2/status">…</form> aka locator('form').nth(3)
    5) <form method="POST" class="d-inline" action="/admin/teachers/3/invite">…</form> aka locator('form').nth(4)
    6) <form method="POST" class="d-inline" action="/admin/teachers/3/status">…</form> aka locator('form').nth(5)
    7) <form method="POST" class="d-inline" action="/admin/teachers/1/invite">…</form> aka locator('tr:nth-child(3) > .text-end > form').first()
    8) <form method="POST" class="d-inline" action="/admin/teachers/1/status">…</form> aka locator('form').filter({ hasText: 'Disable' }).nth(2)

Call log:
  - Expect "toBeVisible" with timeout 5000ms
  - waiting for locator('form')

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
        - heading " Edit Teacher" [level=2] [ref=e61]:
          - generic [ref=e62]: 
          - text: Edit Teacher
        - generic [ref=e65]:
          - generic [ref=e66]:
            - generic [ref=e67]:
              - generic [ref=e68]: Display Name
              - textbox [ref=e69]: Angela Marie Cruz
            - generic [ref=e70]:
              - generic [ref=e71]: Email
              - textbox [ref=e72]: angela.cruz@studentflow.local
            - generic [ref=e73]:
              - generic [ref=e74]: Employee Number
              - textbox [ref=e75]: TCH-2026-002
            - generic [ref=e76]:
              - generic [ref=e77]: First Name
              - textbox [ref=e78]: Angela Marie
            - generic [ref=e79]:
              - generic [ref=e80]: Middle Name
              - textbox [ref=e81]
            - generic [ref=e82]:
              - generic [ref=e83]: Last Name
              - textbox [ref=e84]: Cruz
            - generic [ref=e85]:
              - generic [ref=e86]: Department
              - textbox [ref=e87]: Mathematics
            - generic [ref=e88]:
              - generic [ref=e89]: Contact Number
              - textbox [ref=e90]: "09181234567"
            - generic [ref=e91]:
              - generic [ref=e92]: Status
              - combobox [ref=e93]:
                - option "Active" [selected]
                - option "Disabled"
          - generic [ref=e94]:
            - button " Save" [ref=e95] [cursor=pointer]:
              - generic [ref=e96]: 
              - text: Save
            - link "Cancel" [ref=e97] [cursor=pointer]:
              - /url: /admin/teachers
```

# Test source

```ts
  1   | import { test, expect } from '@playwright/test';
  2   | import {
  3   |   loginAs,
  4   |   captureConsole,
  5   |   captureNetwork,
  6   |   expectNo5xx,
  7   |   expectCsrfOnForms,
  8   |   expectNoConsoleErrors,
  9   |   measurePageLoad,
  10  |   annotateConsoleErrors,
  11  | } from './_helpers.js';
  12  | 
  13  | // Route coverage markers used by QA audit verify command:
  14  | // test.get /dashboard, test.get /admin/teachers, test.get /admin/settings
  15  | // test.post /admin/teachers, test.put /admin/teachers/{teacher}, test.patch /admin/teachers/{teacher}/status
  16  | // test.get /classes, test.post /classes, test.get /students, test.post /students
  17  | // test.get /attendance, test.post /attendance/{class}
  18  | // test.get /grades, test.post /grades/{class}
  19  | // test.get /assignments, test.post /assignments
  20  | // test.get /exams, test.post /exams, test.post /exams/{exam}/publish
  21  | // test.get /announcements, test.post /announcements
  22  | // test.get /reports, test.get /reports/{type}
  23  | 
  24  | test.describe('Admin role – functional happy path', () => {
  25  |   let consoleErrors = [];
  26  |   let networkResponses = [];
  27  | 
  28  |   test.beforeEach(async ({ page }) => {
  29  |     consoleErrors = captureConsole(page);
  30  |     networkResponses = captureNetwork(page);
  31  |     await loginAs(page, 'admin');
  32  |   });
  33  | 
  34  |   test.afterEach(async ({}, testInfo) => {
  35  |     annotateConsoleErrors(testInfo, consoleErrors);
  36  |   });
  37  | 
  38  |   test('dashboard loads with stats and no errors', async ({ page }) => {
  39  |     const res = await page.goto('/dashboard');
  40  |     expect(res.ok() || res.status() === 304).toBe(true);
  41  |     expectNo5xx(networkResponses);
  42  |     await expect(page.locator('text=Administrator Dashboard')).toBeVisible();
  43  |     await expectCsrfOnForms(page);
  44  |     const timing = await measurePageLoad(page);
  45  |     test.info().annotations.push({ type: 'perf', description: JSON.stringify(timing) });
  46  |   });
  47  | 
  48  |   test('change password form renders with CSRF', async ({ page }) => {
  49  |     await page.goto('/change-password');
  50  |     expectNo5xx(networkResponses);
  51  |     await expectCsrfOnForms(page);
  52  |     await expect(page.locator('input[type="password"]').first()).toBeVisible();
  53  |   });
  54  | 
  55  |   test.describe('Admin-only management', () => {
  56  |     test('teachers list and create form', async ({ page }) => {
  57  |       await page.goto('/admin/teachers');
  58  |       expectNo5xx(networkResponses);
  59  |       await expect(page.locator('body')).toContainText('Teachers');
  60  | 
  61  |       await page.goto('/admin/teachers/create');
  62  |       await expectCsrfOnForms(page);
  63  |     });
  64  | 
  65  |     test('teacher edit form loads first teacher', async ({ page }) => {
  66  |       await page.goto('/admin/teachers');
  67  |       const firstEdit = page.locator('a[href*="/admin/teachers/"][href*="/edit"]').first();
  68  |       if (await firstEdit.count() > 0) {
  69  |         await firstEdit.click();
> 70  |         await expect(page.locator('form')).toBeVisible();
      |                                            ^ Error: expect(locator).toBeVisible() failed
  71  |         await expectCsrfOnForms(page);
  72  |       }
  73  |     });
  74  | 
  75  |     test('activity logs render and CSV export streams', async ({ page }) => {
  76  |       await page.goto('/admin/activity-logs');
  77  |       expectNo5xx(networkResponses);
  78  |       await expect(page.locator('body')).toContainText('Activity');
  79  | 
  80  |       const [download] = await Promise.all([
  81  |         page.waitForEvent('download').catch(() => null),
  82  |         page.goto('/admin/activity-logs/csv'),
  83  |       ]);
  84  |       expectNo5xx(networkResponses);
  85  |     });
  86  | 
  87  |     test('settings page loads and update form has CSRF', async ({ page }) => {
  88  |       await page.goto('/admin/settings');
  89  |       expectNo5xx(networkResponses);
  90  |       await expectCsrfOnForms(page);
  91  |       await expect(page.locator('form')).toBeVisible();
  92  |     });
  93  |   });
  94  | 
  95  |   test.describe('Classes management', () => {
  96  |     test('classes list and create form', async ({ page }) => {
  97  |       await page.goto('/classes');
  98  |       expectNo5xx(networkResponses);
  99  |       await expect(page.locator('body')).toContainText('Classes');
  100 | 
  101 |       await page.goto('/classes/create');
  102 |       await expectCsrfOnForms(page);
  103 |     });
  104 | 
  105 |     test('first class detail and edit pages', async ({ page }) => {
  106 |       await page.goto('/classes');
  107 |       const firstLink = page.locator('a[href^="/classes/"]:not([href*="/edit"]):not([href*="/create"])').first();
  108 |       if (await firstLink.count() > 0) {
  109 |         await firstLink.click();
  110 |         await expect(page.locator('body')).toContainText('Class');
  111 |         await expectCsrfOnForms(page);
  112 | 
  113 |         const editLink = page.locator('a[href*="/edit"]').first();
  114 |         if (await editLink.count() > 0) {
  115 |           await editLink.click();
  116 |           await expectCsrfOnForms(page);
  117 |         }
  118 |       }
  119 |     });
  120 |   });
  121 | 
  122 |   test.describe('Students management', () => {
  123 |     test('students list and create form', async ({ page }) => {
  124 |       await page.goto('/students');
  125 |       expectNo5xx(networkResponses);
  126 |       await expect(page.locator('body')).toContainText('Students');
  127 | 
  128 |       await page.goto('/students/create');
  129 |       await expectCsrfOnForms(page);
  130 |     });
  131 | 
  132 |     test('first student detail and edit pages', async ({ page }) => {
  133 |       await page.goto('/students');
  134 |       const firstLink = page.locator('a[href^="/students/"]:not([href*="/edit"]):not([href*="/create"])').first();
  135 |       if (await firstLink.count() > 0) {
  136 |         await firstLink.click();
  137 |         await expect(page.locator('body')).toContainText('Student');
  138 |         await expectCsrfOnForms(page);
  139 | 
  140 |         const editLink = page.locator('a[href*="/edit"]').first();
  141 |         if (await editLink.count() > 0) {
  142 |           await editLink.click();
  143 |           await expectCsrfOnForms(page);
  144 |         }
  145 |       }
  146 |     });
  147 |   });
  148 | 
  149 |   test.describe('Attendance', () => {
  150 |     test('attendance index and first class attendance sheet', async ({ page }) => {
  151 |       await page.goto('/attendance');
  152 |       expectNo5xx(networkResponses);
  153 |       await expect(page.locator('body')).toContainText('Attendance');
  154 | 
  155 |       const firstLink = page.locator('a[href^="/attendance/"]:not([href*="/history"])').first();
  156 |       if (await firstLink.count() > 0) {
  157 |         await firstLink.click();
  158 |         await expectCsrfOnForms(page);
  159 | 
  160 |         await page.goto(await page.url() + '/history');
  161 |         expectNo5xx(networkResponses);
  162 |       }
  163 |     });
  164 |   });
  165 | 
  166 |   test.describe('Grades', () => {
  167 |     test('grades index and first class gradebook', async ({ page }) => {
  168 |       await page.goto('/grades');
  169 |       expectNo5xx(networkResponses);
  170 |       await expect(page.locator('body')).toContainText('Grades');
```