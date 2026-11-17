/**
 * SAMPLE reviews & reels — placeholders until real ones are added in the admin.
 * Do NOT launch with these: replace with genuine guest reviews/videos (with permission).
 * Reels accept either an uploaded MP4 (`video`) or a YouTube/Shorts id (`youtube`).
 */

export interface Reel {
  title: string;
  guest: string;
  tour: string;
  tourHref: string;
  poster: string | null;
  duration: string;
  video?: string | null;   // uploaded MP4 (admin)
  youtube?: string | null; // YouTube / Shorts video id
}

export interface Review {
  name: string;
  country: string;
  flag: string;
  date: string;
  tour: string;
  tourHref: string;
  rating: number;
  title: string;
  text: string;
}

const img = (f: string) => `/images/tours/${f}`;
// Desert Voyagers' own YouTube video, used for every sample reel until real reels are uploaded.
const OWN_VIDEO = 'pgq0S8YABu0';

export const reels: Reel[] = [
  { title: 'Dune bashing at sunset', guest: 'Family from UK', tour: 'Evening Desert Safari', tourHref: '/tours/evening-desert-safari', poster: img('image-for-half-day-desert-safari.png'), duration: '0:32', youtube: OWN_VIDEO },
  { title: 'Our first camel ride', guest: 'Couple from India', tour: 'Morning Desert Safari', tourHref: '/tours/morning-desert-safari', poster: img('image-for-morning-desert-safari.png'), duration: '0:24', youtube: OWN_VIDEO },
  { title: 'Inside the Grand Mosque', guest: 'Friends from Germany', tour: 'Abu Dhabi City Tour', tourHref: '/tours/full-day-abu-dhabi-city-tour', poster: img('pexels-tommy-milanese-7891838-scaled.webp'), duration: '0:41', youtube: OWN_VIDEO },
  { title: 'Dubai Fountain at night', guest: 'Family from Saudi Arabia', tour: 'Modern Dubai Tour', tourHref: '/tours/modern-dubai-tour', poster: img('dubai-mall-fountain-show-jpg.webp'), duration: '0:28', youtube: OWN_VIDEO },
  { title: 'Buggy racing the dunes', guest: 'Group from Philippines', tour: 'Dune Buggy Adventure', tourHref: '/tours/dune-buggy-tour', poster: img('image-for-dune-buggy-tour1-scaled.webp'), duration: '0:36', youtube: OWN_VIDEO },
  { title: 'Night at the Bedouin camp', guest: 'Couple from France', tour: 'Overnight Desert Safari', tourHref: '/tours/overnight-desert-safari', poster: img('image-for-overnight-desert-safari1-jpg.webp'), duration: '0:30', youtube: OWN_VIDEO },
];

export const reviewSummary = { average: 4.9, total: 412, breakdown: [92, 6, 1, 1, 0] }; // % for 5★..1★

export const reviews: Review[] = [
  { name: 'Sarah M.', country: 'United Kingdom', flag: '🇬🇧', date: 'Oct 2026', tour: 'Evening Desert Safari', tourHref: '/tours/evening-desert-safari', rating: 5,
    title: 'Best evening of our holiday', text: 'Pickup was right on time and our driver was brilliant: fun on the dunes but always safe. The sunset was unreal and the BBQ and fire show at camp were the perfect end. The kids still talk about the sandboarding.' },
  { name: 'Rahul & Priya', country: 'India', flag: '🇮🇳', date: 'Oct 2026', tour: 'Full Day Abu Dhabi City Tour', tourHref: '/tours/full-day-abu-dhabi-city-tour', rating: 5,
    title: 'Guide made the city come alive', text: 'Our guide explained the history at every stop and gave us plenty of time for photos at the Grand Mosque and Qasr Al Watan. Comfortable car, cold water, zero stress. Highly recommend for first-timers.' },
  { name: 'Jonas K.', country: 'Germany', flag: '🇩🇪', date: 'Sep 2026', tour: 'Burj Khalifa At The Top', tourHref: '/tickets/burj-khalifa-at-the-top', rating: 5,
    title: 'Tickets on WhatsApp in seconds', text: 'Booked the night before, paid online and the e-ticket arrived on WhatsApp straight away. No queue at the counter, we just scanned and went up. Easy.' },
  { name: 'Aisha A.', country: 'Saudi Arabia', flag: '🇸🇦', date: 'Sep 2026', tour: 'Overnight Desert Safari', tourHref: '/tours/overnight-desert-safari', rating: 5,
    title: 'Magical night under the stars', text: 'The camp was clean and peaceful, dinner was delicious and waking up to the sunrise over the dunes was unforgettable. The team handled everything for our family.' },
  { name: 'Marie L.', country: 'France', flag: '🇫🇷', date: 'Aug 2026', tour: 'Morning Desert Safari', tourHref: '/tours/morning-desert-safari', rating: 4,
    title: 'Great morning in the desert', text: 'Lovely red dunes and the camel farm was a highlight. It was hot by 11 am, so go early in summer. Friendly driver and good communication on WhatsApp before the tour.' },
  { name: 'Carlo D.', country: 'Philippines', flag: '🇵🇭', date: 'Aug 2026', tour: 'Dune Buggy Adventure', tourHref: '/tours/dune-buggy-tour', rating: 5,
    title: 'Pure adrenaline!', text: 'Proper buggies, full safety briefing and the guide picked awesome routes for our group. Worth every dirham. We will book again for quad bikes next time.' },
  { name: 'Emily R.', country: 'United States', flag: '🇺🇸', date: 'Jul 2026', tour: 'Louvre Abu Dhabi Entry Ticket', tourHref: '/tickets/louvre-abu-dhabi-tickets', rating: 5,
    title: 'Smooth and simple', text: 'Bought tickets on my phone in two minutes and walked straight in. The team also arranged a taxi back to our hotel when we asked on WhatsApp. Very helpful.' },
];
