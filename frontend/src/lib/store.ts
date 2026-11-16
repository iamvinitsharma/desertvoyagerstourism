/**
 * TEMPORARY dev storage: appends enquiries/orders to JSON-lines files in `frontend/.data/`.
 * When the Laravel backend exists, replace these with POSTs to the API (MySQL).
 */
import { mkdir, appendFile, readFile } from 'node:fs/promises';
import { join } from 'node:path';
import { randomInt } from 'node:crypto';

const DIR = join(process.cwd(), '.data');

export const newRef = (prefix: 'DV' | 'DT') => {
  const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
  return `${prefix}-${Array.from({ length: 6 }, () => chars[randomInt(chars.length)]).join('')}`;
};

export async function save(kind: 'enquiries' | 'orders', record: Record<string, unknown>) {
  await mkdir(DIR, { recursive: true });
  await appendFile(join(DIR, `${kind}.jsonl`), JSON.stringify(record) + '\n', 'utf8');
}

export async function find(kind: 'enquiries' | 'orders', ref: string) {
  try {
    const lines = (await readFile(join(DIR, `${kind}.jsonl`), 'utf8')).trim().split('\n');
    for (let i = lines.length - 1; i >= 0; i--) {
      const r = JSON.parse(lines[i]); if (r.ref === ref) return r;
    }
  } catch {}
  return null;
}
