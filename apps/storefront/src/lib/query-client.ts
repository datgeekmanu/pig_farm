import { QueryClient } from "@tanstack/react-query";
import { createAsyncStoragePersister } from "@tanstack/query-async-storage-persister";
import { del, get, set } from "idb-keyval";

const DAY = 1000 * 60 * 60 * 24;

export function makeQueryClient() {
  return new QueryClient({
    defaultOptions: {
      queries: {
        staleTime: 60 * 1000,
        // Must be >= persister maxAge, otherwise restored data is GC'd immediately.
        gcTime: DAY,
        // Serve cached data while offline, refetch when back online.
        networkMode: "offlineFirst",
        retry: (failureCount, error) => {
          const status = (error as { status?: number }).status;
          if (status && status >= 400 && status < 500) return false;
          return failureCount < 2;
        },
      },
      mutations: {
        // Read-offline only: writes fail fast instead of queueing.
        networkMode: "online",
      },
    },
  });
}

let browserQueryClient: QueryClient | undefined;

// One client per request on the server, a singleton in the browser.
export function getQueryClient() {
  if (typeof window === "undefined") return makeQueryClient();
  browserQueryClient ??= makeQueryClient();
  return browserQueryClient;
}

export const persister =
  typeof window === "undefined"
    ? undefined
    : createAsyncStoragePersister({
        storage: {
          getItem: (key) => get<string>(key).then((v) => v ?? null),
          setItem: (key, value) => set(key, value),
          removeItem: (key) => del(key),
        },
        key: "storefront-query-cache",
      });

export const PERSIST_MAX_AGE = DAY;
