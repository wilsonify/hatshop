<?php

use Hatshop\Core\Catalog;
use Hatshop\Core\Config;
use Hatshop\Core\FeatureFlags;

// Plugin functions inside plugin files must be named: smarty_type_name
function smarty_function_load_products_list($params, $smarty)
{
    // Create ProductsList object
    $products_list = new ProductsList();
    $products_list->init();
    // Assign template variable
    $smarty->assign($params['assign'], $products_list);
}

class ProductsList
{
    // Constants
    private const PAGE_NO_PARAM = 'PageNo=';
    private const INDEX_URL = 'index.php?';
    // Public variables to be read from Smarty template
    public $mProducts;
    public $mPageNo;
    public $mrHowManyPages = 0;
    public $mNextLink;
    public $mPreviousLink;
    public $mSearchResultsTitle;
    public $mSearch = '';
    public $mAllWords = 'off';
    public $mSearchString;
    // Private members
    private $mDepartmentId;
    private $mCategoryId;

    // Class constructor
    public function __construct()
    {
        // Get DepartmentID from query string casting it to int
        if (isset($_GET['DepartmentID'])) {
            $this->mDepartmentId = (int)$_GET['DepartmentID'];
        }
        // Get CategoryID from query string casting it to int
        if (isset($_GET['CategoryID'])) {
            $this->mCategoryId = (int)$_GET['CategoryID'];
        }
        // Get PageNo from query string casting it to int
        if (isset($_GET['PageNo'])) {
            $this->mPageNo = (int)$_GET['PageNo'];
        } else {
            $this->mPageNo = 1;
        }
        // Get search details from query string
        if (isset($_GET['Search'])) {
            $this->mSearchString = $_GET['Search'];
        }
        // Get all_words from query string
        if (isset($_GET['AllWords'])) {
            $this->mAllWords = $_GET['AllWords'];
        }
    }

    public function init()
    {
        $this->loadProducts();
        $this->buildPaginationLinks();
        $this->buildProductLinks();
    }

    private function loadProducts()
    {
        /* If searching the catalog, get the list of products by calling
           the Search business tier method */
        if (isset($this->mSearchString)) {
            $this->loadSearchResults();
            return;
        }
        /* If browsing a category, get the list of products by calling
           the getProductsInCategory business tier method */
        if (isset($this->mCategoryId)) {
            $this->mProducts = Catalog::getProductsInCategory(
                $this->mCategoryId, $this->mPageNo, $this->mrHowManyPages);
            return;
        }
        /* If browsing a department, get the list of products by calling
           the getProductsOnDepartmentDisplay business tier method */
        if (isset($this->mDepartmentId)) {
            $this->mProducts = Catalog::getProductsOnDepartmentDisplay(
                $this->mDepartmentId, $this->mPageNo, $this->mrHowManyPages);
            return;
        }
        /* If browsing the first page, get the list of products by
           calling the getProductsOnCatalogDisplay business tier method */
        $this->mProducts = Catalog::getProductsOnCatalogDisplay(
            $this->mPageNo, $this->mrHowManyPages);
    }

    private function loadSearchResults()
    {
        // Get search results
        $search_results = Catalog::search(
            $this->mSearchString,
            $this->mAllWords,
            $this->mPageNo,
            $this->mrHowManyPages
        );
        // Get the list of products
        $this->mProducts = $search_results['products'];
        // Build the title for the list of products
        if (!empty($search_results['accepted_words'])) {
            $this->mSearchResultsTitle =
                'Products containing <font class="words">'
                . ($this->mAllWords == 'on' ? 'all' : 'any') . '</font>'
                . ' of these words: <font class="words">'
                . implode(', ', $search_results['accepted_words']) .
                '</font><br />';
        }
        if (!empty($search_results['ignored_words'])) {
            $this->mSearchResultsTitle .=
                'Ignored words: <font class="words">'
                . implode(', ', $search_results['ignored_words']) .
                '</font><br />';
        }
        if (empty($search_results['products'])) {
            $this->mSearchResultsTitle .=
                'Your search generated no results.<br />';
        }
    }

