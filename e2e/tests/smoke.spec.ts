import { test, expect } from "@playwright/test";

// Parameter-free GET pages, plus the seeded event (id 1).
// Catches broken includes, fatal errors and PHP warnings after refactors.
const PAGES = [
  "/evenements",
  "/evenements/calendrier",
  "/evenements/passes",
  "/evenements/nouveau/choix",
  "/evenements/nouveau/simple",
  "/evenements/nouveau/complexe",
  "/evenements/1",
  "/evenements/1/participants",
  "/evenements/1/groupe-equipes",
  "/evenements/1/groupe-equipes/nouveau",
  "/evenements/1/vehicules",
  "/evenements/1/messages",
  "/licencies",
  "/licencies/ajouter",
  "/licencies/desactive",
  "/licencies/inactif",
  "/familles",
  "/groupes",
  "/groupes/nouveau",
  "/mon-profil",
  "/club_settings",
  "/documents",
  "/liens-utiles",
  "/messages",
  "/calendrier/abonnement",
  "/admin",
  "/admin/backups",
  "/admin/logs",
  "/about",
];

const PHP_ERROR = /Fatal error|Parse error|Uncaught|Warning:|Notice:|Deprecated:|Stack trace/;

test.describe("admin", () => {
  test.use({ storageState: ".auth/admin.json" });

  for (const url of PAGES) {
    test(`${url} renders without errors`, async ({ page }) => {
      const response = await page.goto(url);
      expect(response?.status(), "status").toBeLessThan(400);
      expect(await page.content()).not.toMatch(PHP_ERROR);
    });
  }
});

test.describe("member", () => {
  test.use({ storageState: ".auth/member.json" });

  for (const url of ["/evenements", "/evenements/calendrier", "/evenements/1", "/groupes", "/mon-profil", "/documents"]) {
    test(`${url} renders without errors`, async ({ page }) => {
      const response = await page.goto(url);
      expect(response?.status(), "status").toBeLessThan(400);
      expect(await page.content()).not.toMatch(PHP_ERROR);
    });
  }
});
