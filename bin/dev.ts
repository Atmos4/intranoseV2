import { $ } from "bun";
import { unlinkSync } from "node:fs";

// write lock file - using vite
await Bun.write("./usingVite.lockfile", "");
[`SIGBREAK`, `SIGINT`, `SIGTERM`].forEach((eventType) => {
  // delete lock file - we stop using vite
  process.on(eventType, () => unlinkSync(`./usingVite.lockfile`));
});
await Promise.all([$`bun dev:php`, $`bun dev:vite`]);
