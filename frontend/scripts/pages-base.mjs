// Post-build step for the GitHub Pages preview only.
// The site is served from /desertvoyagerstourism/, so absolute links like "/tours" or
// "/images/x.png" in the built HTML/JS need that prefix. (Astro already prefixes its own /_astro/ assets.)
import { readdir, readFile, writeFile } from 'node:fs/promises';
import { join } from 'node:path';

const BASE = '/desertvoyagerstourism';
const DIST = new URL('../dist/', import.meta.url).pathname.replace(/^\/([A-Za-z]:)/, '$1');
const SECTIONS = 'images|tours|tickets|blog|cart|checkout|about|contact|terms|privacy|cancellation-policy|booking|favicon';
// "/images…", '/tours…', (/blog…), =/cart…, &quot;/tickets…  — but not //cdn… or already-prefixed paths
const pathRe = new RegExp(`(["'\`(=]|&quot;)/(?!/|${BASE.slice(1)}/)(?=(?:${SECTIONS})\\b)`, 'g');
const homeRe = /href=(["'])\/\1/g;

async function* walk(dir) {
  for (const e of await readdir(dir, { withFileTypes: true })) {
    const p = join(dir, e.name);
    if (e.isDirectory()) yield* walk(p); else yield p;
  }
}

let files = 0;
for await (const file of walk(DIST)) {
  if (!/\.(html|js)$/.test(file)) continue;
  const src = await readFile(file, 'utf8');
  const out = src.replace(pathRe, `$1${BASE}/`).replace(homeRe, `href=$1${BASE}/$1`);
  if (out !== src) { await writeFile(file, out); files++; }
}
console.log(`pages-base: prefixed links in ${files} files with ${BASE}`);
