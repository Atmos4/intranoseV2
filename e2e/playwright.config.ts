import { defineConfig, devices } from "@playwright/test";

const PORT = 8001;
export const BASE_URL = `http://localhost:${PORT}`;

export default defineConfig({
  testDir: "./tests",
  // One shared SQLite DB: run serially to avoid cross-test interference
  workers: 1,
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  reporter: process.env.CI ? [["github"], ["html", { open: "never" }]] : "list",
  use: {
    baseURL: BASE_URL,
    locale: "fr-FR",
    trace: "retain-on-failure",
  },
  // Recreates the club and its users/event before the server starts
  globalSetup: "./global-setup.ts",
  webServer: {
    command: `php -S localhost:${PORT} server.php`,
    cwd: "..",
    url: `${BASE_URL}/select-club`,
    reuseExistingServer: false,
    timeout: 30_000,
    stdout: "ignore",
    stderr: "ignore",
  },
  projects: [
    { name: "setup", testMatch: /auth\.setup\.ts/ },
    {
      name: "chromium",
      use: { ...devices["Desktop Chrome"] },
      dependencies: ["setup"],
    },
  ],
});
