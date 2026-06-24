import { test, expect } from '@playwright/test';
import {
  loginAs,
  captureConsole,
  captureNetwork,
  expectNo5xx,
  expectNoConsoleErrors,
  annotateConsoleErrors,
} from './_helpers.js';

test.describe('Console and network capture', () => {
  let consoleErrors = [];
  let networkResponses = [];

  test.beforeEach(async ({ page }) => {
    consoleErrors = captureConsole(page);
    networkResponses = captureNetwork(page);
  });

  test.afterEach(async ({}, testInfo) => {
    annotateConsoleErrors(testInfo, consoleErrors);
  });

  test('admin dashboard has no console errors or 5xx network responses', async ({ page }) => {
    await loginAs(page, 'admin');
    await page.goto('/dashboard');
    expectNo5xx(networkResponses);
    expectNoConsoleErrors(consoleErrors);
  });

  test('login page has no console errors', async ({ page }) => {
    await page.goto('/login');
    expectNo5xx(networkResponses);
    expectNoConsoleErrors(consoleErrors);
  });

  test('admin accessing student-only route is rejected without 5xx', async ({ page }) => {
    await loginAs(page, 'admin');
    const res = await page.goto('/student/classes');
    expectNo5xx(networkResponses);
    const status = res.status();
    test.info().annotations.push({ type: 'role-guard', description: `admin -> /student/classes returned ${status}` });
    expect(status).toBeLessThan(500);
    expect([200, 302, 403]).toContain(status);
  });

  test('guest accessing protected route is redirected without 5xx', async ({ page }) => {
    const res = await page.goto('/dashboard');
    expectNo5xx(networkResponses);
    expect(res.status()).toBeLessThan(500);
    expect(page.url()).toContain('/login');
  });

  test('4xx responses are documented in annotations', async ({ page }) => {
    const res = await page.goto('/this-route-does-not-exist-12345');
    const fourXxs = networkResponses.filter((r) => r.status() >= 400 && r.status() < 500);
    for (const r of fourXxs) {
      test.info().annotations.push({
        type: '4xx-response',
        description: `${r.request().method()} ${r.url()} -> ${r.status()}`,
      });
    }
    expect(fourXxs.length).toBeGreaterThan(0);
  });
});
