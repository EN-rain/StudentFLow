# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: admin.spec.js >> Admin role – functional happy path >> Attendance >> attendance index and first class attendance sheet
- Location: admin.spec.js:150:5

# Error details

```
Error: page.goto: net::ERR_ABORTED at http://172.29.144.1:8000/attendance/history
Call log:
  - navigating to "http://172.29.144.1:8000/attendance/history", waiting until "load"

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
            - heading " Mark Attendance" [level=2] [ref=e63]:
              - generic [ref=e64]: 
              - text: Mark Attendance
            - paragraph [ref=e65]: BSIT 1B - Mathematics in the Modern World
          - generic [ref=e66]:
            - link " History" [ref=e67] [cursor=pointer]:
              - /url: /attendance/2/history
              - generic [ref=e68]: 
              - text: History
            - link "Back" [ref=e69] [cursor=pointer]:
              - /url: /attendance
        - generic [ref=e70]:
          - generic [ref=e72]:
            - generic [ref=e73]:
              - generic [ref=e74]: Date
              - textbox [ref=e75]: 2026-06-23
            - button " Mark all Present" [ref=e77] [cursor=pointer]:
              - generic [ref=e78]: 
              - text: Mark all Present
          - table [ref=e81]:
            - rowgroup [ref=e82]:
              - row "# Student Status Remarks" [ref=e83]:
                - columnheader "#" [ref=e84]
                - columnheader "Student" [ref=e85]
                - columnheader "Status" [ref=e86]
                - columnheader "Remarks" [ref=e87]
            - rowgroup [ref=e88]:
              - row "1 Jasmine Marie Aquino 2026-0010 -" [ref=e89]:
                - cell "1" [ref=e90]
                - cell "Jasmine Marie Aquino 2026-0010" [ref=e91]:
                  - link "Jasmine Marie Aquino" [ref=e92] [cursor=pointer]:
                    - /url: /students/10
                  - text: 2026-0010
                - cell "-" [ref=e93]:
                  - combobox [ref=e94]:
                    - option "-" [selected]
                    - option "Present"
                    - option "Absent"
                    - option "Late"
                    - option "Excused"
                - cell [ref=e95]:
                  - textbox "Optional remarks" [ref=e96]
              - row "2 Kevin Anthony Bautista 2026-0011 -" [ref=e97]:
                - cell "2" [ref=e98]
                - cell "Kevin Anthony Bautista 2026-0011" [ref=e99]:
                  - link "Kevin Anthony Bautista" [ref=e100] [cursor=pointer]:
                    - /url: /students/11
                  - text: 2026-0011
                - cell "-" [ref=e101]:
                  - combobox [ref=e102]:
                    - option "-" [selected]
                    - option "Present"
                    - option "Absent"
                    - option "Late"
                    - option "Excused"
                - cell [ref=e103]:
                  - textbox "Optional remarks" [ref=e104]
              - row "3 Ivan James Castillo 2026-0009 -" [ref=e105]:
                - cell "3" [ref=e106]
                - cell "Ivan James Castillo 2026-0009" [ref=e107]:
                  - link "Ivan James Castillo" [ref=e108] [cursor=pointer]:
                    - /url: /students/9
                  - text: 2026-0009
                - cell "-" [ref=e109]:
                  - combobox [ref=e110]:
                    - option "-" [selected]
                    - option "Present"
                    - option "Absent"
                    - option "Late"
                    - option "Excused"
                - cell [ref=e111]:
                  - textbox "Optional remarks" [ref=e112]
              - row "4 Nicole Anne Fernandez 2026-0014 -" [ref=e113]:
                - cell "4" [ref=e114]
                - cell "Nicole Anne Fernandez 2026-0014" [ref=e115]:
                  - link "Nicole Anne Fernandez" [ref=e116] [cursor=pointer]:
                    - /url: /students/14
                  - text: 2026-0014
                - cell "-" [ref=e117]:
                  - combobox [ref=e118]:
                    - option "-" [selected]
                    - option "Present"
                    - option "Absent"
                    - option "Late"
                    - option "Excused"
                - cell [ref=e119]:
                  - textbox "Optional remarks" [ref=e120]
              - row "5 Hannah Grace Lim 2026-0008 -" [ref=e121]:
                - cell "5" [ref=e122]
                - cell "Hannah Grace Lim 2026-0008" [ref=e123]:
                  - link "Hannah Grace Lim" [ref=e124] [cursor=pointer]:
                    - /url: /students/8
                  - text: 2026-0008
                - cell "-" [ref=e125]:
                  - combobox [ref=e126]:
                    - option "-" [selected]
                    - option "Present"
                    - option "Absent"
                    - option "Late"
                    - option "Excused"
                - cell [ref=e127]:
                  - textbox "Optional remarks" [ref=e128]
              - row "6 Mark Anthony Reyes Perez 2026-0013 -" [ref=e129]:
                - cell "6" [ref=e130]
                - cell "Mark Anthony Reyes Perez 2026-0013" [ref=e131]:
                  - link "Mark Anthony Reyes Perez" [ref=e132] [cursor=pointer]:
                    - /url: /students/13
                  - text: 2026-0013
                - cell "-" [ref=e133]:
                  - combobox [ref=e134]:
                    - option "-" [selected]
                    - option "Present"
                    - option "Absent"
                    - option "Late"
                    - option "Excused"
                - cell [ref=e135]:
                  - textbox "Optional remarks" [ref=e136]
              - row "7 Lara Jean Santiago 2026-0012 -" [ref=e137]:
                - cell "7" [ref=e138]
                - cell "Lara Jean Santiago 2026-0012" [ref=e139]:
                  - link "Lara Jean Santiago" [ref=e140] [cursor=pointer]:
                    - /url: /students/12
                  - text: 2026-0012
                - cell "-" [ref=e141]:
                  - combobox [ref=e142]:
                    - option "-" [selected]
                    - option "Present"
                    - option "Absent"
                    - option "Late"
                    - option "Excused"
                - cell [ref=e143]:
                  - textbox "Optional remarks" [ref=e144]
          - button " Save Attendance" [ref=e146] [cursor=pointer]:
            - generic [ref=e147]: 
            - text: Save Attendance
```

