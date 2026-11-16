/**
 * SAMPLE DETAIL CONTENT — per-category defaults + per-product overrides.
 * In the admin every product will have its own gallery, inclusions, itinerary,
 * FAQs, prices, add-ons and time slots. Policies & add-on prices here are placeholders.
 */
import type { Tour } from './tours';

export type PriceKey = 'adult' | 'child' | 'infant';
export interface PriceRow { key: PriceKey; label: string; sub: string; price: number }
export interface Addon { key: string; label: string; sub?: string; price: number; per: 'booking' | 'person' }
export interface Details {
  gallery: string[];
  overview: string[];
  features: { icon: string; label: string }[];
  inclusions: string[];
  exclusions: string[];
  itinerary: { time: string; title: string; text: string }[];
  pickup?: { areas: string; time: string; note?: string };
  info: string[];
  cancellation: string[];
  faqs: { q: string; a: string }[];
  language: string;
  groupType: string;
  prices: PriceRow[];
  addons: Addon[];
  timeSlots: string[];
  minGuests: number;
}

const img = (f: string) => `/images/tours/${f}`;
const G = {
  safari: [img('image-for-half-day-desert-safari.png'), img('image-for-morning-desert-safari.png'), img('image-for-overnight-desert-safari1-jpg.webp'), img('image-for-sunrise-safari1.png'), img('image-for-dune-buggy-tour1-scaled.webp')],
  city: [img('grand-mosque-1.png'), img('qasr-al-watan.png'), img('sheikh-zayed-grand-mosque-jpg.webp'), img('louvre-museum-jpg.webp'), img('pexels-tommy-milanese-7891838-scaled.webp')],
  dubai: [img('dubai-mall-fountain-show-jpg.webp'), img('pexels-tommy-milanese-7891838-scaled.webp'), img('sheikh-zayed-grand-mosque-jpg.webp')],
};

const tourCancellation = [
  'Free cancellation up to 24 hours before the tour start time.',
  'Cancellations within 24 hours and no-shows are non-refundable.',
  'Tours may be rescheduled due to weather; you can choose another date or a full refund.',
];
const ticketCancellation = [
  'Tickets are issued for the selected date and time and are non-refundable once issued.',
  'Date changes may be possible up to 48 hours before your visit. Message us on WhatsApp.',
  'If the attraction closes or cancels, you receive a full refund.',
];

type Partial2 = Partial<Details>;

