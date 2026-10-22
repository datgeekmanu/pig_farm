import type { Metadata } from "next";
import { AppShell } from "@/components/app/app-shell";
import { SowsListScreen } from "@/components/screens/list-screens";

export const metadata: Metadata = { title: "Reproduction" };

export default function ReproPage() {
  return (
    <AppShell>
      <SowsListScreen />
    </AppShell>
  );
}
