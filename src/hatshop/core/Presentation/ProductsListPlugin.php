<?php

namespace Hatshop\Core\Presentation;

use Hatshop\Core\Catalog;
use Hatshop\Core\Config;
use Smarty\Smarty;

/**
 * Smarty plugin for loading products list.
 */
class ProductsListPlugin
{
    /**
     * Execute the plugin.
     *
     * @param array $params Plugin parameters
     * @param Smarty $smarty Smarty instance
     */
    public static function execute(array $params, Smarty $smarty): void
    {
        $productsList = new ProductsList();
        $productsList->init();
        $smarty->assign($params['assign'], $productsList);
    }
}

/**
 * Data object for products list with pagination.
 */
class ProductsList
{
    // Page number query parameter name
    private const PAGE_NO_PARAM = 'PageNo=';

    /** @var array List of products */
    public array $mProducts = [];

    /** @var int Current page number */
    public int $mPageNo = 1;

    /** @var int Total number of pages */
    public int $mrHowManyPages = 0;

    /** @var string Next page link */
    public string $mNextLink = '';

    /** @var string Previous page link */
    public string $mPreviousLink = '';

    /** @var int|null Department ID */
    private ?int $mDepartmentId = null;

    /** @var int|null Category ID */
    private ?int $mCategoryId = null;

    public function __construct()
    {
        if (isset($_GET['DepartmentID'])) {
            $this->mDepartmentId = (int) $_GET['DepartmentID'];
        }

        if (isset($_GET['CategoryID'])) {
            $this->mCategoryId = (int) $_GET['CategoryID'];
        }

        if (isset($_GET['PageNo'])) {
            $this->mPageNo = (int) $_GET['PageNo'];
        } else {
            $this->mPageNo = 1;
        }
    }

    /**
     * Initialize the products list based on context.
     */
    public function init(): void
    {
        // Determine which products to load
        if ($this->mCategoryId !== null) {
            $this->mProducts = Catalog::getProductsInCategory(
                $this->mCategoryId,
                $this->mPageNo,
                $this->mrHowManyPages
            );
        } elseif ($this->mDepartmentId !== null) {
            $this->mProducts = Catalog::getProductsOnDepartmentDisplay(
                $this->mDepartmentId,
                $this->mPageNo,
                $this->mrHowManyPages
            );
        } else {
            $this->mProducts = Catalog::getProductsOnCatalogDisplay(
                $this->mPageNo,
                $this->mrHowManyPages
            );
        }

        // Build pagination links if needed
        if ($this->mrHowManyPages > 1) {
            $this->buildPaginationLinks();
        }

        // Build product detail links
        $this->buildProductLinks();
    }

    /**
     * Build pagination links.
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
