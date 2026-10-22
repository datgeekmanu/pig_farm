// Herd calculations shared by the lot, weigh-in and gestation screens.

export function mean(values: number[]) {
  if (values.length === 0) return 0;
  return values.reduce((sum, v) => sum + v, 0) / values.length;
}

// Coefficient of variation (%), the homogeneity measure for a weigh-in (#2.3).
export function coefficientOfVariation(values: number[]) {
  const avg = mean(values);
  if (avg === 0) return 0;
  const variance = values.reduce((sum, v) => sum + (v - avg) ** 2, 0) / values.length;
  return (Math.sqrt(variance) / avg) * 100;
}

// Below this CV a lot is considered homogeneous.
export const HOMOGENEOUS_CV = 10;

// Gestation length in days (#3.3: configurable per farm).
export const DEFAULT_GESTATION_DAYS = 114;

const DAY_MS = 24 * 60 * 60 * 1000;

export function addDays(date: Date, days: number) {
  return new Date(date.getTime() + days * DAY_MS);
}

// Calendar days, ignoring time of day ("in 2 days" means two midnights away).
export function daysBetween(from: Date, to: Date) {
  const start = new Date(from.getFullYear(), from.getMonth(), from.getDate());
  const end = new Date(to.getFullYear(), to.getMonth(), to.getDate());
  return Math.round((end.getTime() - start.getTime()) / DAY_MS);
}

export function expectedFarrowing(serviceDate: Date, gestationDays = DEFAULT_GESTATION_DAYS) {
  return addDays(serviceDate, gestationDays);
}
