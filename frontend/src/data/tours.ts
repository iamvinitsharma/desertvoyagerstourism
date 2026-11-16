/**
 * SAMPLE DATA — temporary until the Laravel admin/API is built.
 * Shapes match what the API will return, so pages won't change when we switch:
 * replace getTours()/getCategories() bodies with fetch(`${API_URL}/tours`).
 * Ticket prices & ratings below are placeholders — set real values in the admin.
 */

export type ProductType = 'tour' | 'ticket';

export interface Category {
  slug: string;
  name: string;
  icon: string;
  blurb: string;
}

export interface Tour {
  slug: string;
  type: ProductType;
  title: string;
  category: string;          // Category slug
  location: string;
  image?: string;
  duration: string;
  timing?: string;
  price: number;             // current "from" price per adult (AED)
  oldPrice?: number;         // shown struck-through when set
  rating: number;
  reviews: number;
  badge?: 'Best Seller' | 'Featured' | 'Limited Offer' | 'New';
  featured: boolean;
  pickup: boolean;
  instant: boolean;          // instant e-ticket confirmation
  highlights: string[];
}

export const categories: Category[] = [
  { slug: 'desert-safari', name: 'Desert Safari', icon: 'sun', blurb: 'Dune bashing, camels & BBQ camps' },
  { slug: 'city-tours', name: 'City Tours', icon: 'landmark', blurb: 'Abu Dhabi, Dubai & Al Ain' },
  { slug: 'tickets', name: 'Attraction Tickets', icon: 'ticket', blurb: 'Skip-the-line entry passes' },
  { slug: 'theme-parks', name: 'Theme Parks', icon: 'ferris', blurb: 'Yas Island & Dubai parks' },
  { slug: 'water', name: 'Cruises & Water', icon: 'waves', blurb: 'Dhow dinners & kayaking' },
  { slug: 'adventure', name: 'Adventures', icon: 'mountain', blurb: 'Buggies, quads & sunrise' },
];

const img = (f: string) => `/images/tours/${f}`;

