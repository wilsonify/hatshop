/**
 * Fake `Db` for unit tests.
 *
 * Handlers match on the SQL text (which always names the database function being
 * called), so tests can assert *which* function ran, with *which* parameters,
 * without a PostgreSQL server. Nothing here touches the network.
 */
import type { Db } from '../db/types.ts';

export interface RecordedCall {
  sql: string;
  params: readonly unknown[];
}

type Responder = (params: readonly unknown[], sql: string) => Array<Record<string, unknown>>;

export interface FakeDb extends Db {
  calls: RecordedCall[];
  countOf(pattern: RegExp): number;
  callParams(pattern: RegExp): Array<readonly unknown[]>;
}

export function fakeDb(routes: Array<[RegExp, Responder]> = []): FakeDb {
  const calls: RecordedCall[] = [];

  const db: FakeDb = {
    calls,
    countOf(pattern) {
      return calls.filter((call) => pattern.test(call.sql)).length;
    },
    callParams(pattern) {
      return calls.filter((call) => pattern.test(call.sql)).map((call) => call.params);
    },
    async query<T extends Record<string, unknown>>(sql: string, params?: readonly unknown[]) {
      const bound = params ?? [];
      calls.push({ sql, params: bound });
      for (const [pattern, respond] of routes) {
        if (pattern.test(sql)) return { rows: respond(bound, sql) as T[] };
      }
      return { rows: [] as T[] };
    },
  };

  return db;
}

/** Convenience: a single-row response. */
export const one =
  (row: Record<string, unknown>): Responder =>
  () =>
    [row];
