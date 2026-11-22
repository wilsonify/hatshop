/**
 * Authorize.Net AIM (direct post) client.
 *
 * Payment authority is the provider's response, never the HTTP status: a request
 * is only "approved" when the response code is 1, the reason code is acceptable
 * and the echoed amount equals the amount the database computed.
 *
 * Card data is never logged and never stored by this application.
 */

const ENDPOINT_TEST = 'https://test.authorize.net/gateway/transact.dll';
const ENDPOINT_LIVE = 'https://api.authorize.net/gateway/transact.dll';

export interface PaymentConfig {
  configured: boolean;
  gatewayUrl: string;
  loginId: string;
  transactionKey: string;
}

export interface PaymentOutcome {
  approved: boolean;
  responseCode: string;
  reasonCode: string;
  reason: string;
  authCode: string;
  transactionId: string;
  /** Amount echoed by the gateway (field 9), used for reconciliation. */
  amount: string;
}

/** AIM response field positions (0-based) for x_delim_data=TRUE. */
const F = {
  responseCode: 0,
  responseSubcode: 1,
  reasonCode: 2,
  reasonText: 3,
  authCode: 4,
  avs: 5,
  transactionId: 6,
  invoiceNumber: 7,
  description: 8,
  amount: 9,
} as const;

export function paymentConfig(env: {
  PAYMENT_MODE?: string;
  AUTHORIZE_NET_URL?: string;
  AUTHORIZE_NET_LOGIN_ID?: string;
  AUTHORIZE_NET_TRANSACTION_KEY?: string;
}): PaymentConfig {
  const loginId = env.AUTHORIZE_NET_LOGIN_ID ?? '';
  const transactionKey = env.AUTHORIZE_NET_TRANSACTION_KEY ?? '';
  const live = (env.PAYMENT_MODE ?? 'sandbox') === 'production';
  return {
    configured: loginId.length > 0 && transactionKey.length > 0,
    gatewayUrl: env.AUTHORIZE_NET_URL || (live ? ENDPOINT_LIVE : ENDPOINT_TEST),
    loginId,
    transactionKey,
  };
}

function parseResponse(body: string, delim = '|'): PaymentOutcome {
  const f = body.split(delim).map((v) => v.trim());
  return {
    approved: f[F.responseCode] === '1',
    responseCode: f[F.responseCode] ?? '',
    reasonCode: f[F.reasonCode] ?? '',
    reason: f[F.reasonText] ?? '',
    authCode: f[F.authCode] ?? '',
    transactionId: f[F.transactionId] ?? '',
    amount: f[F.amount] ?? '',
  };
}

async function post(cfg: PaymentConfig, fields: Record<string, string>): Promise<PaymentOutcome> {
  const body = new URLSearchParams({
    x_login: cfg.loginId,
    x_tran_key: cfg.transactionKey,
    x_version: '3.1',
    x_delim_data: 'TRUE',
    x_delim_char: '|',
    x_relay_response: 'FALSE',
    ...fields,
  });

  const response = await fetch(cfg.gatewayUrl, {
    method: 'POST',
    headers: { 'content-type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  });

  if (!response.ok) {
    // Transport failure is not a decline and must never be treated as approval.
    return {
      approved: false,
      responseCode: '',
      reasonCode: '',
      reason: `gateway unavailable (${response.status})`,
      authCode: '',
      transactionId: '',
      amount: '',
    };
  }
  return parseResponse(await response.text());
}

/** Compares the gateway's echoed amount with the database-computed total. */
export function amountMatches(echoed: string, expected: string): boolean {
  const a = Number.parseFloat(echoed);
  const b = Number.parseFloat(expected);
  if (!Number.isFinite(a) || !Number.isFinite(b)) return false;
  return Math.abs(a - b) < 0.005;
}

export async function authorize(
  cfg: PaymentConfig,
  input: { cardNumber: string; expiry: string; amount: string; description: string; invoice?: string },
): Promise<PaymentOutcome> {
  return post(cfg, {
    x_type: 'AUTH_ONLY',
    x_card_num: input.cardNumber,
    x_exp_date: input.expiry,
    x_amount: input.amount,
    x_description: input.description.slice(0, 255),
    ...(input.invoice ? { x_invoice_num: input.invoice } : {}),
  });
}

export async function capture(
  cfg: PaymentConfig,
  input: { transactionId: string; amount: string },
): Promise<PaymentOutcome> {
  return post(cfg, {
    x_type: 'PRIOR_AUTH_CAPTURE',
    x_trans_id: input.transactionId,
    x_amount: input.amount,
  });
}

export function luhnValid(cardNumber: string): boolean {
  const digits = cardNumber.replace(/\D/g, '');
  if (digits.length < 12 || digits.length > 19) return false;
  let sum = 0;
  let double = false;
  for (let i = digits.length - 1; i >= 0; i -= 1) {
    let d = Number(digits[i]);
    if (double) {
      d *= 2;
      if (d > 9) d -= 9;
    }
    sum += d;
    double = !double;
  }
  return sum % 10 === 0;
}

/** MMYY, future-dated. */
export function expiryValid(expiry: string, now = new Date()): boolean {
  const match = /^(0[1-9]|1[0-2])(\d{2})$/.exec(expiry.replace(/\s/g, ''));
  if (!match) return false;
  const month = Number(match[1]);
  const year = 2000 + Number(match[2]);
  const end = new Date(Date.UTC(year, month, 1));
  return end > now;
}

export function cardFingerprint(cardNumber: string): string {
  const digits = cardNumber.replace(/\D/g, '');
  return `****${digits.slice(-4)}`;
}
