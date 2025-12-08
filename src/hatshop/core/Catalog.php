<?php

namespace Hatshop\Core;

/**
 * Business tier class for reading product catalog information.
 *
 * This class consolidates catalog functionality from all chapters
 * and uses feature flags to enable/disable specific capabilities.
 */
class Catalog
{
    /**
     * Retrieve all departments.
     *
     * @return array List of departments
     */
    public static function getDepartments(): array
    {
        if (!FeatureFlags::isEnabled(FeatureFlags::FEATURE_DEPARTMENTS)) {
            return [];
        }

        $sql = 'SELECT * FROM catalog_get_departments_list();';
        $result = DatabaseHandler::prepare($sql);
        return DatabaseHandler::getAll($result) ?? [];
    }

    /**
     * Retrieve complete details for a department.
     *
     * @param int $departmentId The department ID
     * @return array|null Department details or null if not found
     */
    public static function getDepartmentDetails(int $departmentId): ?array
    {
        if (!FeatureFlags::isEnabled(FeatureFlags::FEATURE_DEPARTMENTS)) {
            return null;
        }

        $sql = 'SELECT * FROM catalog_get_department_details(:department_id);';
        $params = [':department_id' => $departmentId];
        $result = DatabaseHandler::prepare($sql);
        $row = DatabaseHandler::getRow($result, $params);
        return $row !== false ? $row : null;
    }

    /**
     * Retrieve list of categories in a department.
     *
     * @param int $departmentId The department ID
     * @return array List of categories
     */
    public static function getCategoriesInDepartment(int $departmentId): array
    {
        if (!FeatureFlags::isEnabled(FeatureFlags::FEATURE_CATEGORIES)) {
            return [];
        }

        $sql = 'SELECT * FROM catalog_get_categories_list(:department_id);';
        $params = [':department_id' => $departmentId];
        $result = DatabaseHandler::prepare($sql);
        return DatabaseHandler::getAll($result, $params) ?? [];
    }

    /**
     * Retrieve complete details for a category.
     *
     * @param int $categoryId The category ID
     * @return array|null Category details or null if not found
     */
    public static function getCategoryDetails(int $categoryId): ?array
    {
        if (!FeatureFlags::isEnabled(FeatureFlags::FEATURE_CATEGORIES)) {
            return null;
        }

        $sql = 'SELECT * FROM catalog_get_category_details(:category_id);';
        $params = [':category_id' => $categoryId];
        $result = DatabaseHandler::prepare($sql);
        $row = DatabaseHandler::getRow($result, $params);
        return $row !== false ? $row : null;
    }

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
     * Flag stop words in a search query.
     *
     * @param array $words Array of words to check
     * @return array Array with 'accepted_words' and 'ignored_words'
     */
    public static function flagStopWords(array $words): array
    {
        $sql = 'SELECT * FROM catalog_flag_stop_words(:words);';
        $params = [':words' => '{' . implode(', ', $words) . '}'];
        $result = DatabaseHandler::prepare($sql);
        $flags = DatabaseHandler::getAll($result, $params) ?? [];

        $searchWords = [
            'accepted_words' => [],
            'ignored_words' => [],
        ];

        for ($i = 0; $i < count($flags); $i++) {
            if ($flags[$i]['catalog_flag_stop_words']) {
                $searchWords['ignored_words'][] = $words[$i];
            } else {
                $searchWords['accepted_words'][] = $words[$i];
            }
        }

        return $searchWords;
    }

