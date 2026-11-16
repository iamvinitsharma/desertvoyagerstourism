// Small helpers to (re)play entrance animations defined in global.css.

/** Replay the fade-up on an element (or stagger its children). */
export function replay(el: Element | null | undefined, stagger = false) {
  if (!el) return;
  const node = el as HTMLElement;
  node.classList.remove('dv-enter', 'dv-stagger');
  void node.offsetWidth; // restart CSS animation
  if (stagger) {
    [...node.children].forEach((c, i) => (c as HTMLElement).style.setProperty('--i', String(Math.min(i, 8))));
    node.classList.add('dv-stagger');
  } else {
    node.classList.add('dv-enter');
  }
}

/** Reveal [data-reveal] elements once as they scroll into view. */
export function initReveal(root: ParentNode = document) {
  const els = root.querySelectorAll<HTMLElement>('[data-reveal]:not(.is-in)');
  if (!('IntersectionObserver' in window)) { els.forEach(e => e.classList.add('is-in')); return; }
  const io = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); } });
  }, { rootMargin: '0px 0px -10% 0px', threshold: 0.08 });
  els.forEach(e => io.observe(e));
}
