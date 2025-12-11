<?php

namespace Hatshop\Core;

/**
 * Business tier class for product administration operations.
 *
 * This class handles CRUD operations for products used by the admin interface.
 * For department operations, use DepartmentAdmin.
 * For category operations, use CategoryAdmin.
 */
class CatalogAdmin
{
    /** @var array Product display options */
    public static array $mProductDisplayOptions = [
        'Default',       // 0
        'On Catalog',    // 1
        'On Department', // 2
        'On Both'        // 3
    ];

    /**
     * Gets all products in a category (for admin purposes).
     *
     * @param int $categoryId Category ID
     * @return array List of products
     */
    public static function getCategoryProducts(int $categoryId): array
    {
        $sql = 'SELECT * FROM catalog_get_category_products(:category_id);';
        $params = [':category_id' => $categoryId];
        $result = DatabaseHandler::prepare($sql);
        return DatabaseHandler::getAll($result, $params) ?? [];
    }

    /**
     * Creates a product and assigns it to a category.
     *
     * @param int $categoryId Category ID
     * @param string $productName Product name
     * @param string $productDescription Product description
     * @param float $productPrice Product price
     */
    public static function addProductToCategory(
        int $categoryId,
        string $productName,
        string $productDescription,
        float $productPrice
    ): void {
        $sql = 'SELECT catalog_add_product_to_category(:category_id, :product_name,
                         :product_description, :product_price);';
        $params = [
            ':category_id' => $categoryId,
            ':product_name' => $productName,
            ':product_description' => $productDescription,
            ':product_price' => $productPrice,
        ];
        $result = DatabaseHandler::prepare($sql);
        DatabaseHandler::execute($result, $params);
    }

    /**
     * Updates a product.
     *
     * @param int $productId Product ID
     * @param string $productName New product name
     * @param string $productDescription New product description
     * @param float $productPrice New product price
     * @param float $productDiscountedPrice New discounted price
     */
    public static function updateProduct(
        int $productId,
        string $productName,
        string $productDescription,
        float $productPrice,
        float $productDiscountedPrice
    ): void {
        $sql = 'SELECT catalog_update_product(:product_id, :product_name,
                         :product_description, :product_price, :product_discounted_price);';
        $params = [
            ':product_id' => $productId,
            ':product_name' => $productName,
            ':product_description' => $productDescription,
            ':product_price' => $productPrice,
            ':product_discounted_price' => $productDiscountedPrice,
        ];
        $result = DatabaseHandler::prepare($sql);
        DatabaseHandler::execute($result, $params);
    }

    /**
     * Removes a product from the catalog.
     *
     * @param int $productId Product ID to delete
     */
    public static function deleteProduct(int $productId): void
    {
        $sql = 'SELECT catalog_delete_product(:product_id);';
        $params = [':product_id' => $productId];
        $result = DatabaseHandler::prepare($sql);
        DatabaseHandler::execute($result, $params);
    }

    /**
     * Removes a product from a category.
     *
     * @param int $productId Product ID
     * @param int $categoryId Category ID
     * @return int 1 if product still exists, 0 if deleted
     */
    public static function removeProductFromCategory(int $productId, int $categoryId): int
    {
        $sql = 'SELECT catalog_remove_product_from_category(:product_id, :category_id);';
        $params = [
            ':product_id' => $productId,
            ':category_id' => $categoryId,
        ];
        $result = DatabaseHandler::prepare($sql);
        return (int) DatabaseHandler::getOne($result, $params);
    }

    /**
     * Gets product info for admin editing.
     *
     * @param int $productId Product ID
     * @return array|null Product information
     */
    public static function getProductInfo(int $productId): ?array
    {
        $sql = 'SELECT * FROM catalog_get_product_info(:product_id);';
        $params = [':product_id' => $productId];
        $result = DatabaseHandler::prepare($sql);
        return DatabaseHandler::getRow($result, $params);
    }

    /**
     * Gets all categories a product belongs to.
     *
     * @param int $productId Product ID
     * @return array List of categories
     */
    public static function getCategoriesForProduct(int $productId): array
    {
        $sql = 'SELECT * FROM catalog_get_categories_for_product(:product_id);';
        $params = [':product_id' => $productId];
        $result = DatabaseHandler::prepare($sql);
        return DatabaseHandler::getAll($result, $params) ?? [];
    }

    /**
     * Sets product display option.
     *
     * @param int $productId Product ID
     * @param int $display Display option (0-3)
     */
    public static function setProductDisplayOption(int $productId, int $display): void
    {
        $sql = 'SELECT catalog_set_product_display_option(:product_id, :display);';
        $params = [
            ':product_id' => $productId,
            ':display' => $display,
        ];
        $result = DatabaseHandler::prepare($sql);
        DatabaseHandler::execute($result, $params);
    }

    /**
     * Assigns a product to a category.
     *
     * @param int $productId Product ID
     * @param int $categoryId Category ID
     */
    public static function assignProductToCategory(int $productId, int $categoryId): void
    {
        $sql = 'SELECT catalog_assign_product_to_category(:product_id, :category_id);';
        $params = [
            ':product_id' => $productId,
            ':category_id' => $categoryId,
        ];
        $result = DatabaseHandler::prepare($sql);
        DatabaseHandler::execute($result, $params);
    }

    /**
     * Moves a product from one category to another.
     *
     * @param int $productId Product ID
     * @param int $sourceCategoryId Source category ID
     * @param int $targetCategoryId Target category ID
     */
    public static function moveProductToCategory(
        int $productId,
        int $sourceCategoryId,
        int $targetCategoryId
    ): void {
        $sql = 'SELECT catalog_move_product_to_category(:product_id,
                         :source_category_id, :target_category_id);';
        $params = [
            ':product_id' => $productId,
            ':source_category_id' => $sourceCategoryId,
            ':target_category_id' => $targetCategoryId,
        ];
        $result = DatabaseHandler::prepare($sql);
        DatabaseHandler::execute($result, $params);
    }

    /**
     * Sets product image filename.
     *
     * @param int $productId Product ID
     * @param string $imageName Image filename
     */
    public static function setImage(int $productId, string $imageName): void
    {
        $sql = 'SELECT catalog_set_image(:product_id, :image_name);';
        $params = [
            ':product_id' => $productId,
            ':image_name' => $imageName,
        ];
        $result = DatabaseHandler::prepare($sql);
        DatabaseHandler::execute($result, $params);
    }

    /**
     * Sets product thumbnail filename.
     *
     * @param int $productId Product ID
     * @param string $thumbnailName Thumbnail filename
     */
    public static function setThumbnail(int $productId, string $thumbnailName): void
    {
        $sql = 'SELECT catalog_set_thumbnail(:product_id, :thumbnail_name);';
        $params = [
            ':product_id' => $productId,
            ':thumbnail_name' => $thumbnailName,
        ];
        $result = DatabaseHandler::prepare($sql);
        DatabaseHandler::execute($result, $params);
    }
}

