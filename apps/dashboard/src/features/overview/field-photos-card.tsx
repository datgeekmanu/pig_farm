"use client"

import Image from "next/image"
import { useState } from "react"
import { Icon } from "@/components/icon"
import { Pill } from "@/components/shared/pill"

// TODO: field photos uploaded from the mobile app.
const PHOTOS = [
  {
    src: "/images/selective-closeup-shot-pink-pigs-barn.jpg",
    caption: "Loge 2-04 · L-24-07 à l’auge",
    author: "Jean Mvogo · pesée du 17.10",
  },
  { src: "/images/domestic-pig-farm-outdoors.jpg", caption: "Parcours extérieur · Bâtiment 3", author: "Aminata Ndongo · 15.10" },
  { src: "/images/pigs-grazing-around-farm.jpg", caption: "Truie T-097 · parcours gestantes", author: "Paul Essomba · 12.10" },
]

export function FieldPhotosCard() {
  const [index, setIndex] = useState(0)
  const photo = PHOTOS[index]
  const step = (delta: number) => setIndex((i) => (i + delta + PHOTOS.length) % PHOTOS.length)

  return (
    <section className="relative min-h-95 overflow-hidden rounded-xl bg-sidebar">
      <Image src={photo.src} alt={photo.caption} fill sizes="(min-width: 1024px) 50vw, 100vw" className="object-cover" />
      <div className="absolute inset-x-4 top-4 flex justify-between gap-2">
        <Pill tone="brand">
          <Icon name="camera" />
          Photos terrain
        </Pill>
        <Pill tone="brand">
          {index + 1} / {PHOTOS.length}
        </Pill>
      </div>
      <div className="absolute inset-x-4 bottom-4 flex items-end gap-3">
        <div className="flex min-w-0 flex-1 flex-col rounded-lg bg-card px-3.5 py-3 text-card-foreground">
          <span className="text-body font-semibold">{photo.caption}</span>
          <span className="text-caption text-muted-foreground">{photo.author}</span>
        </div>
        <div className="flex gap-2">
          <PhotoButton label="Photo précédente" icon="arrow-left" onClick={() => step(-1)} />
          <PhotoButton label="Photo suivante" icon="arrow-right" onClick={() => step(1)} />
        </div>
      </div>
    </section>
  )
}

function PhotoButton({ label, icon, onClick }: { label: string; icon: string; onClick: () => void }) {
  return (
    <button
      type="button"
      aria-label={label}
      onClick={onClick}
      className="flex size-11 cursor-pointer items-center justify-center rounded-md bg-card text-foreground transition-colors hover:bg-muted"
    >
      <Icon name={icon} />
    </button>
  )
}
