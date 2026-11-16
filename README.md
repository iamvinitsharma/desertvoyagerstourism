# Desert Voyagers Tourism — Website

New website for Desert Voyagers Tourism LLC (Abu Dhabi): desert safaris, city tours and attraction tickets.

- **Tours** are booked by request (no online payment). The team confirms on WhatsApp.
- **Tickets** are bought online (date, time slot, cart, checkout). Payment via Network International (N-Genius) is in demo mode until the backend is connected.

## Structure

| Folder | What it is |
| --- | --- |
| `frontend/` | Astro 7 (server-rendered, Node adapter) + Tailwind CSS v4 |
| `backend/` | *(planned)* Laravel + Filament admin + MySQL, for tours, tickets, bookings, reviews, blog |

## Run locally

Requires Node.js 22+.

```bash
cd frontend
npm install
npm run dev        # http://localhost:4321
```

Production build (how Hostinger Node.js hosting runs it):

```bash
npm run build
npm start          # serves dist/ on http://localhost:4321
```

Add `?intro` to the URL to replay the desert loading animation.

## Content status

Tours, tickets, reviews, reels, blog posts, availability and policy texts in `frontend/src/data/` are **sample data** until the admin backend is built. Replace them with real content before launch.
