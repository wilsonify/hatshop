# Checkout and payment states

The existing schema already models order progress, so the rewrite reuses it
rather than inventing a second order model.

## Status values (as stored in `orders.status`)

| Status | Meaning | Who sets it |
| --- | --- | --- |
| 0 | Placed, unpaid | `shopping_cart_create_order` |
| 1 | Customer notified | pipeline / admin |
| 2 | Funds checked | pipeline / admin |
| 3 | **Authorized** — money held, awaiting stock | checkout after AUTH_ONLY approval |
| 4 | Stock confirmed | pipeline / admin |
| 5 | **Paid** — captured | checkout after capture approval |
| 6 | Goods shipped | admin (`mark-shipped`) |
| 7 | Shipping confirmed | pipeline / admin |
| 8 | Completed | pipeline / admin |

The PHP `PsTakePayment` currently records a *dummy* payment. The Worker performs
real Authorize.Net sandbox calls and only advances the order when the gateway
approves.

## Transition produced by `POST /checkout`

```
validate cart → compute total in SQL → validate shipping/tax for the customer's region
      │
      ├─ cart empty .......................... 303 → /cart        (no order created)
      ├─ shipping/tax not valid ............. 400                (no order created)
      └─ create order (single plpgsql call, clears the cart)
                 │
                 ├─ AUTH_ONLY declined ...... audit row, status stays 0, 402
                 ├─ amount ≠ database total . audit row, status stays 0, 402
                 ├─ approved ................ orders_set_auth_code(auth, txn), status 3
                 │        └─ capture declined → audit row, stays 3, 502
                 └─ captured ................ status 5, 303 → /orders/:id
```

Rules enforced in `services/checkout.ts`:

1. **The amount is never taken from the request.** It is `shopping_cart_get_total_amount`,
   computed by the database, and the gateway's echoed amount must match it
   (`amountMatches`, ±0.005).
2. **HTTP 200 is not approval.** A response counts only when AIM field 0 is `1`
   *and* the amount matches. Transport failures and 5xx responses are declines,
   never approvals.
3. **Provider identifiers are stored, not invented:** `x_trans_id` → `orders.reference`,
   `x_auth_code` → `orders.auth_code` (both `VARCHAR(50)`).
4. **A decline never marks an order paid.** It writes an audit row
   (`orders_create_audit`, message number 20399) and leaves status 0 so the order
   is visible to administrators as unpaid.
5. **Capture failure leaves the order authorized** (status 3), not paid, with an
   audit row. Re-capturing is a manual, reviewed operation.
6. **Admin guard:** `update-order-status` refuses to set a status ≥ 5 when the
   order has no auth code and status < 3.

## Idempotency and duplicate submission

- `shopping_cart_create_order` writes the order, its lines, and empties the cart in
  one function call. A duplicate POST therefore finds an empty cart and returns
  `empty_cart` — it cannot create a second order.
- The checkout form carries a single-use nonce in a signed, 1-hour cookie that is
  cleared on submission; a stale or replayed form is rejected with 400 before any
  order is created.
- Every POST is also CSRF-checked (token + Origin + `Sec-Fetch-Site`).

## Inventory and stock

`product` has no stock column, and the original application confirms availability
by emailing the supplier (`PsCheckStock`, status 2 → 3). The rewrite preserves
that behaviour: there is no invented inventory model. Introducing real stock
levels would be a schema change and is out of scope.

## Not implemented (documented, not simulated)

- **Email notifications.** Workers have no native sender and no provider is
  configured, so pipeline emails are not sent. Audit rows still record each step.
- **Stored cards.** `customer.credit_card` exists and is deliberately unused by the
  rewrite; card data is never stored.
- **Refunds/voids.** Not part of the original application; would need provider
  support and a reviewed status meaning.

## Verified against the live deployment

With no gateway credentials configured, `POST /checkout` returns **503** and the
row counts of `orders`, `order_detail`, `shopping_cart` and `audit` are identical
before and after — checked by real requests in `scripts/verify-interactive.mjs`,
not by reading the code. `orders` rows with `status >= 5` stay at zero.

## Testing

`tests/payments.test.ts` and `tests/checkout.test.ts` cover approval, decline,
transport failure, amount mismatch, capture failure, and the exact status
sequence written ([3, 5]). They use a stubbed gateway and a fake `Db`; no request
leaves the process and no real card is used.
