// Ticket catalog used by cart/checkout pages (client display) and the checkout API (authoritative pricing).
import { tours } from '../data/tours';
import { getDetails } from '../data/details';

export interface CatalogEntry {
  title: string; image?: string; location: string; href: string;
  prices: { adult: number; child: number; infant: number };
  timeSlots: string[];
}

export function ticketCatalog(): Record<string, CatalogEntry> {
  const out: Record<string, CatalogEntry> = {};
  for (const t of tours.filter(x => x.type === 'ticket')) {
    const d = getDetails(t);
    const p = Object.fromEntries(d.prices.map(r => [r.key, r.price])) as CatalogEntry['prices'];
    out[t.slug] = { title: t.title, image: t.image, location: t.location, href: `/tickets/${t.slug}`, prices: p, timeSlots: d.timeSlots };
  }
  return out;
}
