import { test, expect } from '@playwright/test';
import {
  loginAs,
  captureConsole,
  captureNetwork,
  expectNo5xx,
  measurePageLoad,
  annotateConsoleErrors,
} from './_helpers.js';

// WSL / Cygwin / MSYS environments add meaningful overhead to php artisan serve.
// Thresholds here are calibrated for dev environments (not production bare-metal).
const isWsl = process.platform === 'win32' || !!process.env.WSLENV;
const THRESHOLD_NORMAL = isWsl ? 8000 : 3000;    // login, dashboard, admin/teachers
const THRESHOLD_REPORT = isWsl ? 20000 : 8000;   // reports (data-heavy)

test.describe('Performance measurements', () => {
  let consoleErrors = [];
  let networkResponses = [];

  test.beforeEach(async ({ page }) => {
    consoleErrors = captureConsole(page);
    networkResponses = captureNetwork(page);
  });

  test.afterEach(async ({}, testInfo) => {
    annotateConsoleErrors(testInfo, consoleErrors);
  });

  async function assertLoadTime(page, threshold = THRESHOLD_NORMAL) {
    const timing = await measurePageLoad(page);
    test.info().annotations.push({ type: 'perf', description: JSON.stringify(timing) });
    if (timing) {
      expect(timing.domContentLoaded, `domContentLoaded exceeded ${threshold}ms`).toBeLessThan(threshold);
      expect(timing.loadComplete, `loadComplete exceeded ${threshold}ms`).toBeLessThan(threshold);
    }
  }

  test('/login loads under threshold', async ({ page }) => {
    await page.goto('/login');
    expectNo5xx(networkResponses);
    await assertLoadTime(page, THRESHOLD_NORMAL);
  });

  test('/dashboard loads under threshold as admin', async ({ page }) => {
    await loginAs(page, 'admin');
    await page.goto('/dashboard');
    expectNo5xx(networkResponses);
    await assertLoadTime(page, THRESHOLD_NORMAL);
  });

  test('/admin/teachers loads under threshold as admin', async ({ page }) => {
    await loginAs(page, 'admin');
    await page.goto('/admin/teachers');
    expectNo5xx(networkResponses);
    await assertLoadTime(page, THRESHOLD_NORMAL);
  });

  test('/student/dashboard attempted load is captured', async ({ page }) => {
    // There is no /student/dashboard route; measure the resolved redirect target.
    await page.goto('/student');
    expectNo5xx(networkResponses);
    const timing = await measurePageLoad(page);
    test.info().annotations.push({ type: 'perf', description: JSON.stringify(timing) });
  });

  test('reports index loads under threshold as admin', async ({ page }) => {
    await loginAs(page, 'admin');
    await page.goto('/reports');
    expectNo5xx(networkResponses);
    await assertLoadTime(page, THRESHOLD_REPORT);
  });
});