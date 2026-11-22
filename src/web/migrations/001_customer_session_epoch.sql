-- Migration 001: server-side session revocation
-- REVIEWED, NOT APPLIED. Requires explicit operator approval.
--
-- Adds a per-customer epoch. Sessions embed the epoch they were issued with;
-- incrementing it (on logout-everywhere, password change, or account lock)
-- invalidates every cookie already handed out, without introducing a session
-- table or an extra data store.
--
-- Safety notes:
--   * Adding a column with a non-volatile default is a metadata-only operation
--     on PostgreSQL 11+ (the live database is PostgreSQL 17.12): no table rewrite,
--     no data movement, no locking beyond a brief ACCESS EXCLUSIVE lock.
--   * Purely additive: no existing column is altered or dropped, no rows change
--     their meaning, and the PHP application continues to work untouched.
--   * Rollback: ALTER TABLE customer DROP COLUMN session_epoch;
--
-- After applying, set the Worker variable SESSION_EPOCH_ENABLED=1.

BEGIN;

ALTER TABLE customer
  ADD COLUMN IF NOT EXISTS session_epoch integer NOT NULL DEFAULT 0;

COMMENT ON COLUMN customer.session_epoch IS
  'Session generation. Sessions carry the value they were issued with; incrementing it revokes them.';

COMMIT;

-- Example of a revocation (run deliberately, not as part of this migration):
--   UPDATE customer SET session_epoch = session_epoch + 1 WHERE customer_id = $1;
