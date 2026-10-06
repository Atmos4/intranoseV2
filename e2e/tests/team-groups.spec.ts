import { test, expect, Browser } from "@playwright/test";

// The seeded event is the only one in the e2e club
const EVENT = "/evenements/1";
const GROUP_NAME = "Relais dimanche";

// Tests share one DB, so the steps of the lifecycle run in order
test.describe.configure({ mode: "serial" });

test.describe("team group lifecycle", () => {
  test.use({ storageState: ".auth/admin.json" });

  let groupUrl: string;

  test("admin creates a team group", async ({ page }) => {
    await page.goto(`${EVENT}/groupe-equipes/nouveau`);
    await page.getByLabel("Nom du groupe d'équipes").fill(GROUP_NAME);
    await page.getByRole("button", { name: "Créer le Groupe" }).click();

    await expect(page.getByText("Groupe d'équipes créé")).toBeVisible();
    await expect(page).toHaveURL(/\/groupe-equipes\/\d+$/);
    groupUrl = new URL(page.url()).pathname;
  });

  test("admin adds a team and saves it", async ({ page }) => {
    await page.goto(groupUrl);
    await page.getByText("Ajouter une équipe").click();

    const teamName = page.getByPlaceholder("Nom de l'équipe");
    await expect(teamName).toBeVisible(); // team form is loaded through htmx
    await teamName.fill("Équipe A");
    await page.getByRole("button", { name: "Sauvegarder" }).click();
    await expect(page.getByText("Équipes sauvegardées")).toBeVisible();

    await page.reload();
    await expect(page.getByPlaceholder("Nom de l'équipe")).toHaveValue("Équipe A");
  });

  test("a member cannot see the group until it is published", async ({ browser }) => {
    expect(await memberSeesGroup(browser)).toBe(false);
  });

  test("admin publishes the group, then the member sees it", async ({ page, browser }) => {
    await page.goto(`${groupUrl}/publier`);
    await page.getByRole("button", { name: "Publier" }).click();
    await expect(page.getByText("Groupe publié")).toBeVisible();

    expect(await memberSeesGroup(browser)).toBe(true);
  });

  test("admin deletes the group", async ({ page }) => {
    await page.goto(`${groupUrl}/supprimer`);
    await page.getByRole("button", { name: "Supprimer" }).click();
    await expect(page.getByText("Groupe d'équipes supprimé")).toBeVisible();

    await page.goto(`${EVENT}/groupe-equipes`);
    await expect(page.getByText(GROUP_NAME)).toHaveCount(0);
  });
});

async function memberSeesGroup(browser: Browser) {
  const context = await browser.newContext({ storageState: ".auth/member.json" });
  const page = await context.newPage();
  await page.goto(`${EVENT}/groupe-equipes`);
  const visible = (await page.getByText(GROUP_NAME).count()) > 0;
  await context.close();
  return visible;
}
