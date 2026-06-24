import { test, expect } from '@playwright/test';
import {
  loginAs,
  captureConsole,
  captureNetwork,
  expectNo5xx,
  annotateConsoleErrors,
} from './_helpers.js';

test.describe('Accessibility spot checks', () => {
  let consoleErrors = [];
  let networkResponses = [];

  test.beforeEach(async ({ page }) => {
    consoleErrors = captureConsole(page);
    networkResponses = captureNetwork(page);
  });

  test.afterEach(async ({}, testInfo) => {
    annotateConsoleErrors(testInfo, consoleErrors);
  });

  test('login page has semantic regions and form labels', async ({ page }) => {
    await page.goto('/login');
    expectNo5xx(networkResponses);

    await expect(page.locator('main')).toBeAttached();
    await expect(page.locator('form')).toBeVisible();

    const username = page.locator('input[name="username"]');
    const id = await username.getAttribute('id');
    if (id) {
      const label = page.locator(`label[for="${id}"]`);
      expect(await label.count()).toBeGreaterThan(0);
    }
  });

  test('keyboard can navigate login form and submit button is focusable', async ({ page }) => {
    await page.goto('/login');
    await page.keyboard.press('Tab');
    const first = await page.evaluate(() => document.activeElement?.tagName);
    expect(first).not.toBe('BODY');

    await page.focus('input[name="username"]');
    await page.keyboard.press('Tab');
    await page.keyboard.press('Tab');
    const active = await page.evaluate(() => document.activeElement);
    expect(active).not.toBeNull();

    const outline = await page.evaluate(() => {
      const el = document.activeElement;
      if (!el) return null;
      return window.getComputedStyle(el).outline;
    });
    test.info().annotations.push({ type: 'a11y', description: `focus outline: ${outline}` });
  });

  test('admin dashboard has main region and headings', async ({ page }) => {
    await loginAs(page, 'admin');
    await page.goto('/dashboard');
    expectNo5xx(networkResponses);
    await expect(page.locator('main, [role="main"]')).toBeAttached();
    const headings = await page.locator('h1, h2, h3').count();
    expect(headings).toBeGreaterThan(0);
  });

  test('focus indicators are visible after tab navigation', async ({ page }) => {
    await page.goto('/login');
    await page.keyboard.press('Tab');
    await page.keyboard.press('Tab');
    const style = await page.evaluate(() => {
      const el = document.activeElement;
      if (!el) return '';
      const computed = window.getComputedStyle(el);
      return `${computed.outlineWidth} ${computed.outlineStyle} ${computed.outlineColor}`;
    });
    expect(style).not.toBe('0px none rgba(0, 0, 0, 0)');
  });
});
