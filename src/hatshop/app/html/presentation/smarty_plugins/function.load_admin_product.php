<?php

use Hatshop\Core\CatalogAdmin;
use Hatshop\Core\CategoryAdmin;

/**
 * Path constant for product images directory.
 */
const PRODUCT_IMAGES_PATH = '/product_images/';

/**
 * URL parameter constant for category ID.
 */
const PARAM_CATEGORY_ID = '&CategoryID=';

/**
 * Smarty plugin function for admin product detail management.
 */
function smarty_function_load_admin_product($params, $smarty)
{
    $admin_product = new AdminProduct();
    $admin_product->init();
    $smarty->assign($params['assign'], $admin_product);
}

/**
 * Class that supports single product admin functionality.
 */
class AdminProduct
{
    public $mProductName = '';
    public $mProductImage = '';
    public $mProductThumbnail = '';
    public $mProductDisplay = 0;
    public $mProductCategoriesString = '';
    public $mRemoveFromCategories = [];
    public $mProductDisplayOptions = [];
    public $mAssignOrMoveTo = [];
    public $mProductId;
    public $mCategoryId;
    public $mDepartmentId;
    public $mRemoveFromCategoryButtonDisabled = false;
    public $mAdminProductsLink = 'admin.php?Page=Products';
    public $mAdminProductTarget = 'admin.php?Page=ProductDetails';

    public function __construct()
    {
        $this->mDepartmentId = $this->getRequiredParam('DepartmentID');
        $this->mCategoryId = $this->getRequiredParam('CategoryID');
        $this->mProductId = $this->getRequiredParam('ProductID');
        $this->mProductDisplayOptions = CatalogAdmin::$mProductDisplayOptions;
    }

    /**
     * Get a required GET parameter or trigger error.
     */
    private function getRequiredParam(string $name): int
    {
        if (!isset($_GET[$name])) {
            trigger_error($name . ' not set');
            return 0;
        }
        return (int) $_GET[$name];
    }

    public function init()
    {
        $this->handleImageUpload();
        $this->handleRemoveFromCategory();
        $this->handleSetDisplayOption();
        $this->handleRemoveFromCatalog();
        $this->handleAssignToCategory();
        $this->handleMoveToCategory();
        $this->loadProductInfo();
    }

    /**
     * Handle image upload action.
     */
    private function handleImageUpload(): void
    {
        if (!isset($_POST['Upload'])) {
            return;
        }

        $imagesPath = SITE_ROOT . PRODUCT_IMAGES_PATH;
        if (!is_writeable($imagesPath)) {
            echo "Can't write to the product_images folder";
            exit;
        }

        if (isset($_FILES['ImageUpload']) && $_FILES['ImageUpload']['error'] === 0) {
            move_uploaded_file(
                $_FILES['ImageUpload']['tmp_name'],
                $imagesPath . $_FILES['ImageUpload']['name']
            );
            CatalogAdmin::setImage($this->mProductId, $_FILES['ImageUpload']['name']);
        }

        if (isset($_FILES['ThumbnailUpload']) && $_FILES['ThumbnailUpload']['error'] === 0) {
            move_uploaded_file(
                $_FILES['ThumbnailUpload']['tmp_name'],
                $imagesPath . $_FILES['ThumbnailUpload']['name']
            );
            CatalogAdmin::setThumbnail($this->mProductId, $_FILES['ThumbnailUpload']['name']);
        }
    }

    /**
     * Handle remove from category action.
     */
    private function handleRemoveFromCategory(): void
    {
        if (!isset($_POST['RemoveFromCategory'])) {
            return;
        }

        $targetCategoryId = (int) $_POST['TargetCategoryIdRemove'];
        $stillExists = CatalogAdmin::removeProductFromCategory($this->mProductId, $targetCategoryId);

        if ($stillExists === 0) {
            $this->redirectToProducts();
        }
    }

    /**
     * Handle set display option action.
     */
    private function handleSetDisplayOption(): void
    {
        if (!isset($_POST['SetProductDisplayOption'])) {
            return;
        }

        $productDisplay = (int) $_POST['ProductDisplay'];
        CatalogAdmin::setProductDisplayOption($this->mProductId, $productDisplay);
    }

    /**
     * Handle remove from catalog action.
     */
    private function handleRemoveFromCatalog(): void
    {
        if (!isset($_POST['RemoveFromCatalog'])) {
            return;
        }

        CatalogAdmin::deleteProduct($this->mProductId);
        $this->redirectToProducts();
    }

    /**
     * Handle assign to category action.
     */
    private function handleAssignToCategory(): void
    {
        if (!isset($_POST['Assign'])) {
            return;
        }

        $targetCategoryId = (int) $_POST['TargetCategoryIdAssign'];
        CatalogAdmin::assignProductToCategory($this->mProductId, $targetCategoryId);
    }

    /**
     * Handle move to category action.
     */
    private function handleMoveToCategory(): void
    {
        if (!isset($_POST['Move'])) {
            return;
        }

        $targetCategoryId = (int) $_POST['TargetCategoryIdMove'];
        CatalogAdmin::moveProductToCategory($this->mProductId, $this->mCategoryId, $targetCategoryId);

        header('Location: admin.php?Page=ProductDetails&DepartmentID=' .
               $this->mDepartmentId . PARAM_CATEGORY_ID .
               $targetCategoryId . '&ProductID=' . $this->mProductId);
        exit;
    }

    /**
     * Redirect to products list page.
     */
    private function redirectToProducts(): void
    {
        header('Location: admin.php?Page=Products&DepartmentID=' .
               $this->mDepartmentId . PARAM_CATEGORY_ID . $this->mCategoryId);
        exit;
    }

    /**
     * Load product information.
     */
    private function loadProductInfo(): void
    {
        $productInfo = CatalogAdmin::getProductInfo($this->mProductId);
        $this->mProductName = $productInfo['name'] ?? '';
        $this->mProductImage = $productInfo['image'] ?? '';
        $this->mProductThumbnail = $productInfo['thumbnail'] ?? '';
        $this->mProductDisplay = $productInfo['display'] ?? 0;

        $productCategories = CatalogAdmin::getCategoriesForProduct($this->mProductId);

        if (count($productCategories) === 1) {
            $this->mRemoveFromCategoryButtonDisabled = true;
        }

        $temp1 = [];
        foreach ($productCategories as $category) {
            $temp1[$category['category_id']] = $category['name'];
        }

        $this->mRemoveFromCategories = $temp1;
        $this->mProductCategoriesString = implode(', ', $temp1);

        $allCategories = CategoryAdmin::getCategories();
        $temp2 = [];
        foreach ($allCategories as $category) {
            $temp2[$category['category_id']] = $category['name'];
        }

        $this->mAssignOrMoveTo = array_diff($temp2, $temp1);

        $this->mAdminProductsLink .= '&DepartmentID=' . $this->mDepartmentId .
                                     PARAM_CATEGORY_ID . $this->mCategoryId;
        $this->mAdminProductTarget .= '&DepartmentID=' . $this->mDepartmentId .
                                      PARAM_CATEGORY_ID . $this->mCategoryId .
                                      '&ProductID=' . $this->mProductId;
    }
}
