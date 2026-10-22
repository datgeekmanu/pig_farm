"use client";

import { SerwistProvider } from "@serwist/turbopack/react";
import { PersistQueryClientProvider } from "@tanstack/react-query-persist-client";
import { ReactQueryDevtools } from "@tanstack/react-query-devtools";
import { ThemeProvider } from "next-themes";
import { useEffect } from "react";
import { Toaster } from "sonner";
import { getQueryClient, persister, PERSIST_MAX_AGE } from "@/lib/query-client";

export function Providers({ children }: { children: React.ReactNode }) {
  const queryClient = getQueryClient();

  // Session gone: drop cached (and IndexedDB-persisted) user data.
  useEffect(() => {
    const clear = () => queryClient.clear();
    window.addEventListener("auth:unauthenticated", clear);
    return () => window.removeEventListener("auth:unauthenticated", clear);
  }, [queryClient]);

  return (
    <SerwistProvider swUrl="/serwist/sw.js" disable={process.env.NODE_ENV === "development"}>
      <ThemeProvider attribute="class" defaultTheme="system" enableSystem disableTransitionOnChange>
        <PersistQueryClientProvider
          client={queryClient}
          persistOptions={{ persister: persister!, maxAge: PERSIST_MAX_AGE }}
        >
          {children}
          <Toaster position="top-center" richColors />
          <ReactQueryDevtools buttonPosition="bottom-left" />
        </PersistQueryClientProvider>
      </ThemeProvider>
    </SerwistProvider>
  );
}
