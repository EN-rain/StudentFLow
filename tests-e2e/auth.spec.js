import { test, expect } from '@playwright/test';
import {
  loginAs,
  captureConsole,
  captureNetwork,
  expectNo5xx,
  expectCsrfOnForms,
  expectNoConsoleErrors,
  annotateConsoleErrors,
  CREDENTIALS,
} from './_helpers.js';

test.describe('Authentication flows', () => {
  let consoleErrors = [];
  let networkResponses = [];

  test.beforeEach(async ({ page }) => {
    consoleErrors = captureConsole(page);
    networkResponses = captureNetwork(page);
  });

  test.afterEach(async ({}, testInfo) => {
    annotateConsoleErrors(testInfo, consoleErrors);
  });

  test('GET /login returns 200 and shows form with CSRF', async ({ page }) => {
    const res = await page.goto('/login');
    expect(res.status()).toBe(200);
    await expect(page.locator('input[name="username"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expectCsrfOnForms(page);
    expectNoConsoleErrors(consoleErrors);
  });

  test('POST /login with invalid credentials shows error', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="username"]', 'no-such-user@studentflow.local');
    await page.fill('input[name="password"]', 'WrongPass123!');

    const [res] = await Promise.all([
      page.waitForResponse((r) => r.url().includes('/login') && r.request().method() === 'POST'),
      page.click('button[data-login-submit]'),
    ]);

    expect(res.status()).toBe(302);
    await page.waitForLoadState('load');
    await expect(page.locator('.alert, .invalid-feedback, .text-danger, [data-error]').first()).toBeVisible();
  });

  test('POST /login with valid admin credentials redirects to dashboard', async ({ page }) => {
    await loginAs(page, 'admin');
    expectNo5xx(networkResponses);
    expect(page.url()).toContain('/dashboard');
    await expect(page.locator('body')).toContainText('Administrator Dashboard');
  });

  test('POST /login with valid teacher credentials redirects to dashboard', async ({ page }) => {
    await loginAs(page, 'teacher');
    expectNo5xx(networkResponses);
    expect(page.url()).toContain('/dashboard');
  });

  test('POST /login with valid student credentials shows mobile app rejection', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="username"]', CREDENTIALS.student.email);
    await page.fill('input[name="password"]', CREDENTIALS.student.password);
    await page.click('button[data-login-submit]');
    await page.waitForLoadState('load');
    await expect(page.locator('body')).toContainText(/mobile app|Students must sign in/i);
  });

  test('POST /logout clears session and returns to login', async ({ page }) => {
    await loginAs(page, 'admin');
    await page.goto('/dashboard');
    await expect(page.locator('body')).toContainText('Administrator Dashboard');

    await page.goto('/');
    const logoutForm = page.locator('form[action="/logout"]').first();
    if (await logoutForm.count() > 0) {
      await logoutForm.locator('button').click();
    } else {
      await page.request.post('/logout');
    }
    await page.goto('/dashboard');
    expect(page.url()).toContain('/login');
  });

  test('GET /forgot-password renders form with CSRF', async ({ page }) => {
    const res = await page.goto('/forgot-password');
    expect(res.status()).toBe(200);
    await expectCsrfOnForms(page);
    await expect(page.locator('input[name="email"]')).toBeVisible();
  });

  test('GET /reset-password/{token} renders form with CSRF', async ({ page }) => {
    const res = await page.goto('/reset-password/fake-token-here');
    expect(res.status() < 500).toBe(true);
    await expectCsrfOnForms(page);
  });
});
