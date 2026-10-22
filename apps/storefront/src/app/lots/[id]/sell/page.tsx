import type { Metadata } from "next";
import { SellScreen } from "@/components/screens/sell-screen";

export const metadata: Metadata = { title: "Mettre en vente" };

export default async function SellPage({ params }: PageProps<"/lots/[id]/sell">) {
  const { id } = await params;
  return <SellScreen lotId={id} />;
}
