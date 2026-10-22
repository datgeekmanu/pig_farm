"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { Icon } from "@/components/ui/icon";

const ROUND = "grid size-11 place-items-center rounded-full bg-card text-foreground no-underline";

// 44px circular controls floating over photo headers.
export function BackButton({ fallbackHref = "/" }: { fallbackHref?: string }) {
  const router = useRouter();

  return (
    <button
      type="button"
      aria-label="Retour"
      onClick={() => (window.history.length > 1 ? router.back() : router.push(fallbackHref))}
      className={ROUND}
    >
      <Icon name="arrow-left" />
    </button>
  );
}

export function RoundLink({ href, icon, label }: { href: string; icon: string; label: string }) {
  return (
    <Link href={href} aria-label={label} className={ROUND}>
      <Icon name={icon} />
    </Link>
  );
}

export function RoundButton({ icon, label, onClick }: { icon: string; label: string; onClick?: () => void }) {
  return (
    <button type="button" aria-label={label} onClick={onClick} className={ROUND}>
      <Icon name={icon} />
    </button>
  );
}