# Test source

```ts
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
  70  |         await expect(page.locator('form')).toBeVisible();
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
> 160 |         await page.goto(await page.url() + '/history');
      |                    ^ Error: page.goto: net::ERR_ABORTED at http://172.29.144.1:8000/attendance/history
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
  171 | 
  172 |       const firstLink = page.locator('a[href^="/grades/"]').first();
  173 |       if (await firstLink.count() > 0) {
  174 |         await firstLink.click();
  175 |         await expect(page.locator('body')).toContainText('Grade');
  176 |         await expectCsrfOnForms(page);
  177 |       }
  178 |     });
  179 |   });
  180 | 
  181 |   test.describe('Assignments', () => {
  182 |     test('assignments list and create form', async ({ page }) => {
  183 |       await page.goto('/assignments');
  184 |       expectNo5xx(networkResponses);
  185 |       await expect(page.locator('body')).toContainText('Assignments');
  186 | 
  187 |       await page.goto('/assignments/create');
  188 |       await expectCsrfOnForms(page);
  189 |     });
  190 | 
  191 |     test('first assignment detail and edit', async ({ page }) => {
  192 |       await page.goto('/assignments');
  193 |       const firstLink = page.locator('a[href^="/assignments/"]:not([href*="/edit"]):not([href*="/create"])').first();
  194 |       if (await firstLink.count() > 0) {
  195 |         await firstLink.click();
  196 |         await expect(page.locator('body')).toContainText('Assignment');
  197 |         await expectCsrfOnForms(page);
  198 | 
  199 |         const editLink = page.locator('a[href*="/edit"]').first();
  200 |         if (await editLink.count() > 0) {
  201 |           await editLink.click();
  202 |           await expectCsrfOnForms(page);
  203 |         }
  204 |       }
  205 |     });
  206 |   });
  207 | 
  208 |   test.describe('Exams', () => {
  209 |     test('exams list and create form', async ({ page }) => {
  210 |       await page.goto('/exams');
  211 |       expectNo5xx(networkResponses);
  212 |       await expect(page.locator('body')).toContainText('Exams');
  213 | 
  214 |       await page.goto('/exams/create');
  215 |       await expectCsrfOnForms(page);
  216 |     });
  217 | 
  218 |     test('first exam detail', async ({ page }) => {
  219 |       await page.goto('/exams');
  220 |       const firstLink = page.locator('a[href^="/exams/"]:not([href*="/edit"]):not([href*="/create"])').first();
  221 |       if (await firstLink.count() > 0) {
  222 |         await firstLink.click();
  223 |         await expect(page.locator('body')).toContainText('Exam');
  224 |         await expectCsrfOnForms(page);
  225 |       }
  226 |     });
  227 |   });
  228 | 
  229 |   test.describe('Announcements', () => {
  230 |     test('announcements list, empty-state, and create form', async ({ page }) => {
  231 |       await page.goto('/announcements');
  232 |       expectNo5xx(networkResponses);
  233 |       await expect(page.locator('body')).toContainText('Announcements');
  234 | 
  235 |       const emptyState = page.locator('text=No announcements yet, text=No announcements found, .empty-state');
  236 |       if (await emptyState.count() > 0) {
  237 |         await expect(emptyState.first()).toBeVisible();
  238 |       }
  239 | 
  240 |       await page.goto('/announcements/create');
  241 |       await expectCsrfOnForms(page);
  242 |     });
  243 | 
  244 |     test('first announcement detail and edit', async ({ page }) => {
  245 |       await page.goto('/announcements');
  246 |       const firstLink = page.locator('a[href^="/announcements/"]:not([href*="/edit"]):not([href*="/create"])').first();
  247 |       if (await firstLink.count() > 0) {
  248 |         await firstLink.click();
  249 |         await expect(page.locator('body')).toContainText('Announcement');
  250 |         await expectCsrfOnForms(page);
  251 | 
  252 |         const editLink = page.locator('a[href*="/edit"]').first();
  253 |         if (await editLink.count() > 0) {
  254 |           await editLink.click();
  255 |           await expectCsrfOnForms(page);
  256 |         }
  257 |       }
  258 |     });
  259 |   });
  260 | 
```