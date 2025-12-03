<?php

use Hatshop\Business\Catalog;

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
  // Public variables to be read from Smarty template
  public $mProducts;
  public $mPageNo;
  public $mrHowManyPages;
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
    if (isset ($_GET['DepartmentID'])) {
      $this->mDepartmentId = (int)$_GET['DepartmentID'];
    }
    // Get CategoryID from query string casting it to int
    if (isset ($_GET['CategoryID'])) {
      $this->mCategoryId = (int)$_GET['CategoryID'];
    }
    // Get PageNo from query string casting it to int
    if (isset ($_GET['PageNo'])) {
      $this->mPageNo = (int)$_GET['PageNo'];
    } else {
      $this->mPageNo = 1;
    }
    // Get search details from query string
    if (isset ($_GET['Search'])) {
       $this->mSearchString = $_GET['Search'];
    }
    // Get all_words from query string
    if (isset ($_GET['AllWords'])) {
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
    if (isset ($this->mSearchString))
    {
      $this->loadSearchResults();
    }
    /* If browsing a category, get the list of products by calling
       the getProductsInCategory business tier method */
    elseif (isset ($this->mCategoryId)) {
      $this->mProducts = Catalog::getProductsInCategory(
        $this->mCategoryId, $this->mPageNo, $this->mrHowManyPages);
    }
    /* If browsing a department, get the list of products by calling
       the getProductsOnDepartmentDisplay business tier method */
    elseif (isset ($this->mDepartmentId)) {
      $this->mProducts = Catalog::getProductsOnDepartmentDisplay(
        $this->mDepartmentId, $this->mPageNo, $this->mrHowManyPages);
    }
    /* If browsing the first page, get the list of products by
       calling the getProductsOnCatalogDisplay business
       tier method */
    else {
      $this->mProducts = Catalog::getProductsOnCatalogDisplay(
                           $this->mPageNo, $this->mrHowManyPages);
    }
  }
  private function loadSearchResults()
  {
    // Get search results
    $search_results = Catalog::search($this->mSearchString,
                                      $this->mAllWords,
                                      $this->mPageNo,
                                      $this->mrHowManyPages);
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
    /* If there are subpages of products, display navigation
       controls */
    if ($this->mrHowManyPages <= 1) {
      return;
    }
    // Read the query string
    $query_string = getenv('QUERY_STRING');
    // Find if we have PageNo in the query string
    $pos = stripos($query_string, self::PAGE_NO_PARAM);
    /* If there is no PageNo in the query string
       then we're on the first page */
    if ($pos === false)
    {
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
    } else {
      $new_query_string = str_replace(self::PAGE_NO_PARAM . $this->mPageNo,
                                      self::PAGE_NO_PARAM . ($this->mPageNo + 1),
                                      $query_string);
      $this->mNextLink = 'index.php?' . $new_query_string;
    }
  }
  private function buildPreviousLink($query_string)
  {
    // Build the Previous link
    if ($this->mPageNo == 1) {
      $this->mPreviousLink = '';
    } else {
      $new_query_string = str_replace(self::PAGE_NO_PARAM . $this->mPageNo,
                                      self::PAGE_NO_PARAM . ($this->mPageNo - 1),
                                      $query_string);
      $this->mPreviousLink = 'index.php?' . $new_query_string;
    }
  }
  private function buildProductLinks()
  {
    // Build links for product details pages
    $url = $_SESSION['page_link'];
    if (!empty($_GET)) {
      $url = $url . '&ProductID=';
    } else {
      $url = $url . '?ProductID=';
    }
    for ($i = 0; $i < count($this->mProducts); $i++)
    {
      $this->mProducts[$i]['link'] =
        $url . $this->mProducts[$i]['product_id'];
    }
  }
}
