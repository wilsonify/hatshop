/** Catalog read models. All rows come from the database's catalog functions. */
import {
  countProductsInCategory,
  countProductsOnCatalog,
  countProductsOnDepartment,
  countSearchResults,
  flagStopWords,
  getCategoriesList,
  getCategoryDetails,
  getDepartmentDetails,
  getDepartments,
  getProductDetails,
  getProductReviews,
  getProductsInCategory,
  getProductsOnCatalog,
  getProductsOnDepartment,
  getRecommendations,
  searchProducts,
} from '../db/queries.ts';
import type { Db, ProductListRow } from '../db/types.ts';

export interface PageMeta {
  /** 1-based page number for display. */
  page: number;
  perPage: number;
  total: number;
  pages: number;
  offset: number;
}

export interface PagedProducts {
  items: ProductListRow[];
  meta: PageMeta;
}

export function pageMeta(page: number, perPage: number, total: number): PageMeta {
  const safePerPage = Math.max(1, perPage);
  const pages = Math.max(1, Math.ceil(total / safePerPage));
  const safePage = Math.min(Math.max(1, page), pages);
  return { page: safePage, perPage: safePerPage, total, pages, offset: (safePage - 1) * safePerPage };
}

const count = (row: { n: number } | null): number => (row ? Number(row.n) : 0);

export async function catalogPage(db: Db, page: number, perPage: number, descriptionLength: number) {
  const total = count(await countProductsOnCatalog(db));
  const meta = pageMeta(page, perPage, total);
  const items = meta.total === 0 ? [] : await getProductsOnCatalog(db, descriptionLength, meta.perPage, meta.offset);
  return { items, meta };
}

export async function departments(db: Db) {
  return getDepartments(db);
}

export async function departmentPage(
  db: Db,
  departmentId: number,
  page: number,
  perPage: number,
  descriptionLength: number,
) {
  const [details, categories, totalRow] = await Promise.all([
    getDepartmentDetails(db, departmentId),
    getCategoriesList(db, departmentId),
    countProductsOnDepartment(db, departmentId),
  ]);
  const meta = pageMeta(page, perPage, count(totalRow));
  const items =
    meta.total === 0
      ? []
      : await getProductsOnDepartment(db, departmentId, descriptionLength, meta.perPage, meta.offset);
  return { details, categories, items, meta };
}

export async function categoryPage(
  db: Db,
  categoryId: number,
  page: number,
  perPage: number,
  descriptionLength: number,
) {
  const [details, totalRow] = await Promise.all([
    getCategoryDetails(db, categoryId),
    countProductsInCategory(db, categoryId),
  ]);
  const meta = pageMeta(page, perPage, count(totalRow));
  const items =
    meta.total === 0 ? [] : await getProductsInCategory(db, categoryId, descriptionLength, meta.perPage, meta.offset);
  return { details, items, meta };
}

export async function productPage(db: Db, productId: number, descriptionLength: number) {
  const [details, reviews, recommendations] = await Promise.all([
    getProductDetails(db, productId),
    getProductReviews(db, productId),
    getRecommendations(db, productId, descriptionLength),
  ]);
  return { details, reviews, recommendations };
}

/**
 * Search. Stop words are flagged by the database first so "hat and cap" behaves
 * like the original application; an all-stop-words query falls back to the catalog.
 */
export async function searchPage(
  db: Db,
  query: string,
  allWords: boolean,
  page: number,
  perPage: number,
  descriptionLength: number,
) {
  const words = query
    .toLowerCase()
    .split(/\s+/)
    .map((w) => w.replace(/[^\p{L}\p{N}]/gu, ''))
    .filter((w) => w.length > 1)
    .slice(0, 8);

  if (words.length === 0) {
    return { items: [], meta: pageMeta(1, perPage, 0), words, allWords, empty: true };
  }

  const allWordsFlag = allWords ? 'on' : 'off';
  const total = count(await countSearchResults(db, words, allWordsFlag));
  const meta = pageMeta(page, perPage, total);
  const items =
    meta.total === 0
      ? []
      : await searchProducts(db, words, allWordsFlag, descriptionLength, meta.perPage, meta.offset);
  return { items, meta, words, allWords, empty: meta.total === 0 };
}

export { flagStopWords };
