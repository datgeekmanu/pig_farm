// Route shell shown while params resolve; also what Next prefetches for offline navigation.
export function ScreenLoading() {
  return (
    <div role="status" aria-label="Chargement" className="flex flex-1 flex-col gap-4 p-5">
      <div className="h-60 animate-pulse rounded-xl bg-muted" />
      <div className="h-8 w-1/2 animate-pulse rounded-md bg-muted" />
      <div className="h-24 animate-pulse rounded-xl bg-muted" />
    </div>
  );
}
