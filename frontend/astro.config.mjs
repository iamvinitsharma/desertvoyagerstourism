// @ts-check
import { defineConfig } from 'astro/config';

import tailwindcss from '@tailwindcss/vite';
import node from '@astrojs/node';

// Server-rendered so tours/tickets added in the admin appear instantly (no rebuild).
// Runs as a Node.js app on Hostinger Business / Cloud.
export default defineConfig({
  site: 'https://desertvoyagerstourism.com',
  output: 'server',
  vite: {
    plugins: [tailwindcss()]
  },
  adapter: node({
    mode: 'standalone'
  })
});
