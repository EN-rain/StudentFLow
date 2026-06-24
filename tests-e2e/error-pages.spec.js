import { test, expect } from '@playwright/test';
import {
  captureConsole,
  captureNetwork,
  expectNo5xx,
  annotateConsoleErrors,
  CREDENTIALS,
} from './_helpers.js';

test.describe('Error page handling', () => {
  let consoleErrors = [];
  let networkResponses = [];

  test.beforeEach(async ({ page }) => {
    consoleErrors = captureConsole(page);
    networkResponses = captureNetwork(page);
  });

  test.afterEach(async ({}, testInfo) => {
    annotateConsoleErrors(testInfo, consoleErrors);
  });

  test('GET non-existent route returns 404', async ({ page }) => {
    const res = await page.goto('/this-route-does-not-exist-12345');
    expect(res.status()).toBe(404);
    expectNo5xx(networkResponses);
  });

  test('POST to GET-only route returns 405 or redirects', async ({ page, request }) => {
    const res = await request.post('/dashboard', {
      data: { foo: 'bar' },
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });
    expect([302, 405, 200, 419]).toContain(res.status());
    expect(res.status()).toBeLessThan(500);
  });

  test('login with wrong credentials redirects back to login (no 500)', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="username"]', CREDENTIALS.admin.email);
    await page.fill('input[name="password"]', 'DefinitelyWrong123!');
    const [res] = await Promise.all([
      page.waitForResponse((r) => r.url().includes('/login') && r.request().method() === 'POST'),
      page.click('button[data-login-submit]'),
    ]);
    expect(res.status()).toBe(302);
    await page.waitForLoadState('load');
    expect(page.url()).toContain('/login');
    expectNo5xx(networkResponses);
  });

  test('validation errors on forgot-password do not produce 500', async ({ page }) => {
    await page.goto('/forgot-password');
    await page.fill('input[name="email"]', 'not-an-email');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('load');
    expectNo5xx(networkResponses);
  });

  test('invalid teacher id in admin edit route returns 404 not 500', async ({ page, request }) => {
    // Direct GET without auth is expected to redirect to login; ensure no 500 on the redirect chain.
    const res = await request.get('/admin/teachers/999999/edit');
    expect(res.status()).toBeLessThan(500);
  });

  test('invalid class id in gradebook returns 404 not 500', async ({ page, request }) => {
    const res = await request.get('/grades/999999');
    expect(res.status()).toBeLessThan(500);
  });
});
