-- Migration 002: widen customer.password so a modern hash fits
-- REVIEWED, NOT APPLIED. Requires explicit operator approval.
--
-- Why this is required:
--   * customer.password is VARCHAR(50) on the live database.
--   * The one existing row stores 40 hex characters (legacy SHA-1).
--   * A PBKDF2 string (pbkdf2$210000$<salt>$<hash>) is ~80 characters and an
--     industry-standard bcrypt digest is 60 characters. Neither fits, so without
--     this migration the application can only store unsalted SHA-1 digests.
--
-- Widening a varchar limit is metadata-only in PostgreSQL: no rewrite, no data
-- change, existing values are preserved byte for byte.
--
-- Safety notes:
--   * Additive/compatible: the PHP application keeps working; it reads and writes
--     the same column.
--   * Rollback is possible while every stored value is <= 50 characters:
--       ALTER TABLE customer ALTER COLUMN password TYPE varchar(50);
--     Do not roll back after the first PBKDF2 hash is stored, or those rows break.
--
-- After applying:
--   1. Set PASSWORD_STORAGE_POLICY=pbkdf2 on the Worker.
--   2. Existing SHA-1/SHA-256 logins keep working and are transparently
--      re-hashed on the next successful sign-in.

BEGIN;

ALTER TABLE customer
  ALTER COLUMN password TYPE varchar(255);

COMMENT ON COLUMN customer.password IS
  'Password digest. pbkdf2$<iterations>$<salt>$<hash>, or a legacy unsalted sha1/sha256 hex digest pending upgrade.';

COMMIT;
