# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: negative-path.spec.js >> Negative path validation >> login – empty fields show validation error
- Location: negative-path.spec.js:29:3

# Error details

```
Error: expect(locator).toBeVisible() failed

Locator: locator('.invalid-feedback, .text-danger, .alert-danger, [data-error], .alert').first()
Expected: visible
Timeout: 5000ms
Error: element(s) not found

Call log:
  - Expect "toBeVisible" with timeout 5000ms
  - waiting for locator('.invalid-feedback, .text-danger, .alert-danger, [data-error], .alert').first()

```

```yaml
- main:
  - heading "StudentFlow" [level=3]
  - text: Username or Email
  - textbox
  - text: Password
  - textbox
  - button "Sign in"
  - link "Forgot password?":
    - /url: /forgot-password
```

# Test source

```ts
  1   | import { test, expect } from '@playwright/test';
  2   | import {
  3   |   loginAs,
  4   |   captureConsole,
  5   |   captureNetwork,
  6   |   expectNo5xx,
  7   |   annotateConsoleErrors,
  8   |   CREDENTIALS,
  9   | } from './_helpers.js';
  10  | 
  11  | test.describe('Negative path validation', () => {
  12  |   let consoleErrors = [];
  13  |   let networkResponses = [];
  14  | 
  15  |   test.beforeEach(async ({ page }) => {
  16  |     consoleErrors = captureConsole(page);
  17  |     networkResponses = captureNetwork(page);
  18  |   });
  19  | 
  20  |   test.afterEach(async ({}, testInfo) => {
  21  |     annotateConsoleErrors(testInfo, consoleErrors);
  22  |   });
  23  | 
  24  |   async function expectValidationError(page) {
  25  |     const errorSelector = '.invalid-feedback, .text-danger, .alert-danger, [data-error], .alert';
> 26  |     await expect(page.locator(errorSelector).first()).toBeVisible();
      |                                                       ^ Error: expect(locator).toBeVisible() failed
  27  |   }
  28  | 
  29  |   test('login – empty fields show validation error', async ({ page }) => {
  30  |     await page.goto('/login');
  31  |     await page.fill('input[name="username"]', '');
  32  |     await page.fill('input[name="password"]', '');
  33  |     await page.click('button[data-login-submit]');
  34  |     await page.waitForLoadState('load');
  35  |     await expectValidationError(page);
  36  |   });
  37  | 
  38  |   test('login – invalid email format rejected', async ({ page }) => {
  39  |     await page.goto('/login');
  40  |     await page.fill('input[name="username"]', 'not-an-email');
  41  |     await page.fill('input[name="password"]', 'password');
  42  |     await page.click('button[data-login-submit]');
  43  |     await page.waitForLoadState('load');
  44  |     await expectValidationError(page);
  45  |   });
  46  | 
  47  |   test('forgot-password – empty email shows validation error', async ({ page }) => {
  48  |     await page.goto('/forgot-password');
  49  |     await page.fill('input[name="email"]', '');
  50  |     await page.click('button[type="submit"]');
  51  |     await page.waitForLoadState('load');
  52  |     await expectValidationError(page);
  53  |   });
  54  | 
  55  |   test('forgot-password – malformed email shows validation error', async ({ page }) => {
  56  |     await page.goto('/forgot-password');
  57  |     await page.fill('input[name="email"]', 'bad-email');
  58  |     await page.click('button[type="submit"]');
  59  |     await page.waitForLoadState('load');
  60  |     await expectValidationError(page);
  61  |   });
  62  | 
  63  |   test('reset-password – empty/malformed fields show validation error', async ({ page }) => {
  64  |     await page.goto('/reset-password/fake-token');
  65  |     await page.fill('input[name="email"]', 'bad-email');
  66  |     await page.fill('input[name="password"]', '123');
  67  |     await page.fill('input[name="password_confirmation"]', '456');
  68  |     await page.click('button[type="submit"]');
  69  |     await page.waitForLoadState('load');
  70  |     await expectValidationError(page);
  71  |   });
  72  | 
  73  |   test('change-password – empty fields show validation error', async ({ page }) => {
  74  |     await loginAs(page, 'admin');
  75  |     await page.goto('/change-password');
  76  |     await page.fill('input[name="current_password"]', '');
  77  |     await page.fill('input[name="password"]', '');
  78  |     await page.fill('input[name="password_confirmation"]', '');
  79  |     await page.click('button[type="submit"]');
  80  |     await page.waitForLoadState('load');
  81  |     await expectValidationError(page);
  82  |   });
  83  | 
  84  |   test('change-password – mismatched new password shows error', async ({ page }) => {
  85  |     await loginAs(page, 'admin');
  86  |     await page.goto('/change-password');
  87  |     await page.fill('input[name="current_password"]', CREDENTIALS.admin.password);
  88  |     await page.fill('input[name="password"]', 'NewPass123!');
  89  |     await page.fill('input[name="password_confirmation"]', 'DifferentPass123!');
  90  |     await page.click('button[type="submit"]');
  91  |     await page.waitForLoadState('load');
  92  |     await expectValidationError(page);
  93  |   });
  94  | 
  95  |   test('teacher-setup – empty fields show validation error', async ({ page }) => {
  96  |     await page.goto('/teacher/setup/fake-token');
  97  |     const inputs = await page.locator('input[name], select[name], textarea[name]').all();
  98  |     for (const input of inputs) {
  99  |       const name = await input.getAttribute('name');
  100 |       if (name === '_token') continue;
  101 |       const type = await input.getAttribute('type');
  102 |       if (type === 'password' || type === 'email' || type === 'text') {
  103 |         if (await input.isVisible()) await input.fill('');
  104 |       }
  105 |     }
  106 |     await page.click('button[type="submit"]');
  107 |     await page.waitForLoadState('load');
  108 |     await expectValidationError(page);
  109 |   });
  110 | 
  111 |   test('no 5xx on validation failures', async ({ page }) => {
  112 |     await page.goto('/forgot-password');
  113 |     await page.fill('input[name="email"]', 'bad');
  114 |     await page.click('button[type="submit"]');
  115 |     await page.waitForLoadState('load');
  116 |     expectNo5xx(networkResponses);
  117 |   });
  118 | });
  119 | 
```