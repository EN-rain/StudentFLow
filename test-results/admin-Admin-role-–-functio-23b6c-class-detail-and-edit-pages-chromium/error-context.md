# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: admin.spec.js >> Admin role – functional happy path >> Classes management >> first class detail and edit pages
- Location: admin.spec.js:105:5

# Error details

```
Error: CSRF token missing in POST form

expect(locator).toBeAttached() failed

Locator: locator('form[method="POST"], form[method="post"]').nth(4).locator('input[name="_token"]').first()
Expected: attached
Timeout: 5000ms
Error: element(s) not found

Call log:
  - CSRF token missing in POST form with timeout 5000ms
  - waiting for locator('form[method="POST"], form[method="post"]').nth(4).locator('input[name="_token"]').first()
    - waiting for" http://172.29.144.1:8000/classes/2/edit" navigation to finish...
    - navigated to "http://172.29.144.1:8000/classes/2/edit"

```

```yaml
- navigation:
  - link "StudentFlow":
    - /url: /dashboard
  - text:  Maria Santos Admin
  - button " Logout"
- complementary:
  - list:
    - listitem:
      - link " Dashboard":
        - /url: /dashboard
    - listitem:
      - link " Classes":
        - /url: /classes
    - listitem:
      - link " Students":
        - /url: /students
    - listitem:
      - link " Attendance":
        - /url: /attendance
    - listitem:
      - link " Grades":
        - /url: /grades
    - listitem:
      - link " Assignments":
        - /url: /assignments
    - listitem:
      - link " Exams":
        - /url: /exams
    - listitem:
      - link " Announcements":
        - /url: /announcements
    - listitem:
      - link " Reports":
        - /url: /reports
    - listitem: Administration
    - listitem:
      - link " Teachers":
        - /url: /admin/teachers
    - listitem:
      - link " Settings":
        - /url: /admin/settings
    - listitem:
      - link " Activity Logs":
        - /url: /admin/activity-logs
    - listitem:
      - link " Change Password":
        - /url: /change-password
- main:
  - 'heading " Edit Class: BSIT 1B" [level=2]'
  - text: Class Name *
  - textbox: BSIT 1B
  - text: Section
  - textbox: B
  - text: Subject *
  - textbox: Mathematics in the Modern World
  - text: Grade Level
  - textbox: First Year College
  - text: School Year
  - textbox: 2026-2027
  - text: Semester
  - textbox: First Semester
  - text: Schedule
  - textbox "e.g. MWF 9-10 AM": Tuesday and Thursday, 1:00 PM-2:30 PM
  - text: Room
  - textbox: Room 204
  - text: Status
  - combobox:
    - option "Active" [selected]
    - option "Archived"
  - text: Teacher *
  - combobox:
    - option "Select teacher"
    - option "Angela Marie Cruz (TCH-2026-002)" [selected]
    - option "Roberto Dela Pena (TCH-2026-003)"
    - option "John Michael Reyes (TCH-2026-001)"
  - button " Save"
  - link "Cancel":
    - /url: /classes
```

# Test source

```ts
  1   | import { expect } from '@playwright/test';
  2   | 
  3   | export const CREDENTIALS = {
  4   |   admin: {
  5   |     email: process.env.STUDENTFLOW_ADMIN_EMAIL || 'admin@studentflow.local',
  6   |     password: process.env.STUDENTFLOW_ADMIN_PASSWORD || 'AdminPass123!',
  7   |   },
  8   |   teacher: {
  9   |     email: process.env.STUDENTFLOW_TEACHER_EMAIL || 'john.reyes@studentflow.local',
  10  |     password: process.env.STUDENTFLOW_TEACHER_PASSWORD || 'TeacherPass123!',
  11  |   },
  12  |   student: {
  13  |     email: process.env.STUDENTFLOW_STUDENT_EMAIL || 'aaron.villanueva@studentflow.local',
  14  |     password: process.env.STUDENTFLOW_STUDENT_PASSWORD || 'StudentPass123!',
  15  |   },
  16  | };
  17  | 
  18  | export async function loginAs(page, role) {
  19  |   const creds = CREDENTIALS[role];
  20  |   if (!creds) throw new Error(`Unknown role: ${role}`);
  21  | 
  22  |   await page.goto('/login');
  23  |   await page.waitForSelector('form[data-login-form]', { state: 'visible' });
  24  |   await page.fill('input[name="username"]', creds.email);
  25  |   await page.fill('input[name="password"]', creds.password);
  26  | 
  27  |   const [response] = await Promise.all([
  28  |     page.waitForResponse((res) => res.url().includes('/login') && res.request().method() === 'POST'),
  29  |     page.click('button[data-login-submit]'),
  30  |   ]);
  31  | 
  32  |   await page.waitForLoadState('load');
  33  |   return response;
  34  | }
  35  | 
  36  | export function captureConsole(page) {
  37  |   const errors = [];
  38  |   page.on('console', (msg) => {
  39  |     const type = msg.type();
  40  |     if (type === 'error' || type === 'warning') {
  41  |       errors.push(`[${type}] ${msg.text()}`);
  42  |     }
  43  |   });
  44  |   page.on('pageerror', (err) => {
  45  |     errors.push(`[pageerror] ${err.message}`);
  46  |   });
  47  |   return errors;
  48  | }
  49  | 
  50  | export function captureNetwork(page) {
  51  |   const responses = [];
  52  |   page.on('response', (res) => {
  53  |     responses.push(res);
  54  |   });
  55  |   return responses;
  56  | }
  57  | 
  58  | export function expectNo5xx(responses) {
  59  |   const bad = responses.filter((r) => r.status() >= 500);
  60  |   if (bad.length > 0) {
  61  |     const summary = bad.map((r) => `${r.request().method()} ${r.url()} -> ${r.status()}`).join('; ');
  62  |     throw new Error(`Received ${bad.length} 5xx response(s): ${summary}`);
  63  |   }
  64  | }
  65  | 
  66  | export async function expectCsrfOnForms(page) {
  67  |   const forms = await page.locator('form[method="POST"], form[method="post"]').all();
  68  |   for (const form of forms) {
  69  |     const token = await form.locator('input[name="_token"]').first();
> 70  |     await expect(token, 'CSRF token missing in POST form').toBeAttached();
      |                                                            ^ Error: CSRF token missing in POST form
  71  |   }
  72  | }
  73  | 
  74  | export function expectNoConsoleErrors(errors) {
  75  |   const severe = errors.filter((e) => !e.toLowerCase().includes('favicon'));
  76  |   if (severe.length > 0) {
  77  |     throw new Error(`Console errors detected:\n${severe.join('\n')}`);
  78  |   }
  79  | }
  80  | 
  81  | export async function measurePageLoad(page) {
  82  |   const timing = await page.evaluate(() => {
  83  |     const nav = performance.getEntriesByType('navigation')[0];
  84  |     if (!nav) return null;
  85  |     return {
  86  |       domContentLoaded: nav.domContentLoadedEventEnd - nav.startTime,
  87  |       loadComplete: nav.loadEventEnd - nav.startTime,
  88  |     };
  89  |   });
  90  |   return timing;
  91  | }
  92  | 
  93  | export function annotateConsoleErrors(testInfo, errors) {
  94  |   for (const err of errors) {
  95  |     testInfo.annotations.push({ type: 'console-error', description: err });
  96  |   }
  97  | }
  98  | 
  99  | export async function fillVisibleForm(page) {
  100 |   const textInputs = await page.locator('input[type="text"][name], input[type="email"][name], input[type="password"][name], input[type="number"][name], input[type="date"][name], input[type="url"][name], textarea[name]').all();
  101 |   for (const input of textInputs) {
  102 |     const name = await input.getAttribute('name');
  103 |     const type = await input.getAttribute('type');
  104 |     if (name === '_token' || name === '_method') continue;
  105 | 
  106 |     let value = 'Sample value';
  107 |     if (type === 'email') value = 'sample@studentflow.local';
  108 |     else if (type === 'password') value = 'Password123!';
  109 |     else if (type === 'number') value = '10';
  110 |     else if (type === 'date') value = '2026-06-23';
  111 |     else if (type === 'url') value = 'https://example.com';
  112 |     else if (name.toLowerCase().includes('employee_number')) value = 'TCH-2026-999';
  113 |     else if (name.toLowerCase().includes('student_number')) value = '2026-0999';
  114 |     else if (name.toLowerCase().includes('contact')) value = '09171234567';
  115 | 
  116 |     if (await input.isVisible()) {
  117 |       await input.fill(value);
  118 |     }
  119 |   }
  120 | 
  121 |   const selects = await page.locator('select[name]').all();
  122 |   for (const select of selects) {
  123 |     const options = await select.locator('option').all();
  124 |     const valid = options.find(async (opt) => {
  125 |       const val = await opt.getAttribute('value');
  126 |       return val && val !== '';
  127 |     });
  128 |     if (valid && await select.isVisible()) {
  129 |       await select.selectOption(await valid.getAttribute('value'));
  130 |     }
  131 |   }
  132 | }
  133 | 
```