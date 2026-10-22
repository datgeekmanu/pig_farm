"use client";

import { useState } from "react";
import { cn } from "@/lib/utils";
import { Icon } from "./icon";

// Photo with a calm placeholder until real farm photography is dropped in public/.
export function ImageSlot({
  src,
  alt,
  placeholder,
  round = false,
}: {
  src?: string;
  alt: string;
  placeholder: string;
  round?: boolean;
}) {
  const [failed, setFailed] = useState(false);
  const shape = round ? "rounded-full" : "";

  if (src && !failed) {
    // eslint-disable-next-line @next/next/no-img-element -- local placeholders, size unknown until assets land
    return <img src={src} alt={alt} onError={() => setFailed(true)} className={cn("size-full object-cover", shape)} />;
  }

  return (
    <div
      role="img"
      aria-label={alt}
      className={cn("flex size-full flex-col items-center justify-center gap-2 bg-muted p-4 text-center text-muted-foreground", shape)}
    >
      <Icon name="image" variant="regular" className="text-xl" />
      {!round && <span className="text-xs">{placeholder}</span>}
    </div>
  );
}
