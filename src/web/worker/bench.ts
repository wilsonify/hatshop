/**
 * Dev-only password hashing benchmark (`/__bench`).
 *
 * The Free plan allows 10 ms of CPU per HTTP request, and login is the heaviest
 * route. This measures PBKDF2-SHA256 at candidate iteration counts so the
 * iteration setting is chosen from evidence rather than assumption.
 *
 * Enable with ENABLE_BENCH=1. It refuses to run when ENVIRONMENT=production.
 */
import { hashPassword } from '../auth/password.ts';
import type { Env } from './env.ts';
import { json } from './http.ts';

const CANDIDATES = [100_000, 210_000, 600_000];

export async function runBench(env: Env): Promise<Response> {
  if (env.ENVIRONMENT === 'production') {
    return json({ error: 'bench disabled in production' }, 404);
  }

  const results: Array<{ iterations: number; samples: number[]; medianMs: number }> = [];
  // One warm-up call so module/JIT costs do not land in the first sample.
  await hashPassword('benchmark-warmup-value', 10_000);

  for (const iterations of CANDIDATES) {
    const samples: number[] = [];
    for (let i = 0; i < 5; i += 1) {
      const start = performance.now();
      await hashPassword('benchmark-password-value', iterations);
      samples.push(Number((performance.now() - start).toFixed(3)));
    }
    const sorted = [...samples].sort((a, b) => a - b);
    results.push({ iterations, samples, medianMs: sorted[Math.floor(sorted.length / 2)] ?? 0 });
  }

  return json({
    note: 'Wall-clock per hash inside the Worker isolate. CPU time is measured authoritatively by Cloudflare after deployment.',
    budgetMs: 10,
    targetMs: 5,
    results,
  });
}
