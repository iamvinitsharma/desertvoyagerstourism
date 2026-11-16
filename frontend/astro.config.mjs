// @ts-check
import { defineConfig } from 'astro/config';

import tailwindcss from '@tailwindcss/vite';
import node from '@astrojs/node';

// PAGES=true → static preview build for GitHub Pages (no server: forms/checkout run in demo mode).
// Default → server-rendered Node app (Hostinger Business / Cloud), so admin changes appear instantly.
const pages = process.env.PAGES === 'true';

export default defineConfig(pages
  ? {
      site: 'https://iamvinitsharma.github.io',
      base: '/desertvoyagerstourism',
      output: 'static',
      vite: { plugins: [tailwindcss()] },
    }
  : {
      site: 'https://desertvoyagerstourism.com',
      output: 'server',
      vite: { plugins: [tailwindcss()] },
      adapter: node({ mode: 'standalone' }),
    });
