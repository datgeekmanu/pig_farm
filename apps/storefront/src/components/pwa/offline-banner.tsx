"use client";

import { useOffline } from "next/offline";

export function OfflineBanner() {
  const isOffline = useOffline();
  if (!isOffline) return null;

  return (
    <div role="status" className="bg-muted text-muted-foreground px-4 py-2 text-center text-sm">
      Offline. Showing saved data.
    </div>
  );
}
