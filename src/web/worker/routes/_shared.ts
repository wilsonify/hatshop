/** Shared route plumbing. */
import type { Db } from '../../db/types.ts';
import type { Env } from '../env.ts';
import type { RequestContext } from '../context.ts';

export interface RouteArgs {
  name: string;
  params: Record<string, string>;
  req: Request;
  env: Env;
  url: URL;
  ctx: RequestContext;
  db: Db;
  form?: FormData;
}

export type Handler = (args: RouteArgs) => Promise<Response>;

/** Only same-site paths are allowed as post-action redirects (no open redirect). */
export function safeReturn(value: unknown, fallback = '/cart'): string {
  const path = typeof value === 'string' ? value : '';
  if (!path.startsWith('/') || path.startsWith('//')) return fallback;
  return path.slice(0, 200);
}

export function noticeUrl(path: string, notice: string): string {
  const separator = path.includes('?') ? '&' : '?';
  return `${path}${separator}notice=${encodeURIComponent(notice)}`;
}
