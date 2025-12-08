<?php

namespace Hatshop\Core;

/**
 * Business tier class for catalog administration operations.
 *
 * This class handles CRUD operations for departments, categories, and products
 * used by the admin interface (Chapter 7).
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
     * Retrieves all departments with their descriptions.
     *
     * @return array List of departments with descriptions
     */
    public static function getDepartmentsWithDescriptions(): array
    {
        $sql = 'SELECT * FROM catalog_get_departments();';
        $result = DatabaseHandler::prepare($sql);
        return DatabaseHandler::getAll($result) ?? [];
    }

    /**
     * Updates department details.
     *
     * @param int $departmentId Department ID
     * @param string $departmentName New department name
     * @param string $departmentDescription New department description
     */
    public static function updateDepartment(
        int $departmentId,
        string $departmentName,
        string $departmentDescription
    ): void {
        $sql = 'SELECT catalog_update_department(:department_id, :department_name,
                                                 :department_description);';
        $params = [
            ':department_id' => $departmentId,
            ':department_name' => $departmentName,
            ':department_description' => $departmentDescription,
        ];
        $result = DatabaseHandler::prepare($sql);
        DatabaseHandler::execute($result, $params);
    }

    /**
     * Deletes a department.
     *
     * @param int $departmentId Department ID to delete
     * @return int Status code (negative if department not empty)
     */
    public static function deleteDepartment(int $departmentId): int
    {
        $sql = 'SELECT catalog_delete_department(:department_id);';
        $params = [':department_id' => $departmentId];
        $result = DatabaseHandler::prepare($sql);
        return (int) DatabaseHandler::getOne($result, $params);
    }

    /**
     * Adds a new department.
     *
     * @param string $departmentName Department name
     * @param string $departmentDescription Department description
     */
    public static function addDepartment(string $departmentName, string $departmentDescription): void
    {
        $sql = 'SELECT catalog_add_department(:department_name, :department_description);';
        $params = [
            ':department_name' => $departmentName,
            ':department_description' => $departmentDescription,
        ];
        $result = DatabaseHandler::prepare($sql);
        DatabaseHandler::execute($result, $params);
    }

    /**
     * Gets all categories in a department.
     *
     * @param int $departmentId Department ID
     * @return array List of categories
     */
    public static function getDepartmentCategories(int $departmentId): array
    {
        $sql = 'SELECT * FROM catalog_get_department_categories(:department_id);';
        $params = [':department_id' => $departmentId];
        $result = DatabaseHandler::prepare($sql);
        return DatabaseHandler::getAll($result, $params) ?? [];
    }

    /**
     * Adds a new category to a department.
     *
     * @param int $departmentId Department ID
     * @param string $categoryName Category name
     * @param string $categoryDescription Category description
     */
    public static function addCategory(
        int $departmentId,
        string $categoryName,
        string $categoryDescription
    ): void {
        $sql = 'SELECT catalog_add_category(:department_id, :category_name, :category_description);';
        $params = [
            ':department_id' => $departmentId,
            ':category_name' => $categoryName,
            ':category_description' => $categoryDescription,
        ];
        $result = DatabaseHandler::prepare($sql);
        DatabaseHandler::execute($result, $params);
    }

    /**
     * Deletes a category.
     *
     * @param int $categoryId Category ID to delete
     * @return int Status code (negative if category not empty)
     */
    public static function deleteCategory(int $categoryId): int
    {
        $sql = 'SELECT catalog_delete_category(:category_id);';
        $params = [':category_id' => $categoryId];
        $result = DatabaseHandler::prepare($sql);
        return (int) DatabaseHandler::getOne($result, $params);
    }

    /**
     * Updates a category.
     *
     * @param int $categoryId Category ID
     * @param string $categoryName New category name
     * @param string $categoryDescription New category description
     */
    public static function updateCategory(
        int $categoryId,
        string $categoryName,
        string $categoryDescription
    ): void {
        $sql = 'SELECT catalog_update_category(:category_id, :category_name, :category_description);';
        $params = [
            ':category_id' => $categoryId,
            ':category_name' => $categoryName,
            ':category_description' => $categoryDescription,
        ];
        $result = DatabaseHandler::prepare($sql);
        DatabaseHandler::execute($result, $params);
    }

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
     * Gets all categories.
     *
     * @return array List of all categories
     */
    public static function getCategories(): array
    {
        $sql = 'SELECT * FROM catalog_get_categories();';
        $result = DatabaseHandler::prepare($sql);
        return DatabaseHandler::getAll($result) ?? [];
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
