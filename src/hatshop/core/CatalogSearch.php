<?php

namespace Hatshop\Core;

/**
 * Business tier class for catalog search operations.
 *
 * This class handles search functionality including word filtering,
 * pagination, and result retrieval.
 */
class CatalogSearch
{
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
}
