// Client-side cart (tickets only). Stored in localStorage until checkout.
// Prices are NOT stored here: they are looked up from the catalog and re-checked on the server.
export interface CartItem {
  id: string;
  slug: string;
  date: string;
  time?: string;
  qty: { adult: number; child: number; infant: number };
}
const KEY = 'dv_cart';

export function getCart(): CartItem[] {
  try { return JSON.parse(localStorage.getItem(KEY) || '[]'); } catch { return []; }
}
export function setCart(items: CartItem[]) {
  try { localStorage.setItem(KEY, JSON.stringify(items)); } catch {}
  window.dispatchEvent(new CustomEvent('cart:change'));
}
export function addToCart(item: Omit<CartItem, 'id'>) {
  const cart = getCart();
  const same = cart.find(c => c.slug === item.slug && c.date === item.date && (c.time || '') === (item.time || ''));
  if (same) {
    same.qty.adult += item.qty.adult; same.qty.child += item.qty.child; same.qty.infant += item.qty.infant;
  } else {
    cart.push({ ...item, id: Math.random().toString(36).slice(2, 10) });
  }
  setCart(cart);
}
export const cartCount = () => getCart().reduce((n, i) => n + i.qty.adult + i.qty.child + i.qty.infant, 0);
