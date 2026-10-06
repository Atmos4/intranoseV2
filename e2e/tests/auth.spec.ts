import { test, expect } from "@playwright/test";
import { login } from "./helpers";

test("rejects a wrong password", async ({ page }) => {
  await login(page, "admin", "wrong");
  await expect(page).toHaveURL(/\/login/);
});

test("redirects anonymous users to the login page", async ({ page }) => {
  await page.goto("/select-club");
  await page.getByRole("link", { name: "e2e-club" }).click();
  await page.goto("/evenements");
  await expect(page).toHaveURL(/\/login/);
});

test.describe("authenticated admin", () => {
  test.use({ storageState: ".auth/admin.json" });

  test("sees the event list", async ({ page }) => {
    await page.goto("/evenements");
    await expect(page.getByText("WE Championnats de France")).toBeVisible();
  });
});

test.describe("authenticated member", () => {
  test.use({ storageState: ".auth/member.json" });

  test("cannot reach the admin dashboard", async ({ page }) => {
    const response = await page.goto("/admin");
    expect(response?.status()).toBeGreaterThanOrEqual(400);
  });
});
