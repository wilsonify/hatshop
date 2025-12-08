<?php

namespace Hatshop\Core\Presentation;

use Hatshop\Core\Catalog;
use Smarty\Smarty;

/**
 * Smarty plugin for loading departments list.
 */
class DepartmentsListPlugin
{
    /**
     * Execute the plugin.
     *
     * @param array $params Plugin parameters
     * @param Smarty $smarty Smarty instance
     */
    public static function execute(array $params, Smarty $smarty): void
    {
        $departmentsList = new DepartmentsList();
        $smarty->assign($params['assign'], $departmentsList);
    }
}

/**
 * Data object for departments list.
 */
class DepartmentsList
{
    /** @var array List of departments */
    public array $mDepartments;

    /** @var int|null Currently selected department ID */
    public ?int $mSelectedDepartment = null;

    public function __construct()
    {
        if (isset($_GET['DepartmentID'])) {
            $this->mSelectedDepartment = (int) $_GET['DepartmentID'];
        }

        $this->mDepartments = Catalog::getDepartments();
    }
}
