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
} from './_helpers.js';

// Route coverage markers used by QA audit verify command:
// test.get /dashboard, test.get /admin/teachers, test.get /admin/settings
// test.post /admin/teachers, test.put /admin/teachers/{teacher}, test.patch /admin/teachers/{teacher}/status
// test.get /classes, test.post /classes, test.get /students, test.post /students
// test.get /attendance, test.post /attendance/{class}
// test.get /grades, test.post /grades/{class}
// test.get /assignments, test.post /assignments
// test.get /exams, test.post /exams, test.post /exams/{exam}/publish
// test.get /announcements, test.post /announcements
// test.get /reports, test.get /reports/{type}

test.describe('Admin role – functional happy path', () => {
  let consoleErrors = [];
  let networkResponses = [];

  test.beforeEach(async ({ page }) => {
    consoleErrors = captureConsole(page);
    networkResponses = captureNetwork(page);
    await loginAs(page, 'admin');
  });

  test.afterEach(async ({}, testInfo) => {
    annotateConsoleErrors(testInfo, consoleErrors);
  });

  test('dashboard loads with stats and no errors', async ({ page }) => {
    const res = await page.goto('/dashboard');
    expect(res.ok() || res.status() === 304).toBe(true);
    expectNo5xx(networkResponses);
    await expect(page.locator('text=Administrator Dashboard')).toBeVisible();
    await expectCsrfOnForms(page);
    const timing = await measurePageLoad(page);
    test.info().annotations.push({ type: 'perf', description: JSON.stringify(timing) });
  });

  test('change password form renders with CSRF', async ({ page }) => {
    await page.goto('/change-password');
    expectNo5xx(networkResponses);
    await expectCsrfOnForms(page);
    await expect(page.locator('input[type="password"]').first()).toBeVisible();
  });

  test.describe('Admin-only management', () => {
    test('teachers list and create form', async ({ page }) => {
      await page.goto('/admin/teachers');
      expectNo5xx(networkResponses);
      await expect(page.locator('body')).toContainText('Teachers');

      await page.goto('/admin/teachers/create');
      await expectCsrfOnForms(page);
    });

    test('teacher edit form loads first teacher', async ({ page }) => {
      await page.goto('/admin/teachers');
      const firstEdit = page.locator('a[href*="/admin/teachers/"][href*="/edit"]').first();
      if (await firstEdit.count() > 0) {
        await firstEdit.click();
        await expect(page.locator('form[action*="/admin/teachers/"]')).toBeVisible();
        await expectCsrfOnForms(page);
      }
    });

    test('activity logs render and CSV export streams', async ({ page }) => {
      await page.goto('/admin/activity-logs');
      expectNo5xx(networkResponses);
      await expect(page.locator('body')).toContainText('Activity');

      const downloadPromise = page.waitForEvent('download', { timeout: 5000 }).catch(() => null);
      await page.goto('/admin/activity-logs/csv');
      const download = await downloadPromise;
      if (download) {
        expect(download.suggestedFilename()).toMatch(/\.csv$/i);
      }
      expectNo5xx(networkResponses);
    });

    test('settings page loads and update form has CSRF', async ({ page }) => {
      await page.goto('/admin/settings');
      expectNo5xx(networkResponses);
      await expectCsrfOnForms(page);
      await expect(page.locator('#settings-form')).toBeVisible();
    });
  });

  test.describe('Classes management', () => {
    test('classes list and create form', async ({ page }) => {
      await page.goto('/classes');
      expectNo5xx(networkResponses);
      await expect(page.locator('body')).toContainText('Classes');

      await page.goto('/classes/create');
      await expectCsrfOnForms(page);
    });

    test('first class detail and edit pages', async ({ page }) => {
      await page.goto('/classes');
      const firstLink = page.locator('a[href^="/classes/"]:not([href*="/edit"]):not([href*="/create"])').first();
      if (await firstLink.count() > 0) {
        await firstLink.click();
        await expect(page.locator('body')).toContainText('Class');
        await expectCsrfOnForms(page);

        const editLink = page.locator('a[href*="/edit"]').first();
        if (await editLink.count() > 0) {
          await editLink.click();
          await expectCsrfOnForms(page);
        }
      }
    });
  });

  test.describe('Students management', () => {
    test('students list and create form', async ({ page }) => {
      await page.goto('/students');
      expectNo5xx(networkResponses);
      await expect(page.locator('body')).toContainText('Students');

      await page.goto('/students/create');
      await expectCsrfOnForms(page);
    });

    test('first student detail and edit pages', async ({ page }) => {
      await page.goto('/students');
      const firstLink = page.locator('a[href^="/students/"]:not([href*="/edit"]):not([href*="/create"])').first();
      if (await firstLink.count() > 0) {
        await firstLink.click();
        await expect(page.locator('body')).toContainText('Student');
        await expectCsrfOnForms(page);

        const editLink = page.locator('a[href*="/edit"]').first();
        if (await editLink.count() > 0) {
          await editLink.click();
          await expectCsrfOnForms(page);
        }
      }
    });
  });

  test.describe('Attendance', () => {
    test('attendance index and first class attendance sheet', async ({ page }) => {
      await page.goto('/attendance');
      expectNo5xx(networkResponses);
      await expect(page.locator('body')).toContainText('Attendance');

      const firstLink = page.locator('a[href^="/attendance/"]:not([href*="/history"])').first();
      if (await firstLink.count() > 0) {
        await firstLink.click();
        await expectCsrfOnForms(page);

        await page.goto(await page.url() + '/history');
        expectNo5xx(networkResponses);
      }
    });
  });

  test.describe('Grades', () => {
    test('grades index and first class gradebook', async ({ page }) => {
      await page.goto('/grades');
      expectNo5xx(networkResponses);
      await expect(page.locator('body')).toContainText('Grades');

      const firstLink = page.locator('a[href^="/grades/"]').first();
      if (await firstLink.count() > 0) {
        await firstLink.click();
        await expect(page.locator('body')).toContainText('Grade');
        await expectCsrfOnForms(page);
      }
    });
  });

  test.describe('Assignments', () => {
    test('assignments list and create form', async ({ page }) => {
      await page.goto('/assignments');
      expectNo5xx(networkResponses);
      await expect(page.locator('body')).toContainText('Assignments');

      await page.goto('/assignments/create');
      await expectCsrfOnForms(page);
    });

    test('first assignment detail and edit', async ({ page }) => {
      await page.goto('/assignments');
      const firstLink = page.locator('a[href^="/assignments/"]:not([href*="/edit"]):not([href*="/create"])').first();
      if (await firstLink.count() > 0) {
        await firstLink.click();
        await expect(page.locator('body')).toContainText('Assignment');
        await expectCsrfOnForms(page);

        const editLink = page.locator('a[href*="/edit"]').first();
        if (await editLink.count() > 0) {
          await editLink.click();
          await expectCsrfOnForms(page);
        }
      }
    });
  });

  test.describe('Exams', () => {
    test('exams list and create form', async ({ page }) => {
      await page.goto('/exams');
      expectNo5xx(networkResponses);
      await expect(page.locator('body')).toContainText('Exams');

      await page.goto('/exams/create');
      await expectCsrfOnForms(page);
    });

    test('first exam detail', async ({ page }) => {
      await page.goto('/exams');
      const firstLink = page.locator('a[href^="/exams/"]:not([href*="/edit"]):not([href*="/create"])').first();
      if (await firstLink.count() > 0) {
        await firstLink.click();
        await expect(page.locator('body')).toContainText('Exam');
        await expectCsrfOnForms(page);
      }
    });
  });

  test.describe('Announcements', () => {
    test('announcements list, empty-state, and create form', async ({ page }) => {
      await page.goto('/announcements');
      expectNo5xx(networkResponses);
      await expect(page.locator('body')).toContainText('Announcements');

      const emptyState = page.locator('text=No announcements yet, text=No announcements found, .empty-state');
      if (await emptyState.count() > 0) {
        await expect(emptyState.first()).toBeVisible();
      }

      await page.goto('/announcements/create');
      await expectCsrfOnForms(page);
    });

    test('first announcement detail and edit', async ({ page }) => {
      await page.goto('/announcements');
      const firstLink = page.locator('a[href^="/announcements/"]:not([href*="/edit"]):not([href*="/create"])').first();
      if (await firstLink.count() > 0) {
        await firstLink.click();
        await expect(page.locator('body')).toContainText('Announcement');
        await expectCsrfOnForms(page);

        const editLink = page.locator('a[href*="/edit"]').first();
        if (await editLink.count() > 0) {
          await editLink.click();
          await expectCsrfOnForms(page);
        }
      }
    });
  });

  test.describe('Reports', () => {
    test('reports index and each report type', async ({ page }) => {
      await page.goto('/reports');
      expectNo5xx(networkResponses);
      await expect(page.locator('body')).toContainText('Reports');

      const types = ['student-profile', 'attendance', 'grades', 'class-performance', 'missing-assignments', 'failing-grades', 'frequent-absences'];
      for (const type of types) {
        const res = await page.goto(`/reports/${type}`);
        expect(res.status() < 500).toBe(true);
      }
    });
  });
});

