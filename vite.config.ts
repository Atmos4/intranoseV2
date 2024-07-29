import { defineConfig } from "vite";
import liveReload from "vite-plugin-live-reload";

// AP 2024-07 - Only using Vite for live reload for now
export default defineConfig({
  plugins: [liveReload([__dirname + "/(app|assets|engine)/**/*.(php|css|js)"])],
  server: {
    port: 5133,
  },
  build: {
    outDir: "dist",
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: "assets/vite.js",
    },
  },
});