export const tours: Tour[] = [
  {
    slug: 'evening-desert-safari', type: 'tour', title: 'Evening Desert Safari with BBQ Dinner',
    category: 'desert-safari', location: 'Abu Dhabi', image: img('image-for-half-day-desert-safari.png'),
    duration: '6 hours', timing: '3:00 pm – 9:00 pm', price: 170, oldPrice: 220, rating: 4.9, reviews: 412,
    badge: 'Best Seller', featured: true, pickup: true, instant: true,
    highlights: ['Dune bashing in a 4x4', 'Camel ride & sandboarding', 'BBQ buffet & live shows'],
  },
  {
    slug: 'morning-desert-safari', type: 'tour', title: 'Morning Desert Safari & Sandboarding',
    category: 'desert-safari', location: 'Abu Dhabi', image: img('image-for-morning-desert-safari.png'),
    duration: '3–4 hours', timing: '8:00 am – 11:30 am', price: 260, oldPrice: 310, rating: 4.8, reviews: 198,
    featured: true, pickup: true, instant: true,
    highlights: ['Red dune drive', 'Sandboarding', 'Camel farm visit'],
  },
  {
    slug: 'overnight-desert-safari', type: 'tour', title: 'Overnight Desert Safari under the Stars',
    category: 'desert-safari', location: 'Abu Dhabi', image: img('image-for-overnight-desert-safari1-jpg.webp'),
    duration: 'Overnight', timing: '3:30 pm – 10:00 am', price: 450, oldPrice: 500, rating: 4.9, reviews: 96,
    badge: 'Limited Offer', featured: true, pickup: true, instant: false,
    highlights: ['Bedouin camp stay', 'Dinner & breakfast', 'Sunrise over the dunes'],
  },
  {
    slug: 'full-day-abu-dhabi-city-tour', type: 'tour', title: 'Full Day Abu Dhabi City Tour with Qasr Al Watan',
    category: 'city-tours', location: 'Abu Dhabi', image: img('qasr-al-watan.png'),
    duration: '8.5 hours', timing: '9:00 am – 5:30 pm', price: 300, oldPrice: 350, rating: 4.8, reviews: 254,
    badge: 'Featured', featured: true, pickup: true, instant: true,
    highlights: ['Sheikh Zayed Grand Mosque', 'Qasr Al Watan palace', 'Heritage Village & Corniche'],
  },
  {
    slug: 'half-day-abu-dhabi-city-tour', type: 'tour', title: 'Half Day Abu Dhabi City Tour',
    category: 'city-tours', location: 'Abu Dhabi', image: img('grand-mosque-1.png'),
    duration: '4.5 hours', timing: '8:30 am – 1:00 pm', price: 150, oldPrice: 200, rating: 4.7, reviews: 187,
    featured: true, pickup: true, instant: true,
    highlights: ['Grand Mosque visit', 'Emirates Palace photo stop', 'Date market'],
  },
  {
    slug: 'al-ain-oasis-tour', type: 'tour', title: 'Full Day Al Ain Oasis Tour with Lunch',
    category: 'city-tours', location: 'Al Ain', image: img('al-ain-jahil-fort.png'),
    duration: '10 hours', timing: '8:30 am – 6:30 pm', price: 330, oldPrice: 370, rating: 4.7, reviews: 88,
    featured: false, pickup: true, instant: true,
    highlights: ['Al Jahili Fort', 'Al Ain Oasis walk', 'Lunch included'],
  },
  {
    slug: 'modern-dubai-tour', type: 'tour', title: 'Full Day Modern Dubai Tour from Abu Dhabi',
    category: 'city-tours', location: 'Dubai', image: img('dubai-mall-fountain-show-jpg.webp'),
    duration: '9 hours', timing: '11:00 am – 8:00 pm', price: 450, oldPrice: 500, rating: 4.8, reviews: 143,
    badge: 'Best Seller', featured: true, pickup: true, instant: true,
    highlights: ['Burj Khalifa & Dubai Mall', 'Dubai Fountain show', 'Palm Jumeirah'],
  },
  {
    slug: 'dune-buggy-tour', type: 'tour', title: 'Dune Buggy Adventure Tour',
    category: 'adventure', location: 'Abu Dhabi', image: img('image-for-dune-buggy-tour1-scaled.webp'),
    duration: '3 hours', timing: '8:00 am – 11:00 am', price: 1838, oldPrice: 1870, rating: 4.9, reviews: 61,
    badge: 'New', featured: true, pickup: true, instant: false,
    highlights: ['Self-drive buggy', 'Safety gear & briefing', 'Pro guide'],
  },
  {
    slug: 'sunrise-safari', type: 'tour', title: 'Sunrise Desert Safari',
    category: 'adventure', location: 'Abu Dhabi', image: img('image-for-sunrise-safari1.png'),
    duration: '4 hours', timing: '5:00 am – 9:00 am', price: 200, oldPrice: 250, rating: 4.8, reviews: 72,
    featured: true, pickup: true, instant: true,
    highlights: ['Sunrise over the dunes', 'Camel ride', 'Breakfast'],
  },

  // ---- Attraction tickets ----
  {
    slug: 'louvre-abu-dhabi-tickets', type: 'ticket', title: 'Louvre Abu Dhabi Entry Ticket',
    category: 'tickets', location: 'Saadiyat Island', image: img('louvre-museum-jpg.webp'),
    duration: 'Flexible', price: 63, rating: 4.9, reviews: 320,
    badge: 'Best Seller', featured: true, pickup: false, instant: true,
    highlights: ['Instant e-ticket', 'Valid on chosen date', 'Mobile ticket'],
  },
  {
    slug: 'qasr-al-watan-tickets', type: 'ticket', title: 'Qasr Al Watan Palace Ticket',
    category: 'tickets', location: 'Abu Dhabi', image: img('qasr-al-watan.png'),
    duration: '2–3 hours', price: 65, rating: 4.8, reviews: 140,
    featured: true, pickup: false, instant: true,
    highlights: ['Palace & gardens', 'Instant e-ticket', 'Mobile ticket'],
  },
  {
    slug: 'burj-khalifa-at-the-top', type: 'ticket', title: 'Burj Khalifa At The Top (Levels 124 & 125)',
    category: 'tickets', location: 'Dubai', image: img('dubai-mall-fountain-show-jpg.webp'),
    duration: '1–2 hours', price: 169, oldPrice: 189, rating: 4.9, reviews: 510,
    badge: 'Featured', featured: true, pickup: false, instant: true,
    highlights: ['Choose your time slot', 'Instant e-ticket', 'Skip the ticket line'],
  },
  {
    slug: 'ferrari-world-tickets', type: 'ticket', title: 'Ferrari World Yas Island Ticket',
    category: 'theme-parks', location: 'Yas Island',
    duration: 'Full day', price: 345, rating: 4.8, reviews: 230,
    featured: true, pickup: false, instant: true,
    highlights: ['Formula Rossa access', 'Instant e-ticket', 'Valid 1 day'],
  },
  {
    slug: 'warner-bros-world-tickets', type: 'ticket', title: 'Warner Bros. World Abu Dhabi Ticket',
    category: 'theme-parks', location: 'Yas Island',
    duration: 'Full day', price: 345, rating: 4.8, reviews: 175,
    featured: false, pickup: false, instant: true,
    highlights: ['Indoor theme park', 'Instant e-ticket', 'Valid 1 day'],
  },
  {
    slug: 'yas-waterworld-tickets', type: 'ticket', title: 'Yas Waterworld Ticket',
    category: 'theme-parks', location: 'Yas Island',
    duration: 'Full day', price: 310, rating: 4.7, reviews: 128,
    featured: false, pickup: false, instant: true,
    highlights: ['40+ rides & slides', 'Instant e-ticket', 'Valid 1 day'],
  },
];

export const reviewsSample = [
  { name: 'Guest review', country: 'United Kingdom', tour: 'Evening Desert Safari', rating: 5, text: 'Sample review — replace with a real guest review from the admin. Pickup was on time and the dune drive was the highlight of our trip.' },
  { name: 'Guest review', country: 'India', tour: 'Full Day Abu Dhabi City Tour', rating: 5, text: 'Sample review — replace with a real guest review from the admin. Our guide explained every stop and the palace was stunning.' },
  { name: 'Guest review', country: 'Germany', tour: 'Burj Khalifa At The Top', rating: 5, text: 'Sample review — replace with a real guest review from the admin. Tickets arrived on WhatsApp instantly, no queue at all.' },
];

/* ---- Data access (swap for API calls later) ---- */
export async function getTours(filter?: { type?: ProductType; category?: string; featured?: boolean }) {
  return tours.filter(t =>
    (!filter?.type || t.type === filter.type) &&
    (!filter?.category || t.category === filter.category) &&
    (filter?.featured === undefined || t.featured === filter.featured));
}
export async function getCategories() { return categories; }
export const discountPct = (t: Tour) => t.oldPrice ? Math.round((1 - t.price / t.oldPrice) * 100) : 0;
export const formatAED = (n: number) => `AED ${n.toLocaleString('en-AE')}`;
