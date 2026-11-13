import { test, expect, Page } from "@playwright/test";
import { dateTimeIn } from "./helpers";

const NAME = "Stage de printemps";

test.use({ storageState: ".auth/admin.json" });
test.describe.configure({ mode: "serial" });

/** Fills the event-level fields (the activity panels have their own inputs). */
async function fillEvent(page: Page, name: string) {
  await page.locator('input[name="event_name"]').fill(name);
  await page.locator('input[name="event_start_date"]').fill(dateTimeIn(30, 9));
  await page.locator('input[name="event_end_date"]').fill(dateTimeIn(31, 18));
  await page.locator('input[name="event_limit_date"]').fill(dateTimeIn(29, 9));
}

/** Adds an activity tab through htmx and fills it. */
async function addActivity(page: Page, index: number, name: string, startDay: number) {
  await page.getByRole("button", { name: "Ajouter une activité" }).click();
  const wrapper = page.locator(`#activity-wrapper-${index}`);
  const field = (n: string) => wrapper.locator(`[name="activity[${index}][${n}]"]`);

  await expect(field("name")).toBeVisible(); // form is loaded through htmx
  await field("name").fill(name);
  await field("start_date").fill(dateTimeIn(startDay, 10));
  await field("end_date").fill(dateTimeIn(startDay, 12));
  await field("location_label").fill("Gymnase");
}

test.describe("complex event", () => {
  let eventUrl: string;

  test("form shows an error for an activity outside the event dates", async ({ page }) => {
    await page.goto("/evenements/nouveau/complexe");
    await fillEvent(page, NAME);
    await addActivity(page, 0, "Hors dates", 40);
    await page.getByRole("button", { name: "Créer" }).click();

    await expect(page.getByText("Doit être avant la date de fin de l'événement").first()).toBeVisible();
    await expect(page).toHaveURL(/nouveau\/complexe/);
  });

  test("admin creates an event with two activities", async ({ page }) => {
    await page.goto("/evenements/nouveau/complexe");
    await fillEvent(page, NAME);
    await addActivity(page, 0, "Footing", 30);
    await addActivity(page, 1, "Orientation", 31);
    await page.getByRole("button", { name: "Créer" }).click();

    await expect(page.getByText("Enregistré")).toBeVisible();
    await expect(page).toHaveURL(/\/evenements\/\d+$/);
    eventUrl = new URL(page.url()).pathname;

    await expect(page.getByText("Footing").first()).toBeVisible();
    await expect(page.getByText("Orientation").first()).toBeVisible();
  });

  test("edit form is prefilled with both activities", async ({ page }) => {
    await page.goto(`${eventUrl}/modifier/complexe`);
    await expect(page.locator('input[name="event_name"]')).toHaveValue(NAME);
    await expect(page.locator('input[name="activity[0][name]"]')).toHaveValue("Footing");
    await expect(page.locator('input[name="activity[1][name]"]')).toHaveValue("Orientation");
  });

  test("admin publishes, then a member registers to one activity", async ({ page, browser }) => {
    await page.goto(`${eventUrl}/publier`);
    await page.getByRole("button", { name: "Publier" }).click();
    await expect(page.getByText("Événement publié")).toBeVisible();

    const context = await browser.newContext({ storageState: ".auth/member.json" });
    const member = await context.newPage();
    await member.goto(`${eventUrl}/inscription`);
    await member.getByRole("button", { name: /Je participe/ }).first().click();
    await member.getByRole("button", { name: "Enregistrer" }).click();
    await expect(member.getByText("Inscription enregistrée")).toBeVisible();
    await context.close();
  });

  test("admin unpublishes then deletes the event", async ({ page }) => {
    await page.goto(`${eventUrl}/publier`);
    await page.getByRole("button", { name: "Retirer" }).click();

    await page.goto(`${eventUrl}/supprimer`);
    await page.getByRole("button", { name: "Supprimer" }).click();
    await expect(page).toHaveURL(/\/evenements$/);
    await expect(page.getByText(NAME)).toHaveCount(0);
  });
});
