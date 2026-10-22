import type { MetadataRoute } from "next";

export default function manifest(): MetadataRoute.Manifest {
  return {
    id: "/",
    name: "Pig Farm",
    short_name: "Pig Farm",
    description: "Pesées, mortalités, traitements et ventes saisis sur le terrain, même sans réseau.",
    start_url: "/",
    scope: "/",
    display: "standalone",
    orientation: "portrait",
    background_color: "#f5f4f0",
    theme_color: "#0f2a22",
    // TODO: replace with real artwork (192 + 512, plus a maskable 512).
    icons: [
      { src: "/icons/icon-192.png", sizes: "192x192", type: "image/png" },
      { src: "/icons/icon-512.png", sizes: "512x512", type: "image/png" },
      { src: "/icons/icon-maskable-512.png", sizes: "512x512", type: "image/png", purpose: "maskable" },
    ],
  };
}
