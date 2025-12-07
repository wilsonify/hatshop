<?php

namespace Hatshop\Core\Presentation;

use Hatshop\Core\Catalog;
use Smarty\Smarty;

/**
 * Smarty plugin for loading department details.
 */
class DepartmentPlugin
{
    /**
     * Execute the plugin.
     *
     * @param array $params Plugin parameters
     * @param Smarty $smarty Smarty instance
     */
    public static function execute(array $params, Smarty $smarty): void
    {
        $department = new Department();
        $smarty->assign($params['assign'], $department);
    }
}

/**
 * Data object for department details.
 */
class Department
{
    /** @var string Department name */
    public string $mName = '';

    /** @var string Department description */
    public string $mDescription = '';

    public function __construct()
    {
        if (isset($_GET['DepartmentID'])) {
            $departmentId = (int) $_GET['DepartmentID'];
            $details = Catalog::getDepartmentDetails($departmentId);

            if ($details) {
                $this->mName = $details['name'] ?? '';
                $this->mDescription = $details['description'] ?? '';
            }
        }
    }
}
