import { SowScreen } from "@/components/screens/sow-screen";

export default async function SowPage({ params }: PageProps<"/repro/[id]">) {
  const { id } = await params;
  return <SowScreen id={id} />;
}
