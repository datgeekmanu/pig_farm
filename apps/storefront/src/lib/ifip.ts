// Growth reference band for fattening pigs, by week since the lot started (#2.4).
// Indicative values only: replace with the official IFIP table before release.
const START_KG = 22;
const WEEKLY_GAIN_KG = 4.8;
const BAND_KG = 4;

export function ifipReferenceKg(week: number) {
  return START_KG + WEEKLY_GAIN_KG * week;
}

export function ifipBand(week: number) {
  const mid = ifipReferenceKg(week);
  return { low: mid - BAND_KG, high: mid + BAND_KG };
}
