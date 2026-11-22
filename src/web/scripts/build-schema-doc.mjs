/**
 * Builds docs/schema.generated.md from docs/schema-snapshot.json.
 *
 * Classification is by *effect*, derived from the function name family and
 * confirmed against the function bodies in
 * `src/c00 - PostgreSQL Image/Database Complete/split_sql_files/`.
 *
 *   node scripts/build-schema-doc.mjs
 */
import { readFileSync, writeFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const here = dirname(fileURLToPath(import.meta.url));
const snapshot = JSON.parse(readFileSync(resolve(here, '../docs/schema-snapshot.json'), 'utf8'));

const DESTRUCTIVE = [/^catalog_delete_department$/, /^catalog_delete_category$/, /^catalog_delete_product$/, /^shopping_cart_delete_old_carts$/];
const WRITE = [
  /^catalog_add_/,
  /^catalog_update_/,
  /^catalog_assign_/,
  /^catalog_move_/,
  /^catalog_remove_/,
  /^catalog_set_/,
  /^catalog_create_/,
  /^customer_add$/,
  /^customer_update_/,
  /^orders_create_audit$/,
  /^orders_set_/,
  /^orders_update_/,
  /^shopping_cart_add_/,
  /^shopping_cart_update$/,
  /^shopping_cart_remove_/,
  /^shopping_cart_save_/,
  /^shopping_cart_move_/,
  /^shopping_cart_empty$/,
  /^shopping_cart_create_order$/,
];

function classify(name) {
  if (DESTRUCTIVE.some((re) => re.test(name))) return 'destructive';
  if (WRITE.some((re) => re.test(name))) return 'write';
  return 'read';
}

const functions = [...snapshot.functions]
  .sort((a, b) => a.proname.localeCompare(b.proname))
  .map((f) => ({ ...f, effect: classify(f.proname) }));

const counts = functions.reduce((acc, f) => ({ ...acc, [f.effect]: (acc[f.effect] ?? 0) + 1 }), {});

const rows = (table) => snapshot.row_counts[table] ?? 0;

const tables = [...new Set(snapshot.columns.map((c) => c.table_name))].sort((a, b) => (a < b ? -1 : a > b ? 1 : 0));

const columnTable = tables
  .map((table) => {
    const cols = snapshot.columns
      .filter((c) => c.table_name === table)
      .map((c) => `${c.column_name} ${c.data_type}${c.max_len ? `(${c.max_len})` : ''}`)
      .join(', ');
    return `| \`${table}\` | ${rows(table)} | ${cols} |`;
  })
  .join('\n');

const functionTable = ['read', 'write', 'destructive']
  .flatMap((effect) => functions.filter((f) => f.effect === effect))
  .map((f) => `| \`${f.proname}(${f.args})\` | \`${f.returns}\` | ${f.effect} |`)
  .join('\n');

const passwordShape = snapshot.password_shape[0];

const doc = `# Database inventory (live)

Generated from \`docs/schema-snapshot.json\` by \`scripts/build-schema-doc.mjs\`.
The snapshot was captured read-only with \`scripts/introspect.mjs\` against
\`${snapshot.identity.database}\` on \`${snapshot.identity.server_addr}\`.

| Fact | Value |
| --- | --- |
| Server version | ${snapshot.identity.version} |
| Database | \`${snapshot.identity.database}\` |
| Role | \`${snapshot.identity.role}\` |
| Tables | ${snapshot.tables.length} |
| Public functions | ${snapshot.functions.length} (${counts.read ?? 0} read, ${counts.write ?? 0} write, ${counts.destructive ?? 0} destructive) |
| Composite types | ${snapshot.types.length} |
| Installed extensions | ${snapshot.extensions.map((e) => `${e.extname} ${e.extversion}`).join(', ')} |

## Consequences for the application

1. **The database is the business-logic engine.** ${snapshot.functions.length} plpgsql
   functions implement catalog, cart, order and customer behaviour. The Worker
   calls them; it does not reimplement them.
2. **\`pgcrypto\` is not installed**, so password hashing cannot be delegated to
   PostgreSQL without a new extension (an approval gate, not applied).
3. **\`customer.password\` is \`VARCHAR(50)\`** and the only existing row stores
   **${passwordShape?.len ?? '?'} hex characters**, i.e. a legacy SHA-1 digest
   (${passwordShape?.sha256_hex_like ?? 0} SHA-256-shaped values, ${passwordShape?.has_separator ?? 0}
   modern \`$separated\` digests). See \`openspec/specs/accounts/spec.md\` and
   \`openspec/specs/sessions/spec.md\` for what this means for sign-in, and the
   reviewed migration 002.
4. **\`orders\` has no \`customer_id\`** in its composite type; ownership is proven by
   looking an order up in the customer's own order list (\`services/orders.ts\`).
5. **\`orders\` exposes \`auth_code\` and \`reference\` only as columns**, not through
   \`orders_get_order_info\`; they are read directly when needed.

## Tables

| Table | Rows | Columns |
| --- | --- | --- |
${columnTable}

## Functions by effect

\`read\` = no data changed by this application · \`write\` = inserts/updates ·
\`destructive\` = deletes rows.

| Function | Returns | Effect |
| --- | --- | --- |
${functionTable}

## Read-only test allowlist

Only these functions are exercised by tests that run against the live database:
\`catalog_get_*\`, \`catalog_count_*\`, \`catalog_search\`, \`catalog_flag_stop_words\`,
\`customer_get_login_info\`, \`orders_get_*\`, \`shopping_cart_get_*\`. Anything that
writes (registration, cart mutations, reviews, orders, admin actions) requires an
isolated database branch.

## Known database quirks (preserved, not "fixed")

- \`shopping_cart_count_old_carts(days)\` ignores its argument: the body hard-codes
  \`'1' DAYS\`. The Worker does not use it.
- \`catalog_*_delete_*\` functions do not cascade; they return a smallint signalling
  whether the row could be removed. The admin UI reports the outcome and never
  force-deletes.
- Several "get" functions are \`VOLATILE\` because they are plpgsql; volatility does
  not imply writes for this set.
`;

writeFileSync(resolve(here, '../docs/schema.generated.md'), doc);
console.log(`wrote docs/schema.generated.md (${functions.length} functions, ${tables.length} tables)`);