    private function buildPaginationLinks()
    {
        /* If there are subpages of products, display navigation controls */
        if ($this->mrHowManyPages <= 1) {
            return;
        }
        // Read the query string
        $query_string = getenv('QUERY_STRING');
        // Find if we have PageNo in the query string
        $pos = stripos($query_string, self::PAGE_NO_PARAM);
        /* If there is no PageNo in the query string
           then we're on the first page */
        if ($pos === false) {
            $query_string .= '&' . self::PAGE_NO_PARAM . '1';
            $pos = stripos($query_string, self::PAGE_NO_PARAM);
        }
        // Read the current page number from the query string
        $temp = substr($query_string, $pos);
        sscanf($temp, self::PAGE_NO_PARAM . '%d', $this->mPageNo);
        $this->buildNextLink($query_string);
        $this->buildPreviousLink($query_string);
    }

    private function buildNextLink($query_string)
    {
        // Build the Next link
        if ($this->mPageNo >= $this->mrHowManyPages) {
            $this->mNextLink = '';
            return;
        }
        $new_query_string = str_replace(
            self::PAGE_NO_PARAM . $this->mPageNo,
            self::PAGE_NO_PARAM . ($this->mPageNo + 1),
            $query_string
        );
        $this->mNextLink = self::INDEX_URL . $new_query_string;
    }

    private function buildPreviousLink($query_string)
    {
        // Build the Previous link
        if ($this->mPageNo == 1) {
            $this->mPreviousLink = '';
            return;
        }
        $new_query_string = str_replace(
            self::PAGE_NO_PARAM . $this->mPageNo,
            self::PAGE_NO_PARAM . ($this->mPageNo - 1),
            $query_string
        );
        $this->mPreviousLink = self::INDEX_URL . $new_query_string;
    }

    private function buildProductLinks()
    {
        // Build links for product details pages
        $url = $this->buildProductDetailUrl();

        $paypalEnabled = FeatureFlags::isEnabled(FeatureFlags::FEATURE_PAYPAL);
        $shoppingCartEnabled = FeatureFlags::isEnabled(FeatureFlags::FEATURE_SHOPPING_CART);
        $cartBaseUrl = $this->buildCartBaseUrl();

        for ($i = 0; $i < count($this->mProducts); $i++) {
            $this->mProducts[$i]['link'] = $url . $this->mProducts[$i]['product_id'];

            // Create the PayPal link if feature is enabled
            if ($paypalEnabled) {
                $this->mProducts[$i]['paypal'] = $this->buildPaypalLink($this->mProducts[$i]);
            }

            // Create the shopping cart "Add to Cart" link if feature is enabled
            if ($shoppingCartEnabled) {
                $this->mProducts[$i]['add_to_cart_link'] = $cartBaseUrl .
                    'CartAction=' . Config::get('cart_action_add') .
                    '&ProductID=' . $this->mProducts[$i]['product_id'];
            }
        }
    }

    /**
     * Build the base URL for product detail links.
     */
    private function buildProductDetailUrl(): string
    {
        $url = $_SESSION['page_link'] ?? 'index.php';
        $separator = empty($_GET) ? '?ProductID=' : '&ProductID=';
        return $url . $separator;
    }

    /**
     * Build the base URL for cart action links.
     */
    private function buildCartBaseUrl(): string
    {
        $cartAddUrl = self::INDEX_URL;
        if (isset($_GET['DepartmentID'])) {
            $cartAddUrl .= 'DepartmentID=' . (int)$_GET['DepartmentID'] . '&';
        }
        if (isset($_GET['CategoryID'])) {
            $cartAddUrl .= 'CategoryID=' . (int)$_GET['CategoryID'] . '&';
        }
        return $cartAddUrl;
    }

    /**
     * Build a PayPal add-to-cart link for a product.
     */
    private function buildPaypalLink(array $product): string
    {
        $paypalEmail = Config::get('paypal_email');
        $paypalUrl = Config::get('paypal_url');
        $paypalReturnUrl = Config::get('paypal_return_url');
        $paypalCancelUrl = Config::get('paypal_cancel_url');
        $paypalCurrency = Config::get('paypal_currency_code');

        $productPrice = ($product['discounted_price'] == 0)
            ? $product['price']
            : $product['discounted_price'];

        return 'JavaScript:OpenPayPalWindow(&quot;' .
            $paypalUrl . '?' .
            'cmd=_cart&amp;business=' . rawurlencode($paypalEmail) .
            '&amp;item_name=' . rawurlencode($product['name']) .
            '&amp;amount=' . $productPrice .
            '&amp;currency=' . $paypalCurrency .
            '&amp;add=1&amp;return=' . rawurlencode($paypalReturnUrl) .
            '&amp;cancel_return=' . rawurlencode($paypalCancelUrl) . '&quot;)';
    }
}
