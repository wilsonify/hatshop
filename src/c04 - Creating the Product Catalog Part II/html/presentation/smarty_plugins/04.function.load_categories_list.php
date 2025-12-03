<?php
// Plugin functions inside plugin files must be named: smarty_type_name
function smarty_function_load_categories_list($params, $smarty)
{
    $categoriesList = new CategoriesList();
    $categoriesList->initialize();

    $smarty->assign($params['assign'], $categoriesList);
}

class CategoriesList
{
    public $mCategorySelected = 0;
    public $mDepartmentSelected = 0;
    public $mCategories = [];

    public function __construct($categoriesFetcher = null)
    {
        $this->mDepartmentSelected = $this->getQueryParameter('DepartmentID');
        $this->mCategorySelected = $this->getQueryParameter('CategoryID', 0);
        $this->categoriesFetcher = $categoriesFetcher ?: [Catalog::class, 'GetCategoriesInDepartment'];
    }

    public function initialize()
    {
        $this->mCategories = call_user_func($this->categoriesFetcher, $this->mDepartmentSelected);
        $this->addCategoryLinks();
    }


    public function getQueryParameter($paramName, $default = null)
    {
        if (isset($_GET[$paramName])) {
            return (int)$_GET[$paramName];
        }

        if ($default === null) {
            trigger_error("$paramName not set", E_USER_WARNING);
        }

        return $default;
    }

    public function fetchCategories($departmentId)
    {
        return Catalog::GetCategoriesInDepartment($departmentId);
    }

    public function addCategoryLinks()
    {
        foreach ($this->mCategories as &$category) {
            $category['link'] = $this->buildCategoryLink($category['category_id']);
        }
    }

    public function buildCategoryLink($categoryId)
    {
        return sprintf(
            'index.php?DepartmentID=%d&CategoryID=%d',
            $this->mDepartmentSelected,
            $categoryId
        );
    }
}
