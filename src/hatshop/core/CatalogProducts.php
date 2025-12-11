<?php

namespace Hatshop\Core;

/**
 * Business tier class for product catalog operations.
 *
 * This class handles product retrieval, pagination, recommendations,
 * and reviews functionality.
 */
class CatalogProducts
{
    /**
     * Calculate how many pages of products can be displayed.
     *
     * @param string $countSql SQL query to count products
     * @param array|null $countSqlParams Parameters for the count query
     * @return int Number of pages
     */
    private static function howManyPages(string $countSql, ?array $countSqlParams): int
    {
        if (!FeatureFlags::isEnabled(FeatureFlags::FEATURE_PAGINATION)) {
            return 1;
        }

        $queryHashCode = hash('sha512', $countSql . var_export($countSqlParams, true));

        if (isset($_SESSION['last_count_hash']) &&
            isset($_SESSION['how_many_pages']) &&
            $_SESSION['last_count_hash'] === $queryHashCode) {
            return $_SESSION['how_many_pages'];
        }

        $prepared = DatabaseHandler::prepare($countSql);
        $itemsCount = DatabaseHandler::getOne($prepared, $countSqlParams) ?? 0;

        $productsPerPage = Config::get('products_per_page', 4);
        $howManyPages = (int) ceil($itemsCount / $productsPerPage);

        $_SESSION['last_count_hash'] = $queryHashCode;
        $_SESSION['how_many_pages'] = $howManyPages;

        return $howManyPages;
    }

    /**
     * Retrieve products in a category with pagination.
     *
     * @param int $categoryId The category ID
     * @param int $pageNo Current page number
     * @param int &$rHowManyPages Output parameter for total pages
     * @return array List of products
     */
    public static function getProductsInCategory(int $categoryId, int $pageNo, int &$rHowManyPages): array
    {
        if (!FeatureFlags::isEnabled(FeatureFlags::FEATURE_PRODUCTS)) {
            $rHowManyPages = 0;
            return [];
        }

        $countSql = 'SELECT catalog_count_products_in_category(:category_id);';
        $countParams = [':category_id' => $categoryId];
        $rHowManyPages = self::howManyPages($countSql, $countParams);

        $productsPerPage = Config::get('products_per_page', 4);
        $startItem = ($pageNo - 1) * $productsPerPage;

        $sql = 'SELECT * FROM catalog_get_products_in_category(
                    :category_id, :short_product_description_length,
                    :products_per_page, :start_item);';
        $params = [
            ':category_id' => $categoryId,
            ':short_product_description_length' => Config::get('short_product_description_length', 150),
            ':products_per_page' => $productsPerPage,
            ':start_item' => $startItem,
        ];

