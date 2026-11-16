/**
 * SAMPLE blog posts — general travel tips written as placeholders.
 * Later managed in the admin (title, slug, cover, category, body, SEO fields).
 */
export interface Post {
  slug: string;
  title: string;
  excerpt: string;
  image: string;
  category: string;
  date: string;      // ISO
  readTime: string;
  related: string[]; // tour/ticket hrefs
  body: { h?: string; p: string[] }[];
}

const img = (f: string) => `/images/tours/${f}`;

export const posts: Post[] = [
  {
    slug: 'evening-vs-morning-desert-safari',
    title: 'Evening vs morning desert safari: which one should you book?',
    excerpt: 'Sunset BBQ camp or cool morning dunes? Here’s how the two most popular safaris compare, and who each one suits best.',
    image: img('image-for-half-day-desert-safari.png'), category: 'Desert Safari', date: '2026-10-28', readTime: '5 min read',
    related: ['/tours/evening-desert-safari', '/tours/morning-desert-safari'],
    body: [
      { p: ['Both safaris include the thrill of dune bashing in a 4x4, but the experience around it is quite different. Choosing the right one depends on your schedule, the weather and who you are travelling with.'] },
      { h: 'The evening safari', p: ['The evening safari is the classic choice. You head into the desert in the afternoon, enjoy dune bashing and sandboarding, watch the sunset and finish with dinner and live entertainment at a desert camp.', 'It is ideal for first-time visitors, families and groups who want the full experience in one trip.'] },
      { h: 'The morning safari', p: ['The morning safari is shorter and quieter. The dunes are cooler, the light is soft and you are back at your hotel by lunchtime, leaving the rest of the day free.', 'It suits travellers with tight schedules, photographers and anyone who prefers to skip the big camp dinner.'] },
      { h: 'Our tip', p: ['In the hotter months, a morning safari starting early is more comfortable. In winter, the evening safari is hard to beat. If you can’t decide, message us on WhatsApp and we’ll help you pick.'] },
    ],
  },
  {
    slug: 'grand-mosque-visitor-guide',
    title: 'Visiting Sheikh Zayed Grand Mosque: dress code & tips',
    excerpt: 'What to wear, the best time for photos and how to plan your visit to Abu Dhabi’s most iconic landmark.',
    image: img('pexels-tommy-milanese-7891838-scaled.webp'), category: 'City Guide', date: '2026-10-12', readTime: '4 min read',
    related: ['/tours/full-day-abu-dhabi-city-tour', '/tours/half-day-abu-dhabi-city-tour'],
    body: [
      { p: ['The Sheikh Zayed Grand Mosque is one of the most beautiful buildings in the world and a highlight of any Abu Dhabi trip. A little preparation makes the visit smooth and respectful.'] },
      { h: 'What to wear', p: ['Modest dress is required. Clothing should be loose and cover the arms and legs. Women should also cover their hair. If you are unsure, our guides will advise you before you arrive.'] },
      { h: 'Best time for photos', p: ['Late afternoon gives warm light on the white marble, and staying until dusk lets you see the mosque lit up. Mornings are usually quieter.'] },
      { h: 'Make it easy', p: ['Our Abu Dhabi city tours include hotel pickup and time at the mosque with a guide who explains its history and architecture.'] },
    ],
  },
  {
    slug: 'burj-khalifa-best-time-to-visit',
    title: 'Burj Khalifa At The Top: which time slot is best?',
    excerpt: 'Daytime views, golden hour or city lights? How to choose your Burj Khalifa time slot, and why booking ahead matters.',
    image: img('dubai-mall-fountain-show-jpg.webp'), category: 'Tickets', date: '2026-09-30', readTime: '3 min read',
    related: ['/tickets/burj-khalifa-at-the-top', '/tours/modern-dubai-tour'],
    body: [
      { p: ['A trip to the observation decks of the world’s tallest building is a must in Dubai. The time you choose changes the experience completely.'] },
      { h: 'Daytime', p: ['Clear views across the city, the coastline and the desert beyond. Great for spotting landmarks.'] },
      { h: 'Sunset', p: ['The most popular slot: watch the city turn gold and then light up. These times sell out first, so book early.'] },
      { h: 'Evening', p: ['Sparkling city lights and the fountain show below. A relaxed option after dinner at Dubai Mall.'] },
      { h: 'Book online', p: ['Buy your ticket online, choose your time slot and receive your e-ticket instantly by email and WhatsApp.'] },
    ],
  },
  {
    slug: 'what-to-wear-desert-safari',
    title: 'What to wear on a desert safari (and what to leave at the hotel)',
    excerpt: 'Comfortable clothes, the right shoes and a few small extras that make a big difference out on the dunes.',
    image: img('image-for-morning-desert-safari.png'), category: 'Travel Tips', date: '2026-09-14', readTime: '4 min read',
    related: ['/tours/evening-desert-safari', '/tours/overnight-desert-safari'],
    body: [
      { p: ['Dressing right keeps you comfortable in the heat, the sand and the cooler desert evenings.'] },
      { h: 'Clothing', p: ['Light, breathable clothes in natural fabrics work best. Long, loose trousers or skirts protect against sun and sand.'] },
      { h: 'Shoes', p: ['Closed shoes or sturdy sandals. Sand gets everywhere, so many guests go barefoot on the dunes and keep shoes for the camp.'] },
      { h: 'Small extras', p: ['Sunglasses, sunscreen, a light jacket for winter evenings and a small bag for your phone. Water is provided on our safaris.'] },
    ],
  },
];

export const formatDate = (iso: string) => new Date(iso + 'T00:00').toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
