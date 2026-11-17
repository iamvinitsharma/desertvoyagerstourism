# Desert Voyagers Tourism — Website

New website for Desert Voyagers Tourism LLC (Abu Dhabi): desert safaris, city tours and attraction tickets.

- **Tours** are booked by request (no online payment). The team confirms on WhatsApp.
- **Tickets** are bought online (date, time slot, cart, checkout). Payment via Network International (N-Genius) is in demo mode until the backend is connected.

## Structure

| Folder | What it is |
| --- | --- |
| `frontend/` | Astro 7 (server-rendered, Node adapter) + Tailwind CSS v4. Reads live data from the backend API (cached 30s), falls back to built-in sample data |
| `backend/` | Laravel 12 + Filament 5 admin + MySQL: tours, tickets, availability, tour requests, ticket orders, reviews, reels, blog, settings, plus the public API at `/api` |

## Run the backend (admin + API)

Requires PHP 8.2+, Composer and MySQL/MariaDB.

```bash
cd backend
composer install
cp .env.example .env            # set DB_*, APP_URL, FRONTEND_URL, ADMIN_EMAIL, ADMIN_PASSWORD
php artisan key:generate
php artisan migrate
node database/seeders/export-frontend-data.mjs   # copies sample content + images from frontend/
php artisan db:seed             # admin user, sample catalogue, demo bookings
php artisan storage:link
php artisan serve --port=8000   # admin: http://localhost:8000/admin
```

The website finds the API via `API_URL` (default `http://127.0.0.1:8000/api`).

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
