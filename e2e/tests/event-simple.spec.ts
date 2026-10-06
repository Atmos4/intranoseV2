import { test, expect } from "@playwright/test";
import { dateTimeIn } from "./helpers";

const NAME = "Entraînement du mardi";
const RENAMED = "Entraînement du jeudi";

// Tests share one DB, so the steps of the lifecycle run in order
test.describe.configure({ mode: "serial" });

test.describe("simple event lifecycle", () => {
  test.use({ storageState: ".auth/admin.json" });

  let eventUrl: string;

  test("form rejects an empty submission and an end before the start", async ({ page }) => {
    await page.goto("/evenements/nouveau/simple");
    await page.getByRole("button", { name: "Créer" }).click();
    await expect(page).toHaveURL(/nouveau\/simple/);

    await page.getByLabel("Nom de l'activité").fill(NAME);
    await page.getByLabel("Date de début").fill(dateTimeIn(20, 18));
    await page.getByLabel("Date de fin").fill(dateTimeIn(20, 10));
    await page.getByLabel("Nom du Lieu").fill("Stade municipal");
    await page.getByRole("button", { name: "Créer" }).click();

    await expect(page.getByText("Doit être après le départ")).toBeVisible();
    await expect(page).toHaveURL(/nouveau\/simple/);
  });

  test("admin creates a simple event", async ({ page }) => {
    await page.goto("/evenements/nouveau/simple");
    await page.getByLabel("Nom de l'activité").fill(NAME);
    await page.getByLabel("Date de début").fill(dateTimeIn(20, 18));
    await page.getByLabel("Date de fin").fill(dateTimeIn(20, 20));
    await page.getByLabel("Nom du Lieu").fill("Stade municipal");
    await page.getByLabel("Date limite d'inscription").fill(dateTimeIn(19, 18));
    await page.getByRole("button", { name: "Créer" }).click();

    await expect(page.getByText("Enregistré")).toBeVisible();
    await expect(page).toHaveURL(/\/evenements$/);

    await page.getByText(NAME).first().click();
    await expect(page).toHaveURL(/\/evenements\/\d+/);
    eventUrl = new URL(page.url()).pathname;
  });

  test("admin edits the event", async ({ page }) => {
    await page.goto(`${eventUrl}/modifier/simple`);
    await expect(page.getByLabel("Nom de l'activité")).toHaveValue(NAME);
    await page.getByLabel("Nom de l'activité").fill(RENAMED);
    await page.getByRole("button", { name: "Modifier" }).click();

    await expect(page.getByText("Enregistré")).toBeVisible();
    await expect(page.getByText(RENAMED).first()).toBeVisible();
  });

  test("a member cannot register while the event is unpublished", async ({ browser }) => {
    const context = await browser.newContext({ storageState: ".auth/member.json" });
    const response = await context.newPage().then((p) => p.goto(`${eventUrl}/inscription_simple`));
    expect(response?.status()).toBe(404);
    await context.close();
  });

  test("admin publishes, then a member registers", async ({ page, browser }) => {
    await page.goto(`${eventUrl}/publier`);
    await page.getByRole("button", { name: "Publier" }).click();
    await expect(page.getByText("Événement publié")).toBeVisible();

    const context = await browser.newContext({ storageState: ".auth/member.json" });
    const member = await context.newPage();
    await member.goto(`${eventUrl}/inscription_simple`);
    await member.getByRole("button", { name: /Je participe/ }).first().click();
    await member.getByRole("button", { name: "Enregistrer" }).click();
    await expect(member).toHaveURL(new RegExp(`${eventUrl}$`));

    await member.goto(`${eventUrl}/inscription_simple`);
    await expect(member.getByRole("button", { name: /Je participe/ }).first()).toHaveAttribute("aria-pressed", "true");
    await context.close();
  });

  test("a published event cannot be deleted", async ({ request }) => {
    const response = await request.get(`${eventUrl}/supprimer`);
    expect(response.status()).toBe(404);
  });

  test("admin unpublishes then deletes the event", async ({ page }) => {
    await page.goto(`${eventUrl}/publier`);
    await page.getByRole("button", { name: "Retirer" }).click();
    await expect(page.getByText("Événement retiré")).toBeVisible();

    await page.goto(`${eventUrl}/supprimer`);
    await page.getByRole("button", { name: "Supprimer" }).click();
    await expect(page).toHaveURL(/\/evenements$/);
    await expect(page.getByText(RENAMED)).toHaveCount(0);
  });
});