const byCategory: Record<string, Partial2> = {
  'desert-safari': {
    gallery: G.safari,
    features: [
      { icon: 'car', label: 'Hotel pickup by 4x4' }, { icon: 'sun', label: 'Desert sunset' },
      { icon: 'mountain', label: 'Dune bashing' }, { icon: 'users', label: 'Shared or private' },
    ],
    inclusions: ['Hotel or mall pickup & drop-off in a 4x4', 'Dune bashing with a licensed desert driver', 'Sandboarding', 'Short camel ride', 'Arabic coffee, tea & dates', 'Henna painting', 'Water & soft drinks'],
    exclusions: ['Quad biking (available as an add-on)', 'Alcoholic drinks', 'Tips and personal expenses'],
    pickup: { areas: 'Hotels and malls in Abu Dhabi city, Yas Island and Saadiyat', time: 'Exact pickup time is confirmed on WhatsApp the day before' },
    info: ['Avoid heavy meals 1–2 hours before dune bashing.', 'Wear comfortable clothes, sunglasses and closed shoes.', 'Dune bashing is not recommended for pregnant guests, guests with back problems or children under 3.', 'Vehicles are shared with other guests unless you book a private car.'],
    faqs: [
      { q: 'Is the safari suitable for children?', a: 'Yes. Children aged 3+ can join. For very young children we recommend a gentle drive. Let us know in your booking notes.' },
      { q: 'What should I wear?', a: 'Light, comfortable clothes, closed shoes and sunglasses. Evenings in winter can be cool, so bring a light jacket.' },
      { q: 'Can I book a private car?', a: 'Yes. Choose the Private 4x4 add-on and the whole vehicle is for your group.' },
      { q: 'How do I pay?', a: 'No online payment is needed for tours. We confirm your booking on WhatsApp and share the payment options.' },
    ],
    addons: [
      { key: 'private', label: 'Private 4x4 for your group', sub: 'Up to 6 guests', price: 650, per: 'booking' },
      { key: 'quad', label: 'Quad bike (30 min)', sub: 'Per rider', price: 150, per: 'person' },
    ],
    groupType: 'Shared group (private available)',
  },
  'city-tours': {
    gallery: G.city,
    features: [
      { icon: 'car', label: 'Hotel pickup' }, { icon: 'landmark', label: 'Top landmarks' },
      { icon: 'users', label: 'Expert local guide' }, { icon: 'clock', label: 'Flexible pace' },
    ],
    inclusions: ['Hotel pickup & drop-off in an air-conditioned vehicle', 'Professional English-speaking guide', 'Photo stops at major landmarks', 'Bottled water'],
    exclusions: ['Meals unless stated', 'Entry tickets unless stated', 'Tips and personal expenses'],
    pickup: { areas: 'Hotels in Abu Dhabi city, Yas Island and Saadiyat', time: 'Pickup time is confirmed on WhatsApp the day before' },
    info: ['Modest dress is required at the Grand Mosque: shoulders and knees covered; women should cover their hair (abayas available).', 'Opening hours of attractions may change on public holidays.', 'Carry your passport or Emirates ID.'],
    faqs: [
      { q: 'Is the Grand Mosque open every day?', a: 'It is open daily to visitors except Friday mornings. We plan your itinerary around opening hours.' },
      { q: 'Can the tour be private?', a: 'Yes. Choose the private car add-on and we customise stops to your interests.' },
      { q: 'How do I pay?', a: 'No online payment is needed for tours. We confirm on WhatsApp and share payment options.' },
    ],
    addons: [{ key: 'private', label: 'Private car & guide', sub: 'Up to 4 guests', price: 500, per: 'booking' }],
    groupType: 'Small group (private available)',
  },
  'adventure': {
    gallery: [img('image-for-dune-buggy-tour1-scaled.webp'), img('image-for-sunrise-safari1.png'), img('image-for-half-day-desert-safari.png'), img('image-for-morning-desert-safari.png')],
    features: [
      { icon: 'car', label: 'Hotel pickup' }, { icon: 'mountain', label: 'Desert adventure' },
      { icon: 'shield', label: 'Safety gear included' }, { icon: 'users', label: 'Expert guide' },
    ],
    inclusions: ['Hotel pickup & drop-off', 'Safety briefing and gear', 'Professional desert guide', 'Water'],
    exclusions: ['Personal expenses', 'Tips'],
    pickup: { areas: 'Hotels and malls in Abu Dhabi city', time: 'Pickup time is confirmed on WhatsApp the day before' },
    info: ['Drivers must be 18+ with a valid driving licence.', 'Wear closed shoes and comfortable clothing.'],
    faqs: [{ q: 'Do I need experience?', a: 'No. Your guide gives a full briefing and leads the group at a comfortable pace.' }],
    addons: [],
    groupType: 'Small group',
  },
  // tickets & theme parks
  'tickets': {
    features: [
      { icon: 'zap', label: 'Instant e-ticket' }, { icon: 'ticket', label: 'Mobile entry' },
      { icon: 'calendar', label: 'Choose your date' }, { icon: 'shield', label: 'Secure payment' },
    ],
    inclusions: ['Official entry ticket for the selected date', 'E-ticket by email and WhatsApp'],
    exclusions: ['Transport (ask us for a transfer)', 'Food and drinks'],
    info: ['Show the e-ticket on your phone at the entrance; printing is not needed.', 'Children under 3 usually enter free; carry ID for proof of age.', 'Arrive 15 minutes before your time slot.'],
    faqs: [
      { q: 'When do I receive my ticket?', a: 'Immediately after payment, by email and WhatsApp.' },
      { q: 'Can I change my date?', a: 'Message us on WhatsApp at least 48 hours before your visit and we’ll check availability.' },
    ],
    addons: [],
    groupType: 'Self-guided entry',
  },
  'theme-parks': {
    features: [
      { icon: 'zap', label: 'Instant e-ticket' }, { icon: 'ferris', label: 'Full-day access' },
      { icon: 'calendar', label: 'Choose your date' }, { icon: 'shield', label: 'Secure payment' },
    ],
    inclusions: ['Full-day park admission for the selected date', 'Access to all standard rides and shows', 'E-ticket by email and WhatsApp'],
    exclusions: ['Express / fast-track passes', 'Food, drinks and lockers', 'Transport (ask us for a transfer)'],
    info: ['Some rides have height and age restrictions.', 'Children under 3 usually enter free; carry ID.', 'Opening hours vary by season; check before you go.'],
    faqs: [
      { q: 'When do I receive my ticket?', a: 'Immediately after payment, by email and WhatsApp.' },
      { q: 'Can I combine parks?', a: 'Yes. Message us for multi-park tickets and transfers from your hotel.' },
    ],
    addons: [],
    groupType: 'Self-guided entry',
  },
};

