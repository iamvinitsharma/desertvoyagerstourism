// Exports the website's sample data (frontend/src/data/*.ts) to JSON for the Laravel seeder.
// Usage (Node 22.6+ strips TypeScript types natively):  node database/seeders/export-frontend-data.mjs
import { writeFileSync, mkdirSync, copyFileSync, readdirSync } from 'node:fs';
import { fileURLToPath, pathToFileURL } from 'node:url';
import { dirname, join } from 'node:path';

const here = dirname(fileURLToPath(import.meta.url));
const front = join(here, '../../../frontend/src/data');
const load = f => import(pathToFileURL(join(front, f)).href);

const { tours, categories } = await load('tours.ts');
const { getDetails } = await load('details.ts');
const { reviews, reels, reviewSummary } = await load('reviews.ts');
const { posts } = await load('blog.ts');
const { site } = await load('site.ts');

const media = p => (p ? 'media/' + p.split('/').pop() : null);

const out = {
  categories,
  products: tours.map((t, i) => {
    const d = getDetails(t);
    return {
      ...t, sort_order: i, details: { ...d, gallery: d.gallery.map(media) }, cover_image: media(t.image),
    };
  }),
  reviews, reviewSummary,
  reels: reels.map(r => ({ ...r, poster: media(r.poster) })),
  posts: posts.map(p => ({ ...p, image: media(p.image) })),
  site,
};

mkdirSync(join(here, 'data'), { recursive: true });
writeFileSync(join(here, 'data/website-sample.json'), JSON.stringify(out, null, 2));

// Copy images into storage/app/public/media (served at /storage/media/... after `php artisan storage:link`)
const src = join(here, '../../../frontend/public/images/tours');
const dst = join(here, '../../storage/app/public/media');
mkdirSync(dst, { recursive: true });
for (const f of readdirSync(src)) copyFileSync(join(src, f), join(dst, f));
mkdirSync(join(here, '../../public/images'), { recursive: true });
copyFileSync(join(here, '../../../frontend/public/images/logo.png'), join(here, '../../public/images/logo.png'));

console.log(`exported ${out.products.length} products, ${out.reviews.length} reviews, ${out.reels.length} reels, ${out.posts.length} posts`);
