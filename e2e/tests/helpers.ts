import { Page } from "@playwright/test";

export const CLUB_SLUG = "e2e-club";

export const USERS = {
  admin: { login: "admin", password: "admin-pw" },
  member: { login: "member", password: "member-pw" },
};

export const AUTH = {
  admin: ".auth/admin.json",
  member: ".auth/member.json",
};

export async function selectClub(page: Page) {
  await page.goto("/select-club");
  await page.getByRole("link", { name: CLUB_SLUG }).click();
}

export async function login(
  page: Page,
  role: keyof typeof USERS,
  password = USERS[role].password,
) {
  await selectClub(page);
  await page.goto("/login");
  await page.getByPlaceholder("Login ou email").fill(USERS[role].login);
  await page.getByPlaceholder("Password").fill(password);
  await page.getByRole("button", { name: "Se connecter" }).click();
}

/** Value for a datetime-local input, `days` from now at the given hour. */
export function dateTimeIn(days: number, hour = 10): string {
  const d = new Date();
  d.setDate(d.getDate() + days);
  d.setHours(hour, 0, 0, 0);
  const pad = (n: number) => String(n).padStart(2, "0");
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:00`;
}
