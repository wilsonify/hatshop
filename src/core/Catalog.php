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
}
