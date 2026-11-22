/** Shared fixtures for the service tests. */
import type { PaymentConfig } from '../services/payments.ts';

const originalFetch = globalThis.fetch;

/** Authorize.Net sandbox configuration the payment tests start from. */
export function sandboxConfig(): PaymentConfig {
  return {
    configured: true,
    gatewayUrl: 'https://test.authorize.net/gateway/transact.dll',
    loginId: 'login',
    transactionKey: 'key',
  };
}

/**
 * Replaces `fetch` with a gateway stub.
 *
 * A single body is served for every call; an array is served in order and its last
 * entry repeats, which exercises authorise-then-failed-capture. `onRequest` sees
 * the posted form so a test can assert the amount that was sent.
 */
export function installGateway(
  body: string | string[],
  { status = 200, onRequest }: { status?: number; onRequest?: (sent: string) => void } = {},
): void {
  const bodies = Array.isArray(body) ? body : [body];
  let call = 0;
  globalThis.fetch = (async (_input: RequestInfo | URL, init?: RequestInit) => {
    onRequest?.(String(init?.body ?? ''));
    const chosen = bodies[Math.min(call, bodies.length - 1)] ?? '';
    call += 1;
    return new Response(chosen, { status });
  }) as typeof fetch;
}

/** Restores the real `fetch`; pass to `afterEach`. */
export function restoreFetch(): void {
  globalThis.fetch = originalFetch;
}
