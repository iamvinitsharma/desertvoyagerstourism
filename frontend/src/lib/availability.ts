/**
 * SAMPLE availability (deterministic per product/date so it looks real and stays stable).
 * Shared by the calendar (browser) and the booking APIs (server).
 * Backend phase: replace with real stock from the admin (blocked dates, slot capacity).
 */
export type DayStatus = 'available' | 'limited' | 'soldout' | 'past';
export type SlotStatus = 'available' | 'limited' | 'soldout';

function rnd(seed: string) {
  let h = 2166136261;
  for (let i = 0; i < seed.length; i++) { h ^= seed.charCodeAt(i); h = Math.imul(h, 16777619); }
  return ((h >>> 0) % 1000) / 1000;
}

export const isoDate = (d: Date) => new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
export const MAX_DAYS_AHEAD = 180;

export function dayStatus(slug: string, iso: string, minIso: string): DayStatus {
  if (iso < minIso) return 'past';
  const r = rnd(`${slug}|${iso}`);
  if (r < 0.08) return 'soldout';
  if (r < 0.22) return 'limited';
  return 'available';
}

/** Live rules from the admin: every date/slot is available unless a rule says otherwise. */
export interface LiveAvailability { mode: 'live'; minNoticeDays: number; days: Record<string, string>; slots: Record<string, string> }

/** Status checker using live rules when available, else the sample generator. */
export function makeChecker(slug: string, live: LiveAvailability | null) {
  return {
    day(iso: string, minIso: string): DayStatus {
      if (!live) return dayStatus(slug, iso, minIso);
      if (iso < minIso) return 'past';
      return (live.days[iso] as DayStatus) ?? 'available';
    },
    slot(iso: string, slot: string): SlotStatus {
      if (!live) return slotStatus(slug, iso, slot);
      if (live.days[iso] === 'soldout') return 'soldout';
      return (live.slots[`${iso}|${slot}`] as SlotStatus) ?? 'available';
    },
  };
}

export function slotStatus(slug: string, iso: string, slot: string): SlotStatus {
  const r = rnd(`${slug}|${iso}|${slot}`);
  if (r < 0.15) return 'soldout';
  if (r < 0.35) return 'limited';
  return 'available';
}
