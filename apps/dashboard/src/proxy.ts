import { NextResponse, type NextRequest } from "next/server"

const PUBLIC_ROUTES = ["/login", "/forgot-password", "/reset-password", "/verify-email"]
const SESSION_COOKIE = process.env.SESSION_COOKIE ?? "laravel_session"

// Optimistic check only (cookie presence). AuthGuard validates the session via /user.
export function proxy(request: NextRequest) {
  const { pathname } = request.nextUrl
  const isPublic = PUBLIC_ROUTES.some((route) => pathname.startsWith(route))

  if (!isPublic && !request.cookies.has(SESSION_COOKIE)) {
    const url = new URL("/login", request.url)
    url.searchParams.set("next", pathname)
    return NextResponse.redirect(url)
  }

  return NextResponse.next()
}

export const config = {
  matcher: ["/((?!api|_next/static|_next/image|favicon.ico|.*\\..*).*)"],
}
