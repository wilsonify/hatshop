/**
 * Password hashing cost measurement.
 *
 * This runs in Node on the developer machine, so the numbers are an upper bound
 * on what a Worker will spend (V8 in the isolate is comparable, but Cloudflare's
 * authoritative CPU accounting only exists after deployment — see the final
 * report). The point is to pick an iteration count from evidence: the Free plan
 * allows 10 ms of CPU per request and login is the heaviest route.
 *
 * Run: npm run bench
 */
import assert from 'node:assert/strict';
import { test } from 'node:test';
import { hashPassword } from '../auth/password.ts';

const CANDIDATES = [100_000, 210_000, 600_000];
const SAMPLES = 5;

function median(values: number[]): number {
  const sorted = [...values].sort((a, b) => a - b);
  return sorted[Math.floor(sorted.length / 2)] ?? 0;
}

test('PBKDF2 cost by iteration count', async () => {
  await hashPassword('warmup', 10_000);

  const rows: Array<{ iterations: number; medianMs: number; maxMs: number }> = [];
  for (const iterations of CANDIDATES) {
    const samples: number[] = [];
    for (let i = 0; i < SAMPLES; i += 1) {
      const start = performance.now();
      await hashPassword('benchmark-password-value', iterations);
      samples.push(performance.now() - start);
    }
    rows.push({
      iterations,
      medianMs: Number(median(samples).toFixed(3)),
      maxMs: Number(Math.max(...samples).toFixed(3)),
    });
  }

  for (const row of rows) {
    console.log(
      `PBKDF2-SHA256 ${String(row.iterations).padStart(7)} iterations → median ${row.medianMs.toFixed(3)} ms (max ${row.maxMs.toFixed(3)} ms)`,
    );
  }

  // Sanity: every configuration must actually do work.
  for (const row of rows) assert.ok(row.medianMs > 0, `iterations ${row.iterations} produced no measurable cost`);
});
