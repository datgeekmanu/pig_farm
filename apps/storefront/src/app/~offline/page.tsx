import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Offline",
};

export default function OfflinePage() {
  return (
    <main className="flex flex-1 flex-col items-center justify-center gap-2 p-6 text-center">
      <h1 className="text-xl font-semibold">You are offline</h1>
      <p className="text-muted-foreground">This page is not available offline yet. It will load when you reconnect.</p>
    </main>
  );
}
