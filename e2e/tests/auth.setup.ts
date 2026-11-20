import { test as setup, expect } from "@playwright/test";
import { login, AUTH } from "./helpers";

for (const role of ["admin", "member"] as const) {
  setup(`authenticate as ${role}`, async ({ page }) => {
    await login(page, role);
    await expect(page).toHaveURL(/\/evenements/);
    await page.context().storageState({ path: AUTH[role] });
  });
}