    /**
     * Search the catalog.
     *
     * @param string $searchString The search string
     * @param bool $allWords Whether to match all words
     * @param int $pageNo Current page number
     * @param int &$rHowManyPages Output parameter for total pages
     * @return array Search results with 'accepted_words', 'ignored_words', and 'products'
     */
    public static function search(string $searchString, bool $allWords, int $pageNo, int &$rHowManyPages): array
    {
        $searchResult = [
            'accepted_words' => [],
            'ignored_words' => [],
            'products' => [],
        ];
        $rHowManyPages = 0;

        // Early exit conditions - feature disabled or empty search
        $canSearch = FeatureFlags::isEnabled(FeatureFlags::FEATURE_SEARCH)
            && !empty($searchString);

        if ($canSearch) {
            $delimiters = ',.; ';
            $word = strtok($searchString, $delimiters);
            $words = [];

            while ($word) {
                $words[] = $word;
                $word = strtok($delimiters);
            }

            $searchWords = self::flagStopWords($words);
            $searchResult['accepted_words'] = $searchWords['accepted_words'];
            $searchResult['ignored_words'] = $searchWords['ignored_words'];

            // Only perform search if we have accepted words
            if (count($searchResult['accepted_words']) > 0) {
                $searchResult['products'] = self::performSearch(
                    $searchResult['accepted_words'],
                    $allWords,
                    $pageNo,
                    $rHowManyPages
                );
            }
        }

        return $searchResult;
    }

    /**
     * Perform the actual search query.
     *
     * @param array $acceptedWords Words to search for
     * @param bool $allWords Whether to match all words
     * @param int $pageNo Current page number
     * @param int &$rHowManyPages Output parameter for total pages
     * @return array List of matching products
     */
    private static function performSearch(array $acceptedWords, bool $allWords, int $pageNo, int &$rHowManyPages): array
    {
        $wordsParam = '{' . implode(', ', $acceptedWords) . '}';

        $countSql = 'SELECT catalog_count_search_result(:words, :all_words);';
        $countParams = [
            ':words' => $wordsParam,
            ':all_words' => $allWords,
        ];
        $rHowManyPages = self::howManyPages($countSql, $countParams);

        $productsPerPage = Config::get('products_per_page', 4);
        $startItem = ($pageNo - 1) * $productsPerPage;

        $sql = 'SELECT * FROM catalog_search(:words, :all_words,
                    :short_product_description_length,
                    :products_per_page, :start_page);';
        $params = [
            ':words' => $wordsParam,
            ':all_words' => $allWords,
            ':short_product_description_length' => Config::get('short_product_description_length', 150),
            ':products_per_page' => $productsPerPage,
            ':start_page' => $startItem,
        ];

