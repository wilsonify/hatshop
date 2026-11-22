import { Client } from 'pg';
import type { Db } from './types.ts';

export interface DbEnv {
  HYPERDRIVE: { connectionString: string };
}

/**
 * Runs `fn` with a single PostgreSQL connection, always closing it.
 *
 * One `Client` per request: Hyperdrive owns pooling, so opening a `Pool` inside
 * a Worker would double-pool and leak sockets. See docs/hyperdrive-verification.md.
 */
export async function withDb<T>(env: DbEnv, fn: (db: Db) => Promise<T>): Promise<T> {
  const client = new Client({
    connectionString: env.HYPERDRIVE.connectionString,
    connectionTimeoutMillis: 8000,
    query_timeout: 8000,
    statement_timeout: 8000,
  });

  await client.connect();
  try {
    return await fn({
      query: async (sql, params) => client.query(sql, params ? [...params] : undefined),
    });
  } finally {
    await client.end().catch(() => {});
  }
}
