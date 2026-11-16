// GitHub Pages preview build has no server: forms/checkout simulate success in the browser.
export const STATIC_PREVIEW = import.meta.env.PUBLIC_STATIC_PREVIEW === 'true';

/** Prefix an absolute site path with the deploy base ("/" locally, "/desertvoyagerstourism/" on Pages). */
export const url = (path: string) => {
  const base = import.meta.env.BASE_URL.replace(/\/$/, '');
  // the Pages post-build step may already have prefixed literal paths
  return base && path.startsWith(base + '/') ? path : base + path;
};

export function demoRef(prefix: 'DV' | 'DT') {
  const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
  return `${prefix}-${Array.from({ length: 6 }, () => chars[Math.floor(Math.random() * chars.length)]).join('')}`;
}
