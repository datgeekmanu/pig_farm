import type { Metadata } from "next"
import Image from "next/image"
import { Pill } from "@/components/shared/pill"
import { LoginForm } from "@/features/auth/login-form"

export const metadata: Metadata = { title: "Connexion" }

export default function LoginPage() {
  return (
    <div className="flex min-h-screen gap-4 p-4">
      <div className="relative min-h-140 min-w-0 flex-[1.2] overflow-hidden rounded-2xl bg-sidebar max-md:hidden">
        <Image
          src="/images/pigs-grazing-around-farm.jpg"
          alt="Truie sur un parcours extérieur"
          fill
          sizes="60vw"
          priority
          className="object-cover"
        />
        <Image src="/brand/pigfarm-logo-horizontal-reversed.png" alt="Pig Farm" width={103} height={36} className="absolute top-6 left-6" />
        <div className="absolute inset-x-6 bottom-6 max-w-105">
          <Pill tone="brand">
            <span className="py-1 text-body">Lots, reproduction, alimentation et finances de vos élevages au même endroit.</span>
          </Pill>
        </div>
      </div>
      <div className="flex min-w-0 flex-1 items-center justify-center p-8">
        <LoginForm />
      </div>
    </div>
  )
}
