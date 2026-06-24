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
    const errorSelector = '.invalid-feedback, .text-danger, .alert-danger, .alert-warning, [data-error], .alert, .error';
    await page.waitForLoadState('networkidle');
    await expect(page.locator(errorSelector).first()).toBeVisible({ timeout: 10000 });
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
    await page.click('button');
    await expectValidationError(page);
  });

  test('change-password – empty fields show validation error', async ({ page }) => {
    await loginAs(page, 'admin');
    await page.goto('/change-password');
    // Confirm we are on the change-password form (not redirected to login)
    await expect(page.locator('input[name="current_password"]')).toBeVisible({ timeout: 5000 });
    const token = await page.locator('form[action="/change-password"] input[name="_token"]').getAttribute('value');

    // POST via Playwright's API client (shares cookies with the page)
    const response = await page.request.post('http://127.0.0.1:8000/change-password', {
      form: { _token: token, current_password: '', new_password: '', new_password_confirmation: '' },
      maxRedirects: 0,
    });
    // Laravel validation failure: 302 redirect back (not 200 success, not 419 CSRF fail)
    expect(response.status()).toBe(302);
    expect(response.headers()['location']).toBeTruthy();

    // Navigate to the redirected page to verify the error renders
    await page.goto('/change-password');
    await expect(page.locator('.alert-danger').first()).toBeVisible({ timeout: 10000 });
  });

  test('change-password – mismatched new password shows error', async ({ page }) => {
    await loginAs(page, 'admin');
    await page.goto('/change-password');
    // Confirm we are on the change-password form (not redirected to login)
    await expect(page.locator('input[name="current_password"]')).toBeVisible({ timeout: 5000 });
    const token = await page.locator('form[action="/change-password"] input[name="_token"]').getAttribute('value');

    // POST via Playwright's API client (shares cookies with the page)
    const response = await page.request.post('http://127.0.0.1:8000/change-password', {
      form: {
        _token: token,
        current_password: CREDENTIALS.admin.password,
        new_password: 'NewPass123!',
        new_password_confirmation: 'DifferentPass123!',
      },
      maxRedirects: 0,
    });
    // Laravel validation failure: 302 redirect back (not 200 success, not 419 CSRF fail)
    expect(response.status()).toBe(302);
    expect(response.headers()['location']).toBeTruthy();

    // Navigate to the redirected page to verify the error renders
    await page.goto('/change-password');
    await expect(page.locator('.alert-danger').first()).toBeVisible({ timeout: 10000 });
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
