import { TabBar } from "./tab-bar";

// Screens with the bottom tab bar. Content clears the bar (and the home indicator).
export function AppShell({ children }: { children: React.ReactNode }) {
  return (
    <>
      <main className="flex-1 pb-28">{children}</main>
      <TabBar />
    </>
  );
}