        $result = DatabaseHandler::prepare($sql);
        return DatabaseHandler::getAll($result, $params) ?? [];
    }

    // ========== Admin Methods (Chapter 7) - Delegated to CatalogAdmin ==========

    /** @var array Product display options */
    public static array $mProductDisplayOptions = [
        'Default',       // 0
        'On Catalog',    // 1
        'On Department', // 2
        'On Both'        // 3
    ];

    /**
     * Retrieves all departments with their descriptions.
     * @deprecated Use CatalogAdmin::getDepartmentsWithDescriptions() instead
     */
    public static function getDepartmentsWithDescriptions(): array
    {
        return CatalogAdmin::getDepartmentsWithDescriptions();
    }

    /**
     * Updates department details.
     * @deprecated Use CatalogAdmin::updateDepartment() instead
     */
    public static function updateDepartment(int $departmentId, string $departmentName,
                                            string $departmentDescription): void
    {
        CatalogAdmin::updateDepartment($departmentId, $departmentName, $departmentDescription);
    }

    /**
     * Deletes a department.
     * @deprecated Use CatalogAdmin::deleteDepartment() instead
     */
    public static function deleteDepartment(int $departmentId): int
    {
        return CatalogAdmin::deleteDepartment($departmentId);
    }

    /**
     * Adds a new department.
     * @deprecated Use CatalogAdmin::addDepartment() instead
     */
    public static function addDepartment(string $departmentName, string $departmentDescription): void
    {
        CatalogAdmin::addDepartment($departmentName, $departmentDescription);
    }

    /**
     * Gets all categories in a department.
     * @deprecated Use CatalogAdmin::getDepartmentCategories() instead
     */
    public static function getDepartmentCategories(int $departmentId): array
    {
        return CatalogAdmin::getDepartmentCategories($departmentId);
    }

    /**
     * Adds a new category to a department.
     * @deprecated Use CatalogAdmin::addCategory() instead
     */
    public static function addCategory(int $departmentId, string $categoryName,
                                        string $categoryDescription): void
    {
        CatalogAdmin::addCategory($departmentId, $categoryName, $categoryDescription);
    }

    /**
     * Deletes a category.
     * @deprecated Use CatalogAdmin::deleteCategory() instead
     */
    public static function deleteCategory(int $categoryId): int
    {
        return CatalogAdmin::deleteCategory($categoryId);
    }

    /**
     * Updates a category.
     * @deprecated Use CatalogAdmin::updateCategory() instead
     */
    public static function updateCategory(int $categoryId, string $categoryName,
                                          string $categoryDescription): void
    {
        CatalogAdmin::updateCategory($categoryId, $categoryName, $categoryDescription);
    }

    /**
     * Gets all products in a category (for admin purposes).
     * @deprecated Use CatalogAdmin::getCategoryProducts() instead
     */
    public static function getCategoryProducts(int $categoryId): array
    {
        return CatalogAdmin::getCategoryProducts($categoryId);
    }

    /**
     * Creates a product and assigns it to a category.
     * @deprecated Use CatalogAdmin::addProductToCategory() instead
     */
    public static function addProductToCategory(int $categoryId, string $productName,
                                                string $productDescription, float $productPrice): void
    {
        CatalogAdmin::addProductToCategory($categoryId, $productName, $productDescription, $productPrice);
    }

    /**
     * Updates a product.
     * @deprecated Use CatalogAdmin::updateProduct() instead
     */
    public static function updateProduct(int $productId, string $productName,
                                         string $productDescription, float $productPrice,
                                         float $productDiscountedPrice): void
    {
        CatalogAdmin::updateProduct($productId, $productName, $productDescription,
                                    $productPrice, $productDiscountedPrice);
    }

    /**
     * Removes a product from the catalog.
     * @deprecated Use CatalogAdmin::deleteProduct() instead
     */
    public static function deleteProduct(int $productId): void
    {
        CatalogAdmin::deleteProduct($productId);
    }

    /**
     * Removes a product from a category.
     * @deprecated Use CatalogAdmin::removeProductFromCategory() instead
     */
    public static function removeProductFromCategory(int $productId, int $categoryId): int
    {
        return CatalogAdmin::removeProductFromCategory($productId, $categoryId);
    }

    /**
     * Gets all categories.
     * @deprecated Use CatalogAdmin::getCategories() instead
     */
    public static function getCategories(): array
    {
        return CatalogAdmin::getCategories();
    }

    /**
     * Gets product info for admin editing.
     * @deprecated Use CatalogAdmin::getProductInfo() instead
     */
    public static function getProductInfo(int $productId): ?array
    {
        return CatalogAdmin::getProductInfo($productId);
    }

    /**
     * Gets all categories a product belongs to.
     * @deprecated Use CatalogAdmin::getCategoriesForProduct() instead
     */
    public static function getCategoriesForProduct(int $productId): array
    {
        return CatalogAdmin::getCategoriesForProduct($productId);
    }

    /**
     * Sets product display option.
     * @deprecated Use CatalogAdmin::setProductDisplayOption() instead
     */
    public static function setProductDisplayOption(int $productId, int $display): void
    {
        CatalogAdmin::setProductDisplayOption($productId, $display);
    }

    /**
     * Assigns a product to a category.
     * @deprecated Use CatalogAdmin::assignProductToCategory() instead
     */
    public static function assignProductToCategory(int $productId, int $categoryId): void
    {
        CatalogAdmin::assignProductToCategory($productId, $categoryId);
    }

    /**
     * Moves a product from one category to another.
     * @deprecated Use CatalogAdmin::moveProductToCategory() instead
     */
    public static function moveProductToCategory(int $productId, int $sourceCategoryId,
                                                 int $targetCategoryId): void
    {
        CatalogAdmin::moveProductToCategory($productId, $sourceCategoryId, $targetCategoryId);
    }

    /**
     * Sets product image filename.
     * @deprecated Use CatalogAdmin::setImage() instead
     */
    public static function setImage(int $productId, string $imageName): void
    {
        CatalogAdmin::setImage($productId, $imageName);
    }

    /**
     * Sets product thumbnail filename.
     * @deprecated Use CatalogAdmin::setThumbnail() instead
     */
    public static function setThumbnail(int $productId, string $thumbnailName): void
    {
        CatalogAdmin::setThumbnail($productId, $thumbnailName);
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
