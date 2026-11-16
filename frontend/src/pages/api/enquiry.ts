import type { APIRoute } from 'astro';
import { tours } from '../../data/tours';
import { getDetails } from '../../data/details';
import { newRef, save } from '../../lib/store';
import { dayStatus, isoDate } from '../../lib/availability';

const json = (body: unknown, status = 200) =>
  new Response(JSON.stringify(body), { status, headers: { 'Content-Type': 'application/json' } });

const str = (v: unknown, max = 200) => (typeof v === 'string' ? v.trim().slice(0, max) : '');
const int = (v: unknown, min: number, max: number) => {
  const n = Math.floor(Number(v)); return Number.isFinite(n) ? Math.max(min, Math.min(max, n)) : min;
};

/** Tour booking request — no payment. Totals are recomputed here, never trusted from the browser. */
export const POST: APIRoute = async ({ request }) => {
  let b: Record<string, unknown>;
  try { b = await request.json(); } catch { return json({ ok: false, error: 'Invalid request' }, 400); }
  if (str(b.website)) return json({ ok: true, ref: 'DV-OK' }); // honeypot: silently accept bots

  const tour = tours.find(t => t.slug === str(b.slug) && t.type === 'tour');
  if (!tour) return json({ ok: false, error: 'Tour not found' }, 404);

  const name = str(b.name, 100), email = str(b.email, 150), phone = str(b.phone, 40), date = str(b.date, 10);
  if (!name || !phone) return json({ ok: false, error: 'Name and WhatsApp number are required' }, 422);
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return json({ ok: false, error: 'Please enter a valid email' }, 422);
  if (!/^[+\d][\d\s()-]{6,}$/.test(phone)) return json({ ok: false, error: 'Please enter a valid phone number' }, 422);
  const tmr = new Date(); tmr.setDate(tmr.getDate() + 1);
  const minIso = isoDate(tmr);
  if (!/^\d{4}-\d{2}-\d{2}$/.test(date) || date < minIso) return json({ ok: false, error: 'Please choose a valid future date' }, 422);
  if (dayStatus(tour.slug, date, minIso) === 'soldout') return json({ ok: false, error: 'Sorry, that date is fully booked. Please choose another date' }, 409);

  const d = getDetails(tour);
  const qty = { adult: int(b.adult, 1, 30), child: int(b.child, 0, 30), infant: int(b.infant, 0, 30) };
  const addonKeys = Array.isArray(b.addons) ? b.addons.map(x => String(x)) : [];
  const addons = d.addons.filter(a => addonKeys.includes(a.key));
  const pax = qty.adult + qty.child;
  const total =
    d.prices.reduce((s, p) => s + p.price * qty[p.key], 0) +
    addons.reduce((s, a) => s + (a.per === 'person' ? a.price * pax : a.price), 0);

  const ref = newRef('DV');
  await save('enquiries', {
    ref, createdAt: new Date().toISOString(), status: 'new',
    tour: { slug: tour.slug, title: tour.title }, date, qty, addons: addons.map(a => a.key),
    estimatedTotal: total, currency: 'AED',
    customer: { name, email, phone, hotel: str(b.hotel), notes: str(b.notes, 1000) },
  });
  // TODO (backend): email the team + customer, push to admin, WhatsApp notification.

  return json({ ok: true, ref, tour: tour.title, total });
};
