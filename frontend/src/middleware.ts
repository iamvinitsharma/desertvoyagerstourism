import { defineMiddleware } from 'astro:middleware';
import { ensureData } from './lib/data';

// Load (cached) live data from the admin API before every page / endpoint renders.
export const onRequest = defineMiddleware(async (_context, next) => {
  await ensureData();
  return next();
});
