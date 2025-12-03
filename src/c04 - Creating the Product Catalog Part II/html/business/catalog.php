<?php
// Business tier class for reading product catalog information
class Catalog
{
  // Retrieves all departments
  public static function getDepartments()
  {
    // Build SQL query
    $sql = 'SELECT * FROM catalog_get_departments_list();';

    // Prepare the statement with PDO-specific functionality
    $result = DatabaseHandler::prepare($sql);

    // Execute the query and return the results
    return DatabaseHandler::getAll($result);
  }

  // Retrieves complete details for the specified department
  public static function getDepartmentDetails($departmentId)
  {
    // Build SQL query
    $sql = 'SELECT *
            FROM catalog_get_department_details(:department_id);';
    // Build the parameters array
    $params = array (':department_id' => $departmentId);
    // Prepare the statement with PDO-specific functionality
    $result = DatabaseHandler::prepare($sql);

    // Execute the query and return the results
    return DatabaseHandler::getRow($result, $params);
  }

  // Retrieves list of categories that belong to a department
  public static function getCategoriesInDepartment($departmentId)
  {
    // Build SQL query
    $sql = 'SELECT *
            FROM catalog_get_categories_list(:department_id);';
    // Build the parameters array
    $params = array (':department_id' => $departmentId);
    // Prepare the statement with PDO-specific functionality
    $result = DatabaseHandler::prepare($sql);

    // Execute the query and return the results
    return DatabaseHandler::getAll($result, $params);
  }

  // Retrieves complete details for the specified category
  public static function getCategoryDetails($categoryId)
  {
    // Build SQL query
    $sql = 'SELECT *
            FROM catalog_get_category_details(:category_id);';
    // Build the parameters array
    $params = array (':category_id' => $categoryId);
    // Prepare the statement with PDO-specific functionality
    $result = DatabaseHandler::prepare($sql);

    // Execute the query and return the results
    return DatabaseHandler::getRow($result, $params);
  }

  /* Calculates how many pages of products could be filled by the
     number of products returned by the $countSql query */
  private static function howManyPages($countSql, $countSqlParams)
  {
    // Create a hash for the sql query
    $queryHashCode = hash("sha512",$countSql . var_export($countSqlParams, true));

    // Verify if we have the query results in cache
    if (isset ($_SESSION['last_count_hash']) &&
        isset ($_SESSION['how_many_pages']) &&
        $_SESSION['last_count_hash'] === $queryHashCode)
    {
      // Retrieve the the cached value
      $how_many_pages = $_SESSION['how_many_pages'];
    }
    else
    {
      // Execute the query
      $prepared = DatabaseHandler::prepare($countSql);
      $items_count = DatabaseHandler::getOne($prepared, $countSqlParams);

      // Calculate the number of pages
      $how_many_pages = ceil($items_count / PRODUCTS_PER_PAGE);

      // Save the query and its count result in the session
      $_SESSION['last_count_hash'] = $queryHashCode;
      $_SESSION['how_many_pages'] = $how_many_pages;
    }

    // Return the number of pages
    return $how_many_pages;
  }

  // Retrieves list of products that belong to a category
  public static function getProductsInCategory(
                           $categoryId, $pageNo, &$rHowManyPages)
  {
    // Query that returns the number of products in the category
    $sql = 'SELECT catalog_count_products_in_category(:category_id);';
    $params = array (':category_id' => $categoryId);
    // Calculate the number of pages required to display the products
    $rHowManyPages = Catalog::howManyPages($sql, $params);
    // Calculate the start item
    $start_item = ($pageNo - 1) * PRODUCTS_PER_PAGE;

    // Retrieve the list of products
    $sql = 'SELECT *
            FROM   catalog_get_products_in_category(
                     :category_id, :short_product_description_length,
                     :products_per_page, :start_item);';
    $params = array (
      ':category_id' => $categoryId,
      ':short_product_description_length' => SHORT_PRODUCT_DESCRIPTION_LENGTH,
      ':products_per_page' => PRODUCTS_PER_PAGE,
      ':start_item' => $start_item);
    $result = DatabaseHandler::prepare($sql);

    // Execute the query and return the results
    return DatabaseHandler::getAll($result, $params);
  }

  // Retrieves the list of products for the department page
  public static function getProductsOnDepartmentDisplay(
                           $departmentId, $pageNo, &$rHowManyPages)
  {
    // Query that returns the number of products in the department page
    $sql = 'SELECT catalog_count_products_on_department(:department_id);';
    $params = array (':department_id' => $departmentId);
    // Calculate the number of pages required to display the products
    $rHowManyPages = Catalog::howManyPages($sql, $params);
    // Calculate the start item
    $start_item = ($pageNo - 1) * PRODUCTS_PER_PAGE;

    // Retrieve the list of products
    $sql = 'SELECT *
            FROM   catalog_get_products_on_department(
                     :department_id, :short_product_description_length,
                     :products_per_page, :start_item);';
    $params = array (
      ':department_id' => $departmentId,
      ':short_product_description_length' => SHORT_PRODUCT_DESCRIPTION_LENGTH,
      ':products_per_page' => PRODUCTS_PER_PAGE,
      ':start_item' => $start_item);
    $result = DatabaseHandler::prepare($sql);

    // Execute the query and return the results
    return DatabaseHandler::getAll($result, $params);
  }

  // Retrieves the list of products on catalog display
  public static function getProductsOnCatalogDisplay($pageNo, &$rHowManyPages)
  {
    // Query that returns the number of products for the front catalog page
    $sql = 'SELECT catalog_count_products_on_catalog();';
    // Calculate the number of pages required to display the products
    $rHowManyPages = Catalog::howManyPages($sql, null);
    // Calculate the start item
    $start_item = ($pageNo - 1) * PRODUCTS_PER_PAGE;

    // Retrieve the list of products
    $sql = 'SELECT *
            FROM   catalog_get_products_on_catalog(
                     :short_product_description_length,
                     :products_per_page, :start_item);';
    $params = array (
      ':short_product_description_length' => SHORT_PRODUCT_DESCRIPTION_LENGTH,
      ':products_per_page' => PRODUCTS_PER_PAGE,
      ':start_item' => $start_item);
    $result = DatabaseHandler::prepare($sql);

    // Execute the query and return the results
    return DatabaseHandler::getAll($result, $params);
  }

  // Retrieves complete product details
  public static function getProductDetails($productId)
  {
    // Build SQL query
    $sql = 'SELECT *
            FROM catalog_get_product_details(:product_id);';
    // Build the parameters array
    $params = array (':product_id' => $productId);
    // Prepare the statement with PDO-specific functionality
    $result = DatabaseHandler::prepare($sql);

    // Execute the query and return the results
    return DatabaseHandler::getRow($result, $params);
  }
}

