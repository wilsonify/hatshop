<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../presentation/smarty_plugins/03.function.load_department.php';
use PHPUnit\Framework\TestCase;
use Smarty\Smarty;

class p03_SmartyFunctionLoadDepartmentTest extends TestCase
{
    public function testLoadDepartmentWithValidDepartmentID()
    {
        $_GET['DepartmentID'] = 1;
        $params = ['assign' => 'department'];
        $smarty = new Smarty();
        smarty_function_load_department($params, $smarty);
        $department = $smarty->getTemplateVars('department');

        $this->assertInstanceOf(Department::class, $department);
        $this->assertEquals('Electronics', $department->mNameLabel);
        $this->assertEquals('Description of Electronics', $department->mDescriptionLabel);
    }

    public function testLoadDepartmentWithoutDepartmentID()
    {
        unset($_GET['DepartmentID']);
        $params = ['assign' => 'department'];
        $smarty = new Smarty();

        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        smarty_function_load_department($params, $smarty);
    }

    public function testLoadDepartmentWithCategoryID()
    {
        $_GET['DepartmentID'] = 1;
        $_GET['CategoryID'] = 2;
        $params = ['assign' => 'department'];
        $smarty = new Smarty();
        smarty_function_load_department($params, $smarty);
        $department = $smarty->getTemplateVars('department');

        $this->assertInstanceOf(Department::class, $department);
        $this->assertEquals('Electronics &raquo; Books', $department->mNameLabel);
        $this->assertEquals('Description of Books', $department->mDescriptionLabel);
    }
}