import { test, expect } from '@playwright/test';
import {
  loginAs,
  captureConsole,
  captureNetwork,
  expectNo5xx,
  annotateConsoleErrors,
  CREDENTIALS,
} from './_helpers.js';

test.describe('Negative path validation', () => {
  let consoleErrors = [];
  let networkResponses = [];

  test.beforeEach(async ({ page }) => {
    consoleErrors = captureConsole(page);
    networkResponses = captureNetwork(page);
  });

  test.afterEach(async ({}, testInfo) => {
    annotateConsoleErrors(testInfo, consoleErrors);
  });

  async function expectValidationError(page) {
    const errorSelector = '.invalid-feedback, .text-danger, .alert-danger, [data-error], .alert';
    await expect(page.locator(errorSelector).first()).toBeVisible();
  }

  test('login – empty fields show validation error', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="username"]', '');
    await page.fill('input[name="password"]', '');
    await page.click('button[data-login-submit]');
    await page.waitForLoadState('load');
    await expectValidationError(page);
  });

  test('login – invalid email format rejected', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="username"]', 'not-an-email');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[data-login-submit]');
    await page.waitForLoadState('load');
    await expectValidationError(page);
  });

  test('forgot-password – empty email shows validation error', async ({ page }) => {
    await page.goto('/forgot-password');
    await page.fill('input[name="email"]', '');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('load');
    await expectValidationError(page);
  });

  test('forgot-password – malformed email shows validation error', async ({ page }) => {
    await page.goto('/forgot-password');
    await page.fill('input[name="email"]', 'bad-email');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('load');
    await expectValidationError(page);
  });

  test('reset-password – empty/malformed fields show validation error', async ({ page }) => {
    await page.goto('/reset-password/fake-token');
    await page.fill('input[name="email"]', 'bad-email');
    await page.fill('input[name="password"]', '123');
    await page.fill('input[name="password_confirmation"]', '456');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('load');
    await expectValidationError(page);
  });

  test('change-password – empty fields show validation error', async ({ page }) => {
    await loginAs(page, 'admin');
    await page.goto('/change-password');
    await page.fill('input[name="current_password"]', '');
    await page.fill('input[name="new_password"]', '');
    await page.fill('input[name="new_password_confirmation"]', '');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('load');
    await expectValidationError(page);
  });

  test('change-password – mismatched new password shows error', async ({ page }) => {
    await loginAs(page, 'admin');
    await page.goto('/change-password');
    await page.fill('input[name="current_password"]', CREDENTIALS.admin.password);
    await page.fill('input[name="new_password"]', 'NewPass123!');
    await page.fill('input[name="new_password_confirmation"]', 'DifferentPass123!');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('load');
    await expectValidationError(page);
  });

  test('teacher-setup – empty fields show validation error', async ({ page }) => {
    await page.goto('/teacher/setup/fake-token');
    const inputs = await page.locator('input[name], select[name], textarea[name]').all();
    for (const input of inputs) {
      const name = await input.getAttribute('name');
      if (name === '_token') continue;
      const type = await input.getAttribute('type');
      if (type === 'password' || type === 'email' || type === 'text') {
        if (await input.isVisible()) await input.fill('');
      }
    }
    await page.click('button[type="submit"]');
    await page.waitForLoadState('load');
    await expectValidationError(page);
  });

  test('no 5xx on validation failures', async ({ page }) => {
    await page.goto('/forgot-password');
    await page.fill('input[name="email"]', 'bad');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('load');
    expectNo5xx(networkResponses);
  });
});
