"use server"

import { cookies } from "next/headers"

// Laravel's session cookie is httpOnly, so only the server can drop it.
export async function deleteAuthCookies() {
  const jar = await cookies()
  const domain = process.env.SESSION_DOMAIN || undefined
  for (const name of [process.env.SESSION_COOKIE ?? "laravel_session", "XSRF-TOKEN"]) {
    jar.delete({ name, domain, path: "/" })
  }
}
