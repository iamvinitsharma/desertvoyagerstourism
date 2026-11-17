/**
 * Live data from the Laravel admin API, with the built-in sample data as fallback.
 *
 * The shared arrays/objects exported by src/data/*.ts (tours, categories, reviews, reels,
 * posts, site) are updated IN PLACE, so every component keeps importing them as before.
 * src/middleware.ts calls ensureData() before each request; results are cached for 30s.
 */
import { tours, categories, type Tour, type Category } from '../data/tours';
import { setLiveDetails, type Details } from '../data/details';
import { reviews, reels, reviewSummary } from '../data/reviews';
import { posts } from '../data/blog';
import { site } from '../data/site';
import { STATIC_PREVIEW } from './preview';

export const API_URL = (process.env.API_URL || import.meta.env.API_URL || 'http://127.0.0.1:8000/api').replace(/\/$/, '');
const TTL = 30_000;

// Keep pristine copies of the sample data for the fallback
const sample = {
  tours: structuredClone(tours), categories: structuredClone(categories), reviews: structuredClone(reviews),
  reels: structuredClone(reels), reviewSummary: structuredClone(reviewSummary), posts: structuredClone(posts),
  site: structuredClone(site),
};

let source: 'api' | 'sample' = 'sample';
let loadedAt = 0;
let inflight: Promise<void> | null = null;
let warned = false;

export const dataSource = () => source;

const replace = <T>(arr: T[], items: T[]) => { arr.splice(0, arr.length, ...items); };

async function getJson(path: string, timeout = 4000) {
  const res = await fetch(`${API_URL}${path}`, { signal: AbortSignal.timeout(timeout), headers: { Accept: 'application/json' } });
  if (!res.ok) throw new Error(`${path} → HTTP ${res.status}`);
  return res.json();
}

function applySample() {
  replace(tours, structuredClone(sample.tours));
  replace(categories, structuredClone(sample.categories));
  replace(reviews, structuredClone(sample.reviews));
  replace(reels, structuredClone(sample.reels));
  replace(posts, structuredClone(sample.posts));
  Object.assign(reviewSummary, structuredClone(sample.reviewSummary));
  Object.assign(site, structuredClone(sample.site));
  setLiveDetails(null);
  source = 'sample';
}

async function load() {
  try {
    const [cat, content] = await Promise.all([getJson('/catalog'), getJson('/content')]);
    const details: Record<string, Details> = {};
    const list: Tour[] = cat.products.map((p: any) => { details[p.slug] = p.details; const { details: _d, seo: _s, ...t } = p; return t; });

    replace(categories, cat.categories as Category[]);
    replace(tours, list);
    setLiveDetails(details);
    replace(reviews, content.reviews);
    replace(reels, content.reels);
    replace(posts, content.posts);
    Object.assign(reviewSummary, content.reviewSummary);
    const s = cat.site;
    Object.assign(site, {
      legalName: s.legalName || site.legalName, phone: s.phone || site.phone, phoneHref: s.phoneHref || site.phoneHref,
      whatsapp: s.whatsapp || site.whatsapp, whatsappNumber: s.whatsappNumber || site.whatsappNumber,
      email: s.email || site.email, hours: s.hours || site.hours, address: s.address || site.address,
      social: { ...site.social, ...Object.fromEntries(Object.entries(s.social || {}).filter(([, v]) => v)) },
      award: { ...site.award, ...Object.fromEntries(Object.entries(s.award || {}).filter(([, v]) => v)) },
    });
    source = 'api';
    warned = false;
  } catch (err) {
    if (!warned) { console.warn(`[data] Admin API unavailable (${(err as Error).message}). Using sample data.`); warned = true; }
    if (source !== 'api') applySample(); // keep the last good live data if we had it
  }
  loadedAt = Date.now();
}

/** Make sure data is fresh (≤30s old). Safe to call on every request. */
export async function ensureData(): Promise<void> {
  if (STATIC_PREVIEW) return; // GitHub Pages preview always uses sample data
  if (Date.now() - loadedAt < TTL) return;
  inflight ??= load().finally(() => { inflight = null; });
  return inflight;
}

/** Live availability overrides for one product (no cache), or null to use the sample generator. */
export async function getAvailability(slug: string): Promise<{ mode: 'live'; minNoticeDays: number; days: Record<string, string>; slots: Record<string, string> } | null> {
  if (source !== 'api') return null;
  try {
    const a = await getJson(`/availability/${encodeURIComponent(slug)}`, 3000);
    return { mode: 'live', minNoticeDays: a.minNoticeDays ?? 0, days: a.days || {}, slots: Array.isArray(a.slots) ? {} : a.slots || {} };
  } catch { return null; }
}

/** POST to the admin API (server-side only). Returns { status, data }. */
export async function postApi(path: string, body: unknown) {
  const res = await fetch(`${API_URL}${path}`, {
    method: 'POST', signal: AbortSignal.timeout(8000),
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' }, body: JSON.stringify(body),
  });
  let data: any = null;
  try { data = await res.json(); } catch {}
  return { status: res.status, data: data ?? { ok: false, error: 'Unexpected response from booking system' } };
}

export async function getApi(path: string) {
  const res = await fetch(`${API_URL}${path}`, { signal: AbortSignal.timeout(5000), headers: { Accept: 'application/json' } });
  return { status: res.status, data: await res.json().catch(() => null) };
}
