import { test, expect } from "@playwright/test";

const EVENT_NAME = "WE Championnats de France";

test.use({ storageState: ".auth/member.json" });

test("member registers to a published event", async ({ page }) => {
  await page.goto("/evenements");
  await page.getByText(EVENT_NAME).first().click();
  await expect(page).toHaveURL(/\/evenements\/\d+/);

  await page.getByRole("link", { name: /inscri|participe/i }).first().click();
  await expect(page).toHaveURL(/\/inscription/);

  await page.getByRole("button", { name: /Je participe/ }).first().click();
  await page.getByRole("button", { name: "Enregistrer" }).click();

  await expect(page.getByText("Inscription enregistrée")).toBeVisible();
});