        $result = DatabaseHandler::prepare($sql);
        return DatabaseHandler::getAll($result, $params) ?? [];
    }

    /**
     * Retrieve products for department display with pagination.
     *
     * @param int $departmentId The department ID
     * @param int $pageNo Current page number
     * @param int &$rHowManyPages Output parameter for total pages
     * @return array List of products
     */
    public static function getProductsOnDepartmentDisplay(int $departmentId, int $pageNo, int &$rHowManyPages): array
    {
        if (!FeatureFlags::isEnabled(FeatureFlags::FEATURE_PRODUCTS)) {
            $rHowManyPages = 0;
            return [];
        }

        $countSql = 'SELECT catalog_count_products_on_department(:department_id);';
        $countParams = [':department_id' => $departmentId];
        $rHowManyPages = self::howManyPages($countSql, $countParams);

        $productsPerPage = Config::get('products_per_page', 4);
        $startItem = ($pageNo - 1) * $productsPerPage;

        $sql = 'SELECT * FROM catalog_get_products_on_department(
                    :department_id, :short_product_description_length,
                    :products_per_page, :start_item);';
        $params = [
            ':department_id' => $departmentId,
            ':short_product_description_length' => Config::get('short_product_description_length', 150),
            ':products_per_page' => $productsPerPage,
            ':start_item' => $startItem,
        ];

        $result = DatabaseHandler::prepare($sql);
        return DatabaseHandler::getAll($result, $params) ?? [];
    }

    /**
     * Retrieve products for catalog front page display with pagination.
     *
     * @param int $pageNo Current page number
     * @param int &$rHowManyPages Output parameter for total pages
     * @return array List of products
     */
    public static function getProductsOnCatalogDisplay(int $pageNo, int &$rHowManyPages): array
    {
        if (!FeatureFlags::isEnabled(FeatureFlags::FEATURE_PRODUCTS)) {
            $rHowManyPages = 0;
            return [];
        }

        $countSql = 'SELECT catalog_count_products_on_catalog();';
        $rHowManyPages = self::howManyPages($countSql, null);

        $productsPerPage = Config::get('products_per_page', 4);
        $startItem = ($pageNo - 1) * $productsPerPage;

        $sql = 'SELECT * FROM catalog_get_products_on_catalog(
                    :short_product_description_length,
                    :products_per_page, :start_item);';
        $params = [
            ':short_product_description_length' => Config::get('short_product_description_length', 150),
            ':products_per_page' => $productsPerPage,
            ':start_item' => $startItem,
        ];

        $result = DatabaseHandler::prepare($sql);
        return DatabaseHandler::getAll($result, $params) ?? [];
    }

    /**
     * Retrieve complete product details.
     *
     * @param int $productId The product ID
     * @return array|null Product details or null if not found
     */
    public static function getProductDetails(int $productId): ?array
    {
        if (!FeatureFlags::isEnabled(FeatureFlags::FEATURE_PRODUCT_DETAILS)) {
            return null;
        }

        $sql = 'SELECT * FROM catalog_get_product_details(:product_id);';
        $params = [':product_id' => $productId];
        $result = DatabaseHandler::prepare($sql);
        $row = DatabaseHandler::getRow($result, $params);
        return $row !== false ? $row : null;
    }

    /**
     * Get product recommendations (Chapter 10).
     *
     * @param int $productId Product ID to get recommendations for
     * @return array Recommended products
     */
    public static function getRecommendations(int $productId): array
    {
        if (!FeatureFlags::isEnabled(FeatureFlags::FEATURE_PRODUCT_RECOMMENDATIONS)) {
            return [];
        }

        $sql = 'SELECT * FROM catalog_get_recommendations(
                    :product_id, :short_product_description_length);';
        $params = [
            ':product_id' => $productId,
            ':short_product_description_length' => Config::get('short_product_description_length', 150),
        ];

        $result = DatabaseHandler::prepare($sql);
        return DatabaseHandler::getAll($result, $params) ?? [];
    }

    /**
     * Get reviews for a product (Chapter 16).
     *
     * @param int $productId Product ID
     * @return array Product reviews
     */
    public static function getProductReviews(int $productId): array
    {
        if (!FeatureFlags::isEnabled(FeatureFlags::FEATURE_PRODUCT_REVIEWS)) {
            return [];
        }

        $sql = 'SELECT * FROM catalog_get_product_reviews(:product_id);';
        $params = [':product_id' => $productId];
        $result = DatabaseHandler::prepare($sql);

        return DatabaseHandler::getAll($result, $params) ?? [];
    }

    /**
     * Create a product review (Chapter 16).
     *
     * @param int $customerId Customer ID
     * @param int $productId Product ID
     * @param string $review Review text
     * @param int $rating Rating (1-5)
     */
    public static function createProductReview(
        int $customerId,
        int $productId,
        string $review,
        int $rating
    ): void {
        if (!FeatureFlags::isEnabled(FeatureFlags::FEATURE_PRODUCT_REVIEWS)) {
            return;
        }

        // Validate rating
        $rating = max(1, min(5, $rating));

        $sql = 'SELECT catalog_create_product_review(
                    :customer_id, :product_id, :review, :rating);';
        $params = [
            ':customer_id' => $customerId,
            ':product_id' => $productId,
            ':review' => $review,
            ':rating' => $rating,
        ];
        $result = DatabaseHandler::prepare($sql);

        DatabaseHandler::execute($result, $params);
    }
}
