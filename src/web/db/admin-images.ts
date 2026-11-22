import type { Db } from './types.ts';

/**
 * Points a product at an image that already exists in the static asset bundle.
 *
 * File *uploads* are intentionally not implemented: Workers have no writable
 * filesystem and R2 provisioning is an approval gate. Until then, administrators
 * reference existing files in `public/product_images/`.
 */
export async function catalogSetImage(db: Db, productId: number, image: string): Promise<void> {
  await db.query(`SELECT catalog_set_image($1::integer, $2::varchar)`, [productId, image]);
}

export async function catalogSetThumbnail(db: Db, productId: number, thumbnail: string): Promise<void> {
  await db.query(`SELECT catalog_set_thumbnail($1::integer, $2::varchar)`, [productId, thumbnail]);
}
