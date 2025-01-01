<?php
require_once __DIR__ . '/../presentation/smarty_plugins/function.load_departments_list.php';
use PHPUnit\Framework\TestCase;

// Mock Catalog class to provide a predictable response for testing
class Catalog
{
    public static function GetDepartments()
    {
        return [
            ['department_id' => 1, 'name' => 'Electronics'],
            ['department_id' => 2, 'name' => 'Books'],
        ];
    }
}

class DepartmentsListTest extends TestCase
{
    public function testConstructorWithDepartmentIDInQueryString()
    {
        $_GET['DepartmentID'] = 2;
        $departmentsList = new DepartmentsList();
        $this->assertEquals(2, $departmentsList->mSelectedDepartment);
    }

    public function testConstructorWithoutDepartmentIDInQueryString()
    {
        unset($_GET['DepartmentID']);
        $departmentsList = new DepartmentsList();
        $this->assertEquals(-1, $departmentsList->mSelectedDepartment);
    }

    public function testInitCreatesDepartmentLinks()
    {
        unset($_GET['DepartmentID']);
        $departmentsList = new DepartmentsList();
        $departmentsList->init();

        $expected = [
            [
                'department_id' => 1,
                'name' => 'Electronics',
                'link' => 'index.php?DepartmentID=1'
            ],
            [
                'department_id' => 2,
                'name' => 'Books',
                'link' => 'index.php?DepartmentID=2'
            ]
        ];

        $this->assertEquals($expected, $departmentsList->mDepartments);
    }
}

class SmartyFunctionLoadDepartmentsListAssignsVariableTest  extends TestCase
{
    public function testSmartyFunctionLoadDepartmentsListAssignsVariable()
    {
        $smarty = $this->createMock(Smarty::class);
        $smarty->expects($this->once())
               ->method('assign')
               ->with(
                   $this->equalTo('departments_list'),
                   $this->isInstanceOf(DepartmentsList::class)
               );

        $params = ['assign' => 'departments_list'];
        smarty_function_load_departments_list($params, $smarty);
    }
}
