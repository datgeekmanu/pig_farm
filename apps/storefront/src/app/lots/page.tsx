import type { Metadata } from "next";
import { AppShell } from "@/components/app/app-shell";
import { LotsListScreen } from "@/components/screens/list-screens";

export const metadata: Metadata = { title: "Lots" };

export default function LotsPage() {
  return (
    <AppShell>
      <LotsListScreen />
    </AppShell>
  );
}
