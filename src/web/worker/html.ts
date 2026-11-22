/** HTML helpers. Every value interpolated into markup goes through `e()`. */

const ENTITIES: Record<string, string> = {
  '&': '&amp;',
  '<': '&lt;',
  '>': '&gt;',
  '"': '&quot;',
  "'": '&#39;',
};

export function e(value: unknown): string {
  return String(value ?? '').replace(/[&<>"']/g, (ch) => ENTITIES[ch] ?? ch);
}

export function money(value: string | number | null | undefined): string {
  const n = Number(value ?? 0);
  return `$${(Number.isFinite(n) ? n : 0).toFixed(2)}`;
}

const DEFAULT_IMAGE = '/images/not_available.jpg';

/**
 * Only a plain file name inside the product image directory may be referenced.
 * Anything containing a path separator is rejected outright rather than reduced to
 * its basename, so a bad database value can never point at an unexpected asset.
 */
export function productImage(name: string | null | undefined, thumb = false): string {
  if (!name) return DEFAULT_IMAGE;
  const raw = String(name);
  if (/[\\/]/.test(raw)) return DEFAULT_IMAGE;
  const base = raw;
  if (!/^[A-Za-z0-9._-]+$/.test(base)) return DEFAULT_IMAGE;
  if (thumb && !base.includes('.thumb.')) {
    const candidate = base.replace(/(\.[A-Za-z0-9]+)$/, '.thumb$1');
    return `/product_images/${candidate}`;
  }
  return `/product_images/${base}`;
}

export function truncate(value: string | null | undefined, length: number): string {
  const text = String(value ?? '');
  if (text.length <= length) return text;
  return `${text.slice(0, Math.max(0, length - 1)).trimEnd()}…`;
}

export interface PageLink {
  page: number;
  href: string;
  current: boolean;
}

/** Builds a compact pagination strip (first, previous, window, next, last). */
export function paginationLinks(
  page: number,
  pages: number,
  href: (page: number) => string,
  maxButtons = 9,
): PageLink[] {
  if (pages <= 1) return [];
  const half = Math.floor(maxButtons / 2);
  let start = Math.max(1, page - half);
  const end = Math.min(pages, start + maxButtons - 1);
  start = Math.max(1, end - maxButtons + 1);
  const links: PageLink[] = [];
  for (let p = start; p <= end; p += 1) links.push({ page: p, href: href(p), current: p === page });
  return links;
}

/** Formats a timestamp from PostgreSQL without pulling in a date library. */
export function dateLabel(value: string | null | undefined): string {
  if (!value) return '—';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return String(value);
  return date.toISOString().slice(0, 10);
}
