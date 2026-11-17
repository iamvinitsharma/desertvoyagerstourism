import type { APIRoute } from 'astro';
import { dataSource, postApi } from '../../lib/data';

const json = (body: unknown, status = 200) =>
  new Response(JSON.stringify(body), { status, headers: { 'Content-Type': 'application/json' } });

/** Newsletter sign-up → admin "Newsletter" list. */
export const POST: APIRoute = async ({ request }) => {
  let b: any;
  try { b = await request.json(); } catch { return json({ ok: false, error: 'Invalid request' }, 400); }
  const email = typeof b.email === 'string' ? b.email.trim().slice(0, 150) : '';
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return json({ ok: false, error: 'Please enter a valid email' }, 422);

  if (dataSource() !== 'api') return json({ ok: true }); // no admin connected: accept silently
  try {
    const { status, data } = await postApi('/subscribe', { email, source: 'Website footer' });
    return json(data, status);
  } catch {
    return json({ ok: false, error: 'Please try again in a moment' }, 503);
  }
};
