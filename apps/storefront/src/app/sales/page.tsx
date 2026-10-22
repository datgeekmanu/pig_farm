import type { Metadata } from "next";
import { AppShell } from "@/components/app/app-shell";
import { SalesListScreen } from "@/components/screens/list-screens";

export const metadata: Metadata = { title: "Ventes" };

export default function SalesPage() {
  return (
    <AppShell>
      <SalesListScreen />
    </AppShell>
  );
}
