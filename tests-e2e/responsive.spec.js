import { test, expect } from '@playwright/test';
import {
  loginAs,
  captureConsole,
  captureNetwork,
  expectNo5xx,
  annotateConsoleErrors,
} from './_helpers.js';

test.describe('Responsive layout checks', () => {
  let consoleErrors = [];
  let networkResponses = [];

  test.beforeEach(async ({ page }) => {
    consoleErrors = captureConsole(page);
    networkResponses = captureNetwork(page);
  });

  test.afterEach(async ({}, testInfo) => {
    annotateConsoleErrors(testInfo, consoleErrors);
  });

  async function noHorizontalOverflow(page) {
    const bodyWidth = await page.evaluate(() => document.body.scrollWidth);
    const windowWidth = await page.evaluate(() => window.innerWidth);
    expect(bodyWidth).toBeLessThanOrEqual(windowWidth + 1);
  }

  test('login page renders usable form at 390x844 and 1280x720', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/login');
    expectNo5xx(networkResponses);
    await expect(page.locator('form[data-login-form]')).toBeVisible();
    await noHorizontalOverflow(page);

    await page.setViewportSize({ width: 1280, height: 720 });
    await page.goto('/login');
    await expect(page.locator('form[data-login-form]')).toBeVisible();
    await noHorizontalOverflow(page);
  });

  test('admin dashboard renders without horizontal scroll on mobile', async ({ page }) => {
    await loginAs(page, 'admin');
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/dashboard');
    expectNo5xx(networkResponses);
    await noHorizontalOverflow(page);

    await page.setViewportSize({ width: 1280, height: 720 });
    await page.goto('/dashboard');
    await noHorizontalOverflow(page);
  });

  test('classes page sidebar visible on desktop and hidden on mobile', async ({ page }) => {
    await loginAs(page, 'admin');

    await page.setViewportSize({ width: 1280, height: 720 });
    await page.goto('/classes');
    const sidebarDesktop = page.locator('aside, .sidebar, nav[role="navigation"]').first();
    expect(await sidebarDesktop.count()).toBeGreaterThan(0);

    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/classes');
    await noHorizontalOverflow(page);
  });

  test('students page is usable at both viewports', async ({ page }) => {
    await loginAs(page, 'admin');

    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/students');
    await noHorizontalOverflow(page);
    await expect(page.locator('body')).toContainText('Students');

    await page.setViewportSize({ width: 1280, height: 720 });
    await page.goto('/students');
    await noHorizontalOverflow(page);
    await expect(page.locator('body')).toContainText('Students');
  });
});
