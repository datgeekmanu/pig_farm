import { Icon } from "@/components/ui/icon";

const STEP_BUTTON = "grid size-11 place-items-center text-foreground disabled:opacity-40";

// Pill with −/+ buttons; shows the value between them when given.
export function Stepper({
  value,
  onDecrement,
  onIncrement,
  decrementLabel,
  incrementLabel,
  canDecrement = true,
  canIncrement = true,
}: {
  value?: number;
  onDecrement: () => void;
  onIncrement: () => void;
  decrementLabel: string;
  incrementLabel: string;
  canDecrement?: boolean;
  canIncrement?: boolean;
}) {
  return (
    <div className="flex h-11 items-center rounded-full border border-border">
      <button type="button" aria-label={decrementLabel} onClick={onDecrement} disabled={!canDecrement} className={STEP_BUTTON}>
        <Icon name="minus" />
      </button>
      {value === undefined ? (
        <span className="h-full w-px bg-border" />
      ) : (
        <span className="min-w-7 text-center font-heading font-bold tabular-nums" aria-live="polite">
          {value}
        </span>
      )}
      <button type="button" aria-label={incrementLabel} onClick={onIncrement} disabled={!canIncrement} className={STEP_BUTTON}>
        <Icon name="plus" />
      </button>
    </div>
  );
}
