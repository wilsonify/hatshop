<?php

use Hatshop\Core\Catalog;

/**
 * Smarty plugin function for admin categories management.
 */
function smarty_function_load_admin_categories($params, $smarty)
{
    $admin_categories = new AdminCategories();
    $admin_categories->init();
    $smarty->assign($params['assign'], $admin_categories);
}

/**
 * Class that supports categories admin functionality.
 */
class AdminCategories
{
    public $mCategoriesCount = 0;
    public $mCategories = [];
    public $mEditItem = -1;
    public $mErrorMessage = '';
    public $mDepartmentId;
    public $mDepartmentName = '';
    public $mAdminDepartmentsLink = 'admin.php?Page=Departments';
    public $mAdminCategoriesTarget = 'admin.php?Page=Categories';

    private $mAction = '';
    private $mActionedCategoryId;

    public function __construct()
    {
        if (isset($_GET['DepartmentID'])) {
            $this->mDepartmentId = (int) $_GET['DepartmentID'];
        } else {
            trigger_error('DepartmentID not set');
        }

        $departmentDetails = Catalog::getDepartmentDetails($this->mDepartmentId);
        $this->mDepartmentName = $departmentDetails['name'] ?? '';

        foreach ($_POST as $key => $value) {
            if (substr($key, 0, 6) === 'submit') {
                $lastUnderscore = strrpos($key, '_');
                $this->mAction = substr($key, strlen('submit_'),
                                        $lastUnderscore - strlen('submit_'));
                $this->mActionedCategoryId = (int) substr($key, $lastUnderscore + 1);
                break;
            }
        }
    }

    public function init()
    {
        // Adding a new category
        if ($this->mAction === 'add_categ') {
            $categoryName = $_POST['category_name'] ?? '';
            $categoryDescription = $_POST['category_description'] ?? '';

            if (empty($categoryName)) {
                $this->mErrorMessage = 'Category name is empty';
            }

            if (empty($this->mErrorMessage)) {
                Catalog::addCategory($this->mDepartmentId, $categoryName, $categoryDescription);
            }
        }

        // Editing an existing category
        if ($this->mAction === 'edit_categ') {
            $this->mEditItem = $this->mActionedCategoryId;
        }

        // Updating a category
        if ($this->mAction === 'update_categ') {
            $categoryName = $_POST['name'] ?? '';
            $categoryDescription = $_POST['description'] ?? '';

            if (empty($categoryName)) {
                $this->mErrorMessage = 'Category name is empty';
            }

            if (empty($this->mErrorMessage)) {
                Catalog::updateCategory($this->mActionedCategoryId, $categoryName, $categoryDescription);
            }
        }

        // Deleting a category
        if ($this->mAction === 'delete_categ') {
            $status = Catalog::deleteCategory($this->mActionedCategoryId);

            if ($status < 0) {
                $this->mErrorMessage = 'Category not empty';
            }
        }

        // Editing category's products
        if ($this->mAction === 'edit_products') {
            header('Location: admin.php?Page=Products&DepartmentID=' .
                   $this->mDepartmentId . '&CategoryID=' .
                   $this->mActionedCategoryId);
            exit;
        }

        $this->mAdminCategoriesTarget .= '&DepartmentID=' . $this->mDepartmentId;

        // Load the list of categories
        $this->mCategories = Catalog::getDepartmentCategories($this->mDepartmentId);
        $this->mCategoriesCount = count($this->mCategories);
    }
}
