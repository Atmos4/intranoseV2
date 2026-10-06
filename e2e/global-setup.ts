import { execFileSync } from "node:child_process";
import path from "node:path";

export default function globalSetup() {
  execFileSync("php", ["bin/seed-e2e"], {
    cwd: path.resolve(__dirname, ".."),
    stdio: "inherit",
  });
}
