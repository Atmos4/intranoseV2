import { test, expect } from "@playwright/test";
import { selectClub } from "./helpers";

// Routes that only staff / coaches / root may open (see Access::$ADD_EVENTS and friends)
const RESTRICTED = [
  "/admin",
  "/admin/logs",
  "/licencies/ajouter",
  "/evenements/nouveau/choix",
  "/evenements/nouveau/simple",
  "/evenements/nouveau/complexe",
  "/evenements/1/publier",
  "/evenements/1/groupe-equipes/nouveau",
  "/club_settings",
];

test.describe("admin", () => {
  test.use({ storageState: ".auth/admin.json" });

  for (const url of RESTRICTED) {
    test(`can open ${url}`, async ({ request }) => {
      const response = await request.get(url);
      expect(response.status()).toBeLessThan(400);
    });
  }
});

test.describe("member", () => {
  test.use({ storageState: ".auth/member.json" });

  for (const url of RESTRICTED) {
    test(`is refused ${url}`, async ({ request }) => {
      const response = await request.get(url);
      expect(response.status()).toBe(404);
    });
  }
});

test.describe("anonymous", () => {
  for (const url of ["/evenements", "/licencies", "/admin", "/evenements/1"]) {
    test(`is sent to the login page from ${url}`, async ({ page }) => {
      await selectClub(page);
      await page.goto(url);
      await expect(page).toHaveURL(/\/login/);
    });
  }
});
