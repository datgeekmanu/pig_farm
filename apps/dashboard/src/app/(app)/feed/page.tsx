import type { Metadata } from "next"
import { FeedScreen } from "@/features/feed/feed-screen"

export const metadata: Metadata = { title: "Alimentation" }

export default function Page() {
  return <FeedScreen />
}
