import { expect } from '@playwright/test';

export const CREDENTIALS = {
  admin: {
    email: process.env.STUDENTFLOW_ADMIN_EMAIL || 'admin@studentflow.local',
    password: process.env.STUDENTFLOW_ADMIN_PASSWORD || 'AdminPass123!',
  },
  teacher: {
    email: process.env.STUDENTFLOW_TEACHER_EMAIL || 'john.reyes@studentflow.local',
    password: process.env.STUDENTFLOW_TEACHER_PASSWORD || 'TeacherPass123!',
  },
  student: {
    email: process.env.STUDENTFLOW_STUDENT_EMAIL || 'aaron.villanueva@studentflow.local',
    password: process.env.STUDENTFLOW_STUDENT_PASSWORD || 'StudentPass123!',
  },
};

export async function loginAs(page, role) {
  const creds = CREDENTIALS[role];
  if (!creds) throw new Error(`Unknown role: ${role}`);

  await page.goto('/login');
  await page.waitForSelector('form[data-login-form]', { state: 'visible' });
  await page.fill('input[name="username"]', creds.email);
  await page.fill('input[name="password"]', creds.password);

  const [response] = await Promise.all([
    page.waitForResponse((res) => res.url().includes('/login') && res.request().method() === 'POST'),
    page.click('button[data-login-submit]'),
  ]);

  await page.waitForURL('**/dashboard');
  return response;
}

export function captureConsole(page) {
  const errors = [];
  page.on('console', (msg) => {
    const type = msg.type();
    if (type === 'error' || type === 'warning') {
      errors.push(`[${type}] ${msg.text()}`);
    }
  });
  page.on('pageerror', (err) => {
    errors.push(`[pageerror] ${err.message}`);
  });
  return errors;
}

export function captureNetwork(page) {
  const responses = [];
  page.on('response', (res) => {
    responses.push(res);
  });
  return responses;
}

export function expectNo5xx(responses) {
  const bad = responses.filter((r) => r.status() >= 500);
  if (bad.length > 0) {
    const summary = bad.map((r) => `${r.request().method()} ${r.url()} -> ${r.status()}`).join('; ');
    throw new Error(`Received ${bad.length} 5xx response(s): ${summary}`);
  }
}

export async function expectCsrfOnForms(page) {
  const forms = await page.locator('form[method="POST"], form[method="post"]').all();
  for (const form of forms) {
    const token = await form.locator('input[name="_token"]').first();
    await expect(token, 'CSRF token missing in POST form').toBeAttached();
  }
}

export function expectNoConsoleErrors(errors) {
  const severe = errors.filter((e) => !e.toLowerCase().includes('favicon'));
  if (severe.length > 0) {
    throw new Error(`Console errors detected:\n${severe.join('\n')}`);
  }
}

export async function measurePageLoad(page) {
  const timing = await page.evaluate(() => {
    const nav = performance.getEntriesByType('navigation')[0];
    if (!nav) return null;
    return {
      domContentLoaded: nav.domContentLoadedEventEnd - nav.startTime,
      loadComplete: nav.loadEventEnd - nav.startTime,
    };
  });
  return timing;
}

export function annotateConsoleErrors(testInfo, errors) {
  for (const err of errors) {
    testInfo.annotations.push({ type: 'console-error', description: err });
  }
}

export async function fillVisibleForm(page) {
  const textInputs = await page.locator('input[type="text"][name], input[type="email"][name], input[type="password"][name], input[type="number"][name], input[type="date"][name], input[type="url"][name], textarea[name]').all();
  for (const input of textInputs) {
    const name = await input.getAttribute('name');
    const type = await input.getAttribute('type');
    if (name === '_token' || name === '_method') continue;

    let value = 'Sample value';
    if (type === 'email') value = 'sample@studentflow.local';
    else if (type === 'password') value = 'Password123!';
    else if (type === 'number') value = '10';
    else if (type === 'date') value = '2026-06-23';
    else if (type === 'url') value = 'https://example.com';
    else if (name.toLowerCase().includes('employee_number')) value = 'TCH-2026-999';
    else if (name.toLowerCase().includes('student_number')) value = '2026-0999';
    else if (name.toLowerCase().includes('contact')) value = '09171234567';

    if (await input.isVisible()) {
      await input.fill(value);
    }
  }

  const selects = await page.locator('select[name]').all();
  for (const select of selects) {
    const options = await select.locator('option').all();
    const valid = options.find(async (opt) => {
      const val = await opt.getAttribute('value');
      return val && val !== '';
    });
    if (valid && await select.isVisible()) {
      await select.selectOption(await valid.getAttribute('value'));
    }
  }
}
