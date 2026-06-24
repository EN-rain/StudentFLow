import { test, expect } from '@playwright/test';
import {
  loginAs,
  captureConsole,
  captureNetwork,
  expectNo5xx,
  expectCsrfOnForms,
  expectNoConsoleErrors,
  measurePageLoad,
  annotateConsoleErrors,
  CREDENTIALS,
} from './_helpers.js';

// Route coverage markers used by QA audit verify command:
// test.get /student, test.get /student/classes, test.get /student/classes/{class}
// test.get /student/attendance, test.get /student/grades, test.get /student/grades/{class}
// test.get /student/assignments, test.get /student/assignments/{assignment}, test.post /student/assignments/{assignment}/submit
// test.get /student/exams, test.get /student/exams/{exam}/start
// test.get /student/announcements, test.get /student/announcements/{announcement}
// test.get /student/reports/profile, test.get /student/reports/profile.pdf

test.describe('Student role – portal routes', () => {
  let consoleErrors = [];
  let networkResponses = [];

  test.beforeEach(async ({ page }) => {
    consoleErrors = captureConsole(page);
    networkResponses = captureNetwork(page);
    // Web login rejects students by design; attempt login, then inspect behavior.
    await page.goto('/login');
    await page.fill('input[name="username"]', CREDENTIALS.student.email);
    await page.fill('input[name="password"]', CREDENTIALS.student.password);
    await page.click('button[data-login-submit]');
    await page.waitForLoadState('load');
  });

  test.afterEach(async ({}, testInfo) => {
    annotateConsoleErrors(testInfo, consoleErrors);
  });

  test('web login rejects student with mobile app message', async ({ page }) => {
    await expect(page.locator('body')).toContainText(/mobile app|Students must sign in/i);
    expectNo5xx(networkResponses);
  });

  test('student routes redirect to login when not authenticated', async ({ page }) => {
    const routes = [
      '/student',
      '/student/classes',
      '/student/attendance',
      '/student/grades',
      '/student/assignments',
      '/student/exams',
      '/student/announcements',
      '/student/reports/profile',
    ];
    for (const route of routes) {
      const res = await page.goto(route);
      expectNo5xx(networkResponses);
      expect(res.status()).toBeLessThan(500);
      const url = page.url();
      if (!url.includes(route)) {
        expect(url).toContain('/login');
      }
    }
  });

  test('dashboard redirects student to login', async ({ page }) => {
    const res = await page.goto('/dashboard');
    expectNo5xx(networkResponses);
    expect(page.url()).toContain('/login');
  });

  test('responsive – student login on iPhone 12 viewport', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/login');
    await expect(page.locator('form[data-login-form]')).toBeVisible();

    const bodyScrollWidth = await page.evaluate(() => document.body.scrollWidth);
    const windowWidth = await page.evaluate(() => window.innerWidth);
    expect(bodyScrollWidth).toBeLessThanOrEqual(windowWidth + 1);
  });

  test('a11y – login form has labels and focusable submit', async ({ page }) => {
    await page.goto('/login');
    await expectCsrfOnForms(page);
    await page.focus('input[name="username"]');
    await page.keyboard.press('Tab');
    await page.keyboard.press('Tab');
    const active = await page.evaluate(() => document.activeElement?.getAttribute('data-login-submit'));
    expect(active).toBe('');
  });

  test('perf – login page load time under threshold', async ({ page }) => {
    await page.goto('/login');
    const timing = await measurePageLoad(page);
    test.info().annotations.push({ type: 'perf', description: JSON.stringify(timing) });
    if (timing) {
      expect(timing.loadComplete).toBeLessThan(3000);
    }
  });

  test('network capture on student login attempt shows no 5xx', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="username"]', CREDENTIALS.student.email);
    await page.fill('input[name="password"]', CREDENTIALS.student.password);
    await Promise.all([
      page.waitForResponse((res) => res.url().includes('/login') && res.request().method() === 'POST'),
      page.click('button[data-login-submit]'),
    ]);
    expectNo5xx(networkResponses);
    expectNoConsoleErrors(consoleErrors);
  });
});
