"use client";

import { Switch as SwitchPrimitive } from "radix-ui";
import { useId } from "react";

export function Switch({
  checked,
  onCheckedChange,
  label,
  description,
  disabled,
}: {
  checked: boolean;
  onCheckedChange: (checked: boolean) => void;
  label: string;
  description?: string;
  disabled?: boolean;
}) {
  const id = useId();

  return (
    <label htmlFor={id} className="flex cursor-pointer items-center gap-3 has-disabled:cursor-not-allowed">
      <span className="min-w-0 flex-1">
        <span className="block text-lead font-semibold">{label}</span>
        {description && <span className="block text-meta text-muted-foreground">{description}</span>}
      </span>
      <SwitchPrimitive.Root
        id={id}
        checked={checked}
        onCheckedChange={onCheckedChange}
        disabled={disabled}
        className="relative h-[26px] w-[46px] flex-none rounded-full bg-input transition-colors duration-200 ease-out disabled:opacity-50 data-[state=checked]:bg-primary"
      >
        <SwitchPrimitive.Thumb className="block size-5 translate-x-[3px] rounded-full bg-white shadow-sm transition-transform duration-200 ease-out data-[state=checked]:translate-x-[23px] data-[state=checked]:bg-primary-foreground" />
      </SwitchPrimitive.Root>
    </label>
  );
}
