import type { APIRoute } from 'astro';
import { ticketCatalog } from '../../lib/catalog';
import { newRef, save } from '../../lib/store';
import { dayStatus, slotStatus, isoDate } from '../../lib/availability';

const json = (body: unknown, status = 200) =>
  new Response(JSON.stringify(body), { status, headers: { 'Content-Type': 'application/json' } });
const str = (v: unknown, max = 200) => (typeof v === 'string' ? v.trim().slice(0, max) : '');
const int = (v: unknown) => { const n = Math.floor(Number(v)); return Number.isFinite(n) ? Math.max(0, Math.min(20, n)) : 0; };

/**
 * Ticket checkout. Validates the cart and recomputes every price server-side.
 * DEMO MODE: no payment gateway yet — returns the success page as "paymentUrl".
 * Backend phase: create a Network International (N-Genius) order here and return its
 * hosted payment page URL; mark the order paid only after verifying it server-side.
 */
export const POST: APIRoute = async ({ request }) => {
  let b: any;
  try { b = await request.json(); } catch { return json({ ok: false, error: 'Invalid request' }, 400); }

  const catalog = ticketCatalog();
  const today = isoDate(new Date());
  if (!Array.isArray(b.items) || b.items.length === 0 || b.items.length > 20) return json({ ok: false, error: 'Your cart is empty' }, 422);

  const lines = [];
  for (const raw of b.items) {
    const c = catalog[str(raw?.slug)];
    if (!c) return json({ ok: false, error: 'A ticket in your cart is no longer available' }, 422);
    const date = str(raw.date, 10);
    if (!/^\d{4}-\d{2}-\d{2}$/.test(date) || date < today) return json({ ok: false, error: `Please choose a future date for ${c.title}` }, 422);
    if (dayStatus(str(raw.slug), date, today) === 'soldout') return json({ ok: false, error: `${c.title} is sold out on that date. Please pick another date` }, 409);
    const time = str(raw.time, 10);
    if (c.timeSlots.length && !c.timeSlots.includes(time)) return json({ ok: false, error: `Please choose a visit time for ${c.title}` }, 422);
    if (c.timeSlots.length && slotStatus(str(raw.slug), date, time) === 'soldout') return json({ ok: false, error: `The ${time} slot for ${c.title} is sold out. Please pick another time` }, 409);
    const qty = { adult: int(raw.qty?.adult), child: int(raw.qty?.child), infant: int(raw.qty?.infant) };
    if (qty.adult + qty.child === 0) return json({ ok: false, error: `Add at least one adult or child ticket for ${c.title}` }, 422);
    if (qty.infant && !qty.adult) return json({ ok: false, error: 'Infants must be accompanied by an adult' }, 422);
    const amount = qty.adult * c.prices.adult + qty.child * c.prices.child + qty.infant * c.prices.infant;
    lines.push({ slug: str(raw.slug), title: c.title, date, time: c.timeSlots.length ? time : undefined, qty, unit: c.prices, amount });
  }

  const cu = b.customer ?? {};
  const customer = { firstName: str(cu.firstName, 60), lastName: str(cu.lastName, 60), email: str(cu.email, 150), phone: str(cu.phone, 40), notes: str(cu.notes, 1000) };
  if (!customer.firstName || !customer.lastName) return json({ ok: false, error: 'Please enter your full name' }, 422);
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(customer.email)) return json({ ok: false, error: 'Please enter a valid email' }, 422);
  if (!/^\+\d{1,4} [\d\s()-]{6,}$/.test(customer.phone)) return json({ ok: false, error: 'Please enter a valid mobile number' }, 422);
  if (b.terms !== true) return json({ ok: false, error: 'Please accept the terms' }, 422);

  const total = lines.reduce((s, l) => s + l.amount, 0);
  const ref = newRef('DT');
  await save('orders', {
    ref, createdAt: new Date().toISOString(), status: 'paid_demo', currency: 'AED', total, lines, customer,
    payment: { provider: 'ngenius', mode: 'demo' },
  });

  return json({ ok: true, ref, total, paymentUrl: `/checkout/success?ref=${encodeURIComponent(ref)}` });
};
