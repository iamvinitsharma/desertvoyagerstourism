// Builds the static GitHub Pages preview (no server routes), then prefixes links with the repo base path.
import { execSync } from 'node:child_process';
import { rename, rm, access } from 'node:fs/promises';

const api = 'src/pages/api';
const parked = '.api-parked';
const exists = p => access(p).then(() => true, () => false);

// Server-only API routes can't exist in a static site; park them during the build.
if (await exists(api)) await rename(api, parked);
try {
  await rm('dist', { recursive: true, force: true });
  execSync('npx astro build', { stdio: 'inherit', env: { ...process.env, PAGES: 'true', PUBLIC_STATIC_PREVIEW: 'true' } });
  execSync('node scripts/pages-base.mjs', { stdio: 'inherit' });
} finally {
  if (await exists(parked)) await rename(parked, api);
}
