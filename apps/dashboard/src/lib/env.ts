// Public env is inlined at build time; fail fast if it's missing.
const apiUrl = process.env.NEXT_PUBLIC_API_URL
if (!apiUrl) throw new Error("NEXT_PUBLIC_API_URL is not set")

export const env = {
  apiUrl,
  // Laravel app root (no /api suffix), used for /sanctum/csrf-cookie.
  appUrl: apiUrl.replace(/\/api\/?$/, ""),
}
