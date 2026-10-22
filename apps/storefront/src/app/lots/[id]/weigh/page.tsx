import type { Metadata } from "next";
import { WeighScreen } from "@/components/screens/weigh-screen";

export const metadata: Metadata = { title: "Pesée" };

export default async function WeighPage({ params }: PageProps<"/lots/[id]/weigh">) {
  const { id } = await params;
  return <WeighScreen id={id} />;
}
