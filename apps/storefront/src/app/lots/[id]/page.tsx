import { LotDetailScreen } from "@/components/screens/lot-detail-screen";

export default async function LotPage({ params }: PageProps<"/lots/[id]">) {
  const { id } = await params;
  return <LotDetailScreen id={id} />;
}
