<?php
// Reference Composer's autoload
require_once '/var/www/html/vendor/autoload.php'; // NOSONAR - Legacy PHP application without PSR-4 autoloading
require_once __DIR__ . '/../presentation/smarty_plugins/02.function.load_departments_list.php'; // NOSONAR
use PHPUnit\Framework\TestCase;
use Smarty\Smarty;

// Mock Catalog class to provide a predictable response for testing
class Catalog
{
    public static function getDepartments() // NOSONAR - Mock class for testing
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
