import { api } from "@/lib/api-client"
import { respond } from "@/mocks/db"

type Role = "owner" | "worker"

// TODO(#0.5): replace with the generated @pig-farm/types User.
type User = {
  id: number
  firstName: string
  lastName: string
  email: string
  role: Role
}

export type LoginInput = { email: string; password: string; remember: boolean }

const DEMO_USER: User = { id: 1, firstName: "Aminata", lastName: "Ndongo", email: "aminata.ndongo@ferme-mbalmayo.cm", role: "owner" }
const SESSION_COOKIE = "laravel_session"

// TODO(#1.1): drop the mock branch once Sanctum login/logout routes exist.
const useMocks = process.env.NEXT_PUBLIC_API_MOCKS === "true"

export const authApi = {
  me: () => (useMocks ? respond(DEMO_USER) : api.get<User>("/user")),

  login: (input: LoginInput) => {
    if (!useMocks) return api.post<User>("/login", input)
    // Fake session cookie so proxy.ts lets the demo through.
    document.cookie = `${SESSION_COOKIE}=demo; path=/; SameSite=Lax`
    return respond(DEMO_USER)
  },

  logout: () => {
    if (!useMocks) return api.post<void>("/logout")
    document.cookie = `${SESSION_COOKIE}=; path=/; max-age=0`
    return respond(undefined)
  },
}