const overrides: Record<string, Partial2> = {
  'evening-desert-safari': {
    overview: [
      'Swap the city skyline for endless red dunes on our most popular experience. Your driver picks you up in a 4x4 and heads into the desert for an adrenaline-filled dune bashing session.',
      'Watch the sun set over the sand, try sandboarding and a camel ride, then relax at our Bedouin-style camp with a BBQ buffet dinner and live Tanura, belly and fire shows under the stars.',
    ],
    itinerary: [
      { time: '3:00 pm', title: 'Hotel pickup', text: 'Your driver collects you from your hotel or mall in a 4x4.' },
      { time: '4:15 pm', title: 'Dune bashing', text: 'A thrilling drive over the red dunes with a photo stop at the top.' },
      { time: '5:15 pm', title: 'Sandboarding & sunset', text: 'Slide down the dunes and watch the sun set over the desert.' },
      { time: '6:00 pm', title: 'Camp activities', text: 'Camel ride, henna, Arabic coffee and traditional costume photos.' },
      { time: '7:00 pm', title: 'BBQ dinner & shows', text: 'Buffet dinner with Tanura, belly dance and fire shows.' },
      { time: '9:00 pm', title: 'Drop-off', text: 'Return transfer to your hotel or mall.' },
    ],
    inclusions: ['Hotel or mall pickup & drop-off in a 4x4', 'Dune bashing with a licensed desert driver', 'Sandboarding & sunset photo stop', 'Short camel ride', 'Arabic coffee, tea & dates', 'Henna painting & Arabic costume photos', 'BBQ buffet dinner (veg & non-veg)', 'Tanura, belly dance & fire shows', 'Water & soft drinks'],
  },
};

function defaultOverview(t: Tour): string[] {
  return [
    `${t.title} is one of our most loved experiences in ${t.location}. ${t.highlights.join(', ')}.`,
    t.type === 'ticket'
      ? 'Book online in a few clicks, pay securely and receive your e-ticket instantly on email and WhatsApp.'
      : 'Send a booking request in under a minute. Our team confirms your booking and pickup time on WhatsApp, with no prepayment needed.',
  ];
}

export function getDetails(t: Tour): Details {
  const cat = byCategory[t.category] ?? byCategory[t.type === 'ticket' ? 'tickets' : 'city-tours'];
  const ov = overrides[t.slug] ?? {};
  const isTicket = t.type === 'ticket';
  const childPrice = Math.round(t.price * (isTicket ? 0.8 : 0.75));
  const gallery = ov.gallery ?? (t.image ? [t.image, ...(cat.gallery ?? []).filter(g => g !== t.image)] : (cat.gallery ?? []));

  return {
    gallery: gallery.slice(0, 6),
    overview: ov.overview ?? defaultOverview(t),
    features: ov.features ?? cat.features ?? [],
    inclusions: ov.inclusions ?? cat.inclusions ?? [],
    exclusions: ov.exclusions ?? cat.exclusions ?? [],
    itinerary: ov.itinerary ?? [],
    pickup: t.pickup ? (ov.pickup ?? cat.pickup) : undefined,
    info: ov.info ?? cat.info ?? [],
    cancellation: isTicket ? ticketCancellation : tourCancellation,
    faqs: ov.faqs ?? cat.faqs ?? [],
    language: 'English, Arabic',
    groupType: ov.groupType ?? cat.groupType ?? '',
    prices: [
      { key: 'adult', label: 'Adult', sub: isTicket ? 'Age 12+' : 'Age 12+', price: t.price },
      { key: 'child', label: 'Child', sub: 'Age 3–11', price: childPrice },
      { key: 'infant', label: 'Infant', sub: 'Under 3', price: 0 },
    ],
    addons: ov.addons ?? cat.addons ?? [],
    // Ticket entry times (sample). Tours have fixed timings, so no slot picker.
    timeSlots: ov.timeSlots ?? (!isTicket ? []
      : t.slug === 'burj-khalifa-at-the-top' ? ['10:00', '12:00', '14:00', '16:00', '17:30', '19:00', '20:30', '22:00']
      : t.category === 'theme-parks' ? ['11:00', '12:00', '13:00', '14:00', '15:00', '16:00']
      : ['10:00', '11:30', '13:00', '14:30', '16:00', '17:30']),
    minGuests: 1,
  };
}
