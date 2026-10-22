import axios, { AxiosError, type InternalAxiosRequestConfig } from "axios"
import { env } from "./env"
import { activeFarm } from "./farm"

type Params = Record<string, unknown>

// Laravel error body: { message, errors?: { field: [msg] } }
type LaravelError = { message?: string; errors?: Record<string, string[]> }

export class ApiError extends Error {
  constructor(
    public status: number,
    message: string,
    public errors?: Record<string, string[]>,
  ) {
    super(message)
    this.name = "ApiError"
  }
}

const http = axios.create({
  baseURL: env.apiUrl,
  headers: { Accept: "application/json" },
  withCredentials: true,
  withXSRFToken: true,
})

// Sanctum SPA auth: fetch XSRF-TOKEN cookie once; axios echoes it as X-XSRF-TOKEN.
let csrfPromise: Promise<unknown> | null = null

function initCsrf() {
  csrfPromise ??= http.get("/sanctum/csrf-cookie", { baseURL: env.appUrl }).finally(() => {
    csrfPromise = null
  })
  return csrfPromise
}

function hasXsrfCookie() {
  return document.cookie.split("; ").some((c) => c.startsWith("XSRF-TOKEN="))
}

http.interceptors.request.use(async (config: InternalAxiosRequestConfig) => {
  const method = config.method?.toLowerCase() ?? "get"
  if (method !== "get" && !hasXsrfCookie()) await initCsrf()

  const farmId = activeFarm.get()
  if (farmId && !config.headers["X-Farm-Id"]) config.headers["X-Farm-Id"] = farmId

  return config
})

http.interceptors.response.use(
  (response) => response,
  async (error: AxiosError<LaravelError>) => {
    const original = error.config as InternalAxiosRequestConfig & { _retry?: boolean }

    // 419 = CSRF token mismatch/expired: refresh once and replay.
    if (error.response?.status === 419 && original && !original._retry) {
      original._retry = true
      await initCsrf()
      return http(original)
    }

    const data = error.response?.data
    throw new ApiError(error.response?.status ?? 0, data?.message ?? error.message, data?.errors)
  },
)

async function request<T>(config: {
  method: "GET" | "POST" | "PUT" | "PATCH" | "DELETE"
  url: string
  data?: unknown
  params?: Params
  headers?: Record<string, string>
}): Promise<T> {
  const response = await http.request<T>(config)
  return response.data
}

export const api = {
  get: <T>(url: string, params?: Params) => request<T>({ method: "GET", url, params }),
  post: <T>(url: string, data?: unknown) => request<T>({ method: "POST", url, data }),
  put: <T>(url: string, data?: unknown) => request<T>({ method: "PUT", url, data }),
  patch: <T>(url: string, data?: unknown) => request<T>({ method: "PATCH", url, data }),
  delete: <T>(url: string) => request<T>({ method: "DELETE", url }),
}
