import { test, expect } from "@playwright/test";

const FRONTEND_URL = process.env.FRONTEND_URL || "http://localhost:3000";

test.describe("StudentFlow Web Auth", () => {
  test("shows login page at /login", async ({ page }) => {
    await page.goto(`${FRONTEND_URL}/login`);
    await expect(page.locator("h1")).toContainText("Sign in");
  });

  test("shows register page at /register", async ({ page }) => {
    await page.goto(`${FRONTEND_URL}/register`);
    await expect(page.locator("h1")).toContainText("Sign up");
  });

  test("redirects to login when unauthenticated", async ({ page }) => {
    await page.goto(`${FRONTEND_URL}/dashboard`);
    await page.waitForURL("**/login");
  });

  test("login sets session cookie and redirects to dashboard", async ({ page }) => {
    await page.goto(`${FRONTEND_URL}/login`);
    await page.fill('input[name="username"]', "admin");
    await page.fill('input[name="password"]', "AdminPass123!");
    await page.click('button[type="submit"]');

    await page.waitForURL("**/dashboard/**");

    const cookies = await page.context().cookies();
    const sessionCookie = cookies.find((c) => c.name.includes("session"));
    expect(sessionCookie).toBeDefined();
    expect(sessionCookie?.value).toBeTruthy();
  });

  test("session persists across page navigation after login", async ({ page }) => {
    await page.goto(`${FRONTEND_URL}/login`);
    await page.fill('input[name="username"]', "aaronvillanueva001");
    await page.fill('input[name="password"]', "StudentPass123!");
    await page.click('button[type="submit"]');
    await page.waitForURL("**/dashboard/**");

    await page.goto(`${FRONTEND_URL}/login`);
    await page.waitForURL("**/dashboard/**");
  });

  test("student can sign up and is auto-logged in", async ({ page }) => {
    const email = `e2e.${Date.now()}@studentflow.local`;
    await page.goto(`${FRONTEND_URL}/register`);
    await page.fill('input[name="name"]', "E2E Test Student");
    await page.fill('input[type="email"]', email);
    await page.fill('input[name="password"]', "StudentPass123!");
    await page.fill('input[name="password_confirmation"]', "StudentPass123!");
    await page.click('button[type="submit"]');

    await page.waitForURL("**/dashboard/**");

    const cookies = await page.context().cookies();
    const sessionCookie = cookies.find((c) => c.name.includes("session"));
    expect(sessionCookie).toBeDefined();
  });

  test("authenticated user can access dashboard", async ({ page }) => {
    await page.goto(`${FRONTEND_URL}/login`);
    await page.fill('input[name="username"]', "aaronvillanueva001");
    await page.fill('input[name="password"]', "StudentPass123!");
    await page.click('button[type="submit"]');
    await page.waitForURL("**/dashboard/**");

    await page.goto(`${FRONTEND_URL}/dashboard/student`);
    await page.waitForLoadState("networkidle");
    await expect(page.locator("body")).not.toContainText("Login");
  });
});
