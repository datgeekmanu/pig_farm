import { createSerwistRoute } from "@serwist/turbopack";

// Builds src/app/sw.ts with esbuild-wasm and serves it at /serwist/sw.js.
export const { dynamic, dynamicParams, revalidate, generateStaticParams, GET } = createSerwistRoute({
  swSrc: "src/app/sw.ts",
  additionalPrecacheEntries: [{ url: "/~offline", revision: crypto.randomUUID() }],
});
