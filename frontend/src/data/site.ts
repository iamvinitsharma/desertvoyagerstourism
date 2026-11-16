// Business details. Later these come from the admin "Settings" page.
export const site = {
  name: 'Desert Voyagers Tourism',
  legalName: 'Desert Voyagers Tourism LLC',
  tagline: 'Desert safaris, city tours & attraction tickets across the UAE',
  phone: '+971 58 572 1121',
  phoneHref: 'tel:+971585721121',
  whatsapp: 'https://wa.me/971585721121?text=Hi%20Desert%20Voyagers%2C%20I%20would%20like%20to%20book%20a%20tour',
  email: 'info@desertvoyagerstourism.com',
  hours: 'Daily 10:00 am – 10:00 pm',
  address: 'Office 14, 2nd Floor, Omeir Bin Yousef Building, Hamdan Street, Abu Dhabi, UAE',
  social: {
    facebook: 'https://www.facebook.com/desertvoyagerstourism',
    instagram: 'https://www.instagram.com/voyagerstourismdesert/',
    youtube: 'https://www.youtube.com/@desertvoyagerstourism-yq6ds',
  },
  currency: 'AED',
  // Tripadvisor award — badge image: public/images/awards/tripadvisor-award.webp
  award: {
    name: 'Certificate of Excellence',
    year: 2026,
    href: 'https://www.tripadvisor.com/', // TODO: replace with your Tripadvisor listing URL
  },
};

export const nav = [
  { label: 'Home', href: '/' },
  { label: 'Desert Safari', href: '/tours?category=desert-safari' },
  { label: 'City Tours', href: '/tours?category=city-tours' },
  { label: 'Tickets', href: '/tickets' },
  { label: 'Adventures', href: '/tours?category=adventure' },
  { label: 'About', href: '/about' },
  { label: 'Contact', href: '/contact' },
];
