import axios, { AxiosError, type AxiosRequestConfig, type InternalAxiosRequestConfig } from "axios";
import type { ApiError as ApiErrorBody } from "@pig-farm/types";

// Laravel origin, e.g. http://localhost:8000. JSON routes live under /api,
// Sanctum's CSRF endpoint under /sanctum.
const API_ORIGIN = process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000";

export class ApiError extends Error {
  constructor(
    public status: number,
    message: string,
    public errors?: Record<string, string[]>,
  ) {
    super(message);
    this.name = "ApiError";
  }
}

const http = axios.create({
  baseURL: `${API_ORIGIN}/api`,
  headers: { Accept: "application/json" },
  // Sanctum SPA auth: session cookie + XSRF-TOKEN cookie echoed as X-XSRF-TOKEN.
  withCredentials: true,
  withXSRFToken: true,
});

let csrfPromise: Promise<unknown> | null = null;

export function initCsrf() {
  csrfPromise ??= axios
    .get(`${API_ORIGIN}/sanctum/csrf-cookie`, { withCredentials: true })
    .finally(() => {
      csrfPromise = null;
    });
  return csrfPromise;
}

function hasXsrfCookie() {
  return document.cookie.split("; ").some((c) => c.startsWith("XSRF-TOKEN="));
}

http.interceptors.request.use(async (config: InternalAxiosRequestConfig) => {
  const method = (config.method ?? "get").toLowerCase();
  if (method !== "get" && typeof document !== "undefined" && !hasXsrfCookie()) {
    await initCsrf();
  }
  return config;
});

http.interceptors.response.use(
  (response) => response,
  async (error: AxiosError<ApiErrorBody>) => {
    const original = error.config as (InternalAxiosRequestConfig & { _retry?: boolean }) | undefined;

    // 419 = CSRF token expired: refresh once and replay.
    if (error.response?.status === 419 && original && !original._retry) {
      original._retry = true;
      await initCsrf();
      return http(original);
    }

    if (error.response?.status === 401 && typeof window !== "undefined") {
      window.dispatchEvent(new CustomEvent("auth:unauthenticated"));
    }

    const body = error.response?.data;
    return Promise.reject(
      new ApiError(error.response?.status ?? 0, body?.message ?? error.message, body?.errors),
    );
  },
);

export const api = {
  get: <T>(url: string, config?: AxiosRequestConfig) => http.get<T>(url, config).then((r) => r.data),
  post: <T>(url: string, data?: unknown, config?: AxiosRequestConfig) =>
    http.post<T>(url, data, config).then((r) => r.data),
  put: <T>(url: string, data?: unknown, config?: AxiosRequestConfig) =>
    http.put<T>(url, data, config).then((r) => r.data),
  patch: <T>(url: string, data?: unknown, config?: AxiosRequestConfig) =>
    http.patch<T>(url, data, config).then((r) => r.data),
  delete: <T>(url: string, config?: AxiosRequestConfig) => http.delete<T>(url, config).then((r) => r.data),
};
