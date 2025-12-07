<?php

namespace Hatshop\Core\Presentation;

use Hatshop\Core\Catalog;
use Smarty\Smarty;

/**
 * Smarty plugin for loading search box and handling search.
 */
class SearchBoxPlugin
{
    /**
     * Execute the plugin.
     *
     * @param array $params Plugin parameters
     * @param Smarty $smarty Smarty instance
     */
    public static function execute(array $params, Smarty $smarty): void
    {
        $searchBox = new SearchBox();
        $searchBox->init();
        $smarty->assign($params['assign'], $searchBox);
    }
}

/**
 * Data object for search functionality.
 */
class SearchBox
{
    // Page number query parameter
    private const PAGE_NO_PARAM = 'PageNo=';

    /** @var string Search string */
    public string $mSearchString = '';

    /** @var bool Whether to match all words */
    public bool $mAllWords = false;

    /** @var array Accepted search words */
    public array $mAcceptedWords = [];

    /** @var array Ignored (stop) words */
    public array $mIgnoredWords = [];

    /** @var array Search result products */
    public array $mProducts = [];

    /** @var int Current page number */
    public int $mPageNo = 1;

    /** @var int Total number of pages */
    public int $mrHowManyPages = 0;

    /** @var string Next page link */
    public string $mNextLink = '';

    /** @var string Previous page link */
    public string $mPreviousLink = '';

    public function __construct()
    {
        if (isset($_GET['Search'])) {
            $this->mSearchString = trim($_GET['Search']);
        }

        if (isset($_GET['AllWords'])) {
            $this->mAllWords = $_GET['AllWords'] === 'on';
        }

        if (isset($_GET['PageNo'])) {
            $this->mPageNo = (int) $_GET['PageNo'];
        } else {
            $this->mPageNo = 1;
        }
    }

    /**
     * Initialize search results.
     */
    public function init(): void
    {
        if (empty($this->mSearchString)) {
            return;
        }

        $searchResults = Catalog::search(
            $this->mSearchString,
            $this->mAllWords,
            $this->mPageNo,
            $this->mrHowManyPages
        );

        $this->mAcceptedWords = $searchResults['accepted_words'];
        $this->mIgnoredWords = $searchResults['ignored_words'];
        $this->mProducts = $searchResults['products'];

        // Build pagination links if needed
        if ($this->mrHowManyPages > 1) {
            $this->buildPaginationLinks();
        }

        // Build product detail links
        $this->buildProductLinks();
    }

    /**
     * Build pagination links for search results.
     */
    private function buildPaginationLinks(): void
    {
        $queryString = getenv('QUERY_STRING') ?: '';

        // Find PageNo in query string
        $pos = stripos($queryString, self::PAGE_NO_PARAM);

        // If no PageNo in query string, add it
        if ($pos === false) {
            $queryString .= '&' . self::PAGE_NO_PARAM . '1';
            $pos = stripos($queryString, self::PAGE_NO_PARAM);
        }

        // Read current page number from query string
        $temp = substr($queryString, $pos);
        sscanf($temp, self::PAGE_NO_PARAM . '%d', $this->mPageNo);

        // Build Next link
        if ($this->mPageNo >= $this->mrHowManyPages) {
            $this->mNextLink = '';
        } else {
            $newQueryString = str_replace(
                self::PAGE_NO_PARAM . $this->mPageNo,
                self::PAGE_NO_PARAM . ($this->mPageNo + 1),
                $queryString
            );
            $this->mNextLink = 'index.php?' . $newQueryString;
        }

        // Build Previous link
        if ($this->mPageNo === 1) {
            $this->mPreviousLink = '';
        } else {
            $newQueryString = str_replace(
                self::PAGE_NO_PARAM . $this->mPageNo,
                self::PAGE_NO_PARAM . ($this->mPageNo - 1),
                $queryString
            );
            $this->mPreviousLink = 'index.php?' . $newQueryString;
        }
    }

    /**
     * Build product detail page links.
     */
    private function buildProductLinks(): void
    {
        $url = $_SESSION['page_link'] ?? '';

        if (!empty($_GET)) {
            $url .= '&ProductID=';
        } else {
            $url .= '?ProductID=';
        }

        for ($i = 0; $i < count($this->mProducts); $i++) {
            $this->mProducts[$i]['link'] = $url . $this->mProducts[$i]['product_id'];
        }
    }
}
