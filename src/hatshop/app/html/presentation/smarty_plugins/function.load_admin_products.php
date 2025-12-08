<?php

use Hatshop\Core\Catalog;

/**
 * Smarty plugin function for admin products management.
 */
function smarty_function_load_admin_products($params, $smarty)
{
    $admin_products = new AdminProducts();
    $admin_products->init();
    $smarty->assign($params['assign'], $admin_products);
}

/**
 * Class that supports products admin functionality.
 */
class AdminProducts
{
    public $mProducts = [];
    public $mProductsCount = 0;
    public $mEditItem;
    public $mErrorMessage = '';
    public $mDepartmentId;
    public $mCategoryId;
    public $mProductId;
    public $mCategoryName = '';
    public $mAdminCategoriesLink = 'admin.php?Page=Categories';
    public $mAdminProductsTarget = 'admin.php?Page=Products';

    private $mAction = '';
    private $mActionedProductId;

    public function __construct()
    {
        $this->mDepartmentId = $this->getParam('DepartmentID');
        $this->mCategoryId = $this->getParam('CategoryID');

        $categoryDetails = Catalog::getCategoryDetails($this->mCategoryId);
        $this->mCategoryName = $categoryDetails['name'] ?? '';

        $this->parseAction();
    }

    /**
     * Get a GET parameter or trigger error.
     */
    private function getParam(string $name): int
    {
        if (isset($_GET[$name])) {
            return (int) $_GET[$name];
        }
        trigger_error($name . ' not set');
        return 0;
    }

    /**
     * Parse action from POST data.
     */
    private function parseAction(): void
    {
        foreach ($_POST as $key => $value) {
            if (substr($key, 0, 6) !== 'submit') {
                continue;
            }
            $lastUnderscore = strrpos($key, '_');
            $this->mAction = substr($key, strlen('submit_'), $lastUnderscore - strlen('submit_'));
            $this->mActionedProductId = (int) substr($key, $lastUnderscore + 1);
            break;
        }
    }

    public function init()
    {
        $this->handleAddProduct();
        $this->handleEditProduct();
        $this->handleSelectProduct();
        $this->handleUpdateProduct();
        $this->buildLinks();
        $this->loadProducts();
    }

    /**
     * Handle add product action.
     */
    private function handleAddProduct(): void
    {
        if ($this->mAction !== 'add_prod') {
            return;
        }

        $productName = $_POST['product_name'] ?? '';
        $productDescription = $_POST['product_description'] ?? '';
        $productPrice = $_POST['product_price'] ?? '';

        $this->validateProductInput($productName, $productDescription, $productPrice);

        if (empty($this->mErrorMessage)) {
            Catalog::addProductToCategory(
                $this->mCategoryId,
                $productName,
                $productDescription,
                (float) $productPrice
            );
        }
    }

    /**
     * Handle edit product action.
     */
    private function handleEditProduct(): void
    {
        if ($this->mAction === 'edit_prod') {
            $this->mEditItem = $this->mActionedProductId;
        }
    }

    /**
     * Handle select product action.
     */
    private function handleSelectProduct(): void
    {
        if ($this->mAction !== 'select_prod') {
            return;
        }

        header('Location: admin.php?Page=ProductDetails&DepartmentID=' .
               $this->mDepartmentId . '&CategoryID=' . $this->mCategoryId .
               '&ProductID=' . $this->mActionedProductId);
        exit;
    }

    /**
     * Handle update product action.
     */
    private function handleUpdateProduct(): void
    {
        if ($this->mAction !== 'update_prod') {
            return;
        }

        $productName = $_POST['name'] ?? '';
        $productDescription = $_POST['description'] ?? '';
        $productPrice = $_POST['price'] ?? '';
        $productDiscountedPrice = $_POST['discounted_price'] ?? '0';

        $this->validateProductInput($productName, $productDescription, $productPrice);

        if (!is_numeric($productDiscountedPrice)) {
            $this->mErrorMessage = 'Product discounted price must be a number!';
        }

        if (empty($this->mErrorMessage)) {
            Catalog::updateProduct(
                $this->mActionedProductId,
                $productName,
                $productDescription,
                (float) $productPrice,
                (float) $productDiscountedPrice
            );
        }
    }

    /**
     * Validate product input fields.
     */
    private function validateProductInput(string $name, string $description, string $price): void
    {
        if (empty($name)) {
            $this->mErrorMessage = 'Product name is empty';
        } elseif (empty($description)) {
            $this->mErrorMessage = 'Product description is empty';
        } elseif (empty($price) || !is_numeric($price)) {
            $this->mErrorMessage = 'Product price must be a number!';
        }
    }

    /**
     * Build admin links.
     */
    private function buildLinks(): void
    {
        $this->mAdminCategoriesLink .= '&DepartmentID=' . $this->mDepartmentId;
        $this->mAdminProductsTarget .= '&DepartmentID=' . $this->mDepartmentId .
                                       '&CategoryID=' . $this->mCategoryId;
    }

    /**
     * Load products list.
     */
    private function loadProducts(): void
    {
        $this->mProducts = Catalog::getCategoryProducts($this->mCategoryId);
        $this->mProductsCount = count($this->mProducts);
    }
}