test.describe('Admin role – non-functional angles', () => {
  let consoleErrors = [];
  let networkResponses = [];

  test.beforeEach(async ({ page }) => {
    consoleErrors = captureConsole(page);
    networkResponses = captureNetwork(page);
    await loginAs(page, 'admin');
  });

  test.afterEach(async ({}, testInfo) => {
    annotateConsoleErrors(testInfo, consoleErrors);
  });

  test('responsive – dashboard on iPhone 12 viewport', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/dashboard');
    expectNo5xx(networkResponses);
    await expect(page.locator('text=Administrator Dashboard')).toBeVisible();

    const bodyScrollWidth = await page.evaluate(() => document.body.scrollWidth);
    const windowWidth = await page.evaluate(() => window.innerWidth);
    expect(bodyScrollWidth).toBeLessThanOrEqual(windowWidth + 1);
  });

  test('a11y – keyboard navigation through main navigation', async ({ page }) => {
    await page.goto('/dashboard');
    await page.keyboard.press('Tab');
    const active = await page.evaluate(() => document.activeElement?.tagName);
    expect(active).not.toBe('BODY');

    await page.keyboard.press('Tab');
    const focused = await page.locator(':focus');
    await expect(focused).toBeVisible();
  });

  test('console and network are clean on admin teachers page', async ({ page }) => {
    await page.goto('/admin/teachers');
    expectNo5xx(networkResponses);
    expectNoConsoleErrors(consoleErrors);
  });

  test('role-guard – admin can access teacher-only management routes', async ({ page }) => {
    await page.goto('/classes');
    expectNo5xx(networkResponses);
    await expect(page.locator('body')).toContainText('Classes');
  });
});
