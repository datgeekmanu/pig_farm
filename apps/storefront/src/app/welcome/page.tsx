import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { Icon } from "@/components/ui/icon";
import { ImageSlot } from "@/components/ui/image-slot";

export const metadata: Metadata = {
  title: "Bienvenue",
};

// TODO(#1.1): point both actions at the login and invitation flows.
export default function WelcomePage() {
  return (
    <main className="relative flex min-h-dvh flex-col">
      <div className="absolute inset-0">
        <ImageSlot
          src="/images/selective-closeup-shot-pink-pigs-barn.jpg"
          alt="Bâtiment d'élevage"
          placeholder="Photo plein cadre : bâtiment d'élevage, lumière naturelle"
        />
      </div>

      <section className="relative flex flex-col gap-3.5 rounded-b-2xl bg-background px-7 pt-[max(5rem,calc(env(safe-area-inset-top)+3rem))] pb-7">
        <Image src="/brand/logo-forest-notag.png" alt="Pig Farm" width={218} height={52} priority className="-ml-1.5 h-13 w-auto self-start" />
        <h1 className="mt-1.5 text-display font-bold">
          Votre élevage, <span className="text-ring">dans la poche.</span>
        </h1>
        <p className="text-lead text-muted-foreground">
          Pesées, mortalités, traitements et ventes saisis sur le terrain — même sans réseau.
        </p>
      </section>

      <div className="relative mt-auto flex flex-col gap-3 px-5 pb-[max(2.5rem,env(safe-area-inset-bottom))]">
        <Link href="/" className="flex h-16 items-center gap-3 rounded-full bg-sidebar p-1.5 no-underline">
          <span className="grid size-13 place-items-center rounded-full bg-white text-lg text-sidebar">
            <Icon name="arrow-right" />
          </span>
          <span className="mr-5 flex-1 text-center text-base font-semibold text-white">Se connecter</span>
          <span className="mr-4 flex text-meta tracking-[-2px] text-switch-on" aria-hidden>
            <Icon name="chevron-right" />
            <Icon name="chevron-right" className="opacity-60" />
            <Icon name="chevron-right" className="opacity-30" />
          </span>
        </Link>
        <Link
          href="/"
          className="flex h-12 items-center justify-center gap-2 rounded-full border border-border bg-card text-lead font-semibold text-foreground no-underline"
        >
          <Icon name="ticket" />
          J&apos;ai un code d&apos;invitation
        </Link>
      </div>
    </main>
  );
}
