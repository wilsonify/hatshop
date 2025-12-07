<?php

namespace Hatshop\Core\Presentation;

use Hatshop\Core\Catalog;
use Smarty\Smarty;

/**
 * Smarty plugin for loading categories list.
 */
class CategoriesListPlugin
{
    /**
     * Execute the plugin.
     *
     * @param array $params Plugin parameters
     * @param Smarty $smarty Smarty instance
     */
    public static function execute(array $params, Smarty $smarty): void
    {
        $categoriesList = new CategoriesList();
        $smarty->assign($params['assign'], $categoriesList);
    }
}

/**
 * Data object for categories list.
 */
class CategoriesList
{
    /** @var array List of categories */
    public array $mCategories = [];

    /** @var int|null Currently selected category ID */
    public ?int $mSelectedCategory = null;

    /** @var int|null Current department ID */
    private ?int $mDepartmentId = null;

    public function __construct()
    {
        if (isset($_GET['DepartmentID'])) {
            $this->mDepartmentId = (int) $_GET['DepartmentID'];
        }

        if (isset($_GET['CategoryID'])) {
            $this->mSelectedCategory = (int) $_GET['CategoryID'];
        }

        if ($this->mDepartmentId !== null) {
            $this->mCategories = Catalog::getCategoriesInDepartment($this->mDepartmentId);
        }
    }
}
