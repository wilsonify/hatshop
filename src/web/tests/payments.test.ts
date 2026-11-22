import assert from 'node:assert/strict';
import { test, beforeEach, afterEach } from 'node:test';
import {
  amountMatches,
  authorize,
  capture,
  cardFingerprint,
  expiryValid,
  luhnValid,
  paymentConfig,
  type PaymentConfig,
} from '../services/payments.ts';

const cfg: PaymentConfig = {
  configured: true,
  gatewayUrl: 'https://test.authorize.net/gateway/transact.dll',
  loginId: 'login',
  transactionKey: 'key',
};

const originalFetch = globalThis.fetch;
let lastBody = '';

function stubGateway(body: string, status = 200) {
  globalThis.fetch = (async (_input: RequestInfo | URL, init?: RequestInit) => {
    lastBody = String(init?.body ?? '');
    return new Response(body, { status });
  }) as typeof fetch;
}

beforeEach(() => {
  lastBody = '';
});

afterEach(() => {
  globalThis.fetch = originalFetch;
});

// AIM field order: responseCode|subcode|reasonCode|reasonText|authCode|avs|transId|invoice|desc|amount
const APPROVED = '1|1|1|Approved|AUTH01|Y|TXN123|7|HatShop order 7|25.00';
const DECLINED = '2|1|2|Declined|000000||TXN999|7|HatShop order 7|25.00';

test('approval requires response code 1 and a matching amount', async () => {
  stubGateway(APPROVED);
  const result = await authorize(cfg, { cardNumber: '4111111111111111', expiry: '1230', amount: '25.00', description: 'order 7' });

  assert.equal(result.approved, true);
  assert.equal(result.authCode, 'AUTH01');
  assert.equal(result.transactionId, 'TXN123');
  assert.equal(amountMatches(result.amount, '25.00'), true);
  assert.match(lastBody, /x_type=AUTH_ONLY/);
});

test('a decline is never treated as approved', async () => {
  stubGateway(DECLINED);
  const result = await authorize(cfg, { cardNumber: '4111111111111111', expiry: '1230', amount: '25.00', description: 'x' });
  assert.equal(result.approved, false);
  assert.equal(result.reasonCode, '2');
});

test('a transport failure is not an approval', async () => {
  stubGateway('gateway exploded', 500);
  const result = await authorize(cfg, { cardNumber: '4111111111111111', expiry: '1230', amount: '25.00', description: 'x' });
  assert.equal(result.approved, false);
  assert.match(result.reason, /gateway unavailable/);
});

test('an approved response with the wrong amount must not settle', async () => {
  stubGateway('1|1|1|Approved|AUTH01|Y|TXN123|7|desc|99.00');
  const result = await authorize(cfg, { cardNumber: '4111111111111111', expiry: '1230', amount: '25.00', description: 'x' });
  assert.equal(result.approved, true);
  assert.equal(amountMatches(result.amount, '25.00'), false, 'amount mismatch blocks capture');
});

test('amount comparison tolerates representation but not real differences', () => {
  assert.equal(amountMatches('25', '25.00'), true);
  assert.equal(amountMatches('25.001', '25.00'), true);
  assert.equal(amountMatches('25.02', '25.00'), false);
  assert.equal(amountMatches('', '25.00'), false);
});

test('capture sends the prior transaction id', async () => {
  stubGateway(APPROVED);
  await capture(cfg, { transactionId: 'TXN123', amount: '25.00' });
  assert.match(lastBody, /x_type=PRIOR_AUTH_CAPTURE/);
  assert.match(lastBody, /x_trans_id=TXN123/);
});

test('config is unconfigured without credentials and defaults to sandbox', () => {
  const empty = paymentConfig({});
  assert.equal(empty.configured, false);
  assert.match(empty.gatewayUrl, /test\.authorize\.net/);

  const prod = paymentConfig({ PAYMENT_MODE: 'production', AUTHORIZE_NET_LOGIN_ID: 'a', AUTHORIZE_NET_TRANSACTION_KEY: 'b' });
  assert.equal(prod.configured, true);
  assert.match(prod.gatewayUrl, /api\.authorize\.net/);
});

test('card validation helpers', () => {
  assert.equal(luhnValid('4111111111111111'), true);
  assert.equal(luhnValid('4111111111111112'), false);
  assert.equal(luhnValid('123'), false);

  const now = new Date('2026-11-22T00:00:00Z');
  assert.equal(expiryValid('1227', now), true);
  assert.equal(expiryValid('1126', now), true);
  assert.equal(expiryValid('1026', now), false);
  assert.equal(expiryValid('1327', now), false);
});

test('card fingerprints keep only the last four digits', () => {
  assert.equal(cardFingerprint('4111 1111 1111 1234'), '****1234');
  assert.ok(!cardFingerprint('4111111111111111').includes('411111111111'));
});
