<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../presentation/smarty_plugins/function.load_categories_list.php';
use PHPUnit\Framework\TestCase;
use Smarty\Smarty;


class CategoriesListTest extends TestCase
{
    protected function setUp(): void
    {
        /*
        $_GET is a superglobal array
        used to collect form data (using the GET method).
        It can also collect query string parameters from the URL.
        on Setup, before each test, ensure $_GET is reset
        */
        $_GET = [];
    }

    public function testGetQueryParameterWithValidParam()
    {
        // Simulate $_GET['DepartmentID'] being set
        $_GET['DepartmentID'] = 1;

        $categoriesList = new CategoriesList();
        $result = $categoriesList->getQueryParameter('DepartmentID');

        // Assert that the correct value is returned from $_GET
        $this->assertEquals(1, $result);  // Expecting 1 as it was set in $_GET
    }

    public function testGetQueryParameterWithMissingParamAndDefault()
    {
        // Simulate $_GET missing 'CategoryID'
        $_GET['DepartmentID'] = 1;

        $categoriesList = new CategoriesList();
        // Here we expect the default value for 'CategoryID' since it's not in $_GET
        $result = $categoriesList->getQueryParameter('CategoryID', 0);

        $this->assertEquals(0, $result);  // Default should be returned (0)
    }

    public function testFetchCategories()
    {
        $categoriesList = new CategoriesList();
        $categoriesList->mDepartmentSelected = 1;
        $categoriesList->mCategories = [
                ['category_id' => 101, 'name' => 'Category 101'],
                ['category_id' => 102, 'name' => 'Category 102']
            ];
        $this->assertCount(2, $categoriesList->mCategories);
        $this->assertEquals('Category 101', $categoriesList->mCategories[0]['name']);
    }

    public function testAddCategoryLinks()
    {
        $_GET['DepartmentID'] = 1;
        $categoriesList = new CategoriesList();
        $categoriesList->mCategories = [
            ['category_id' => 101, 'name' => 'Category 101'],
            ['category_id' => 102, 'name' => 'Category 102']
        ];
        $categoriesList->addCategoryLinks();
        $this->assertEquals(
            'index.php?DepartmentID=1&CategoryID=101',
            $categoriesList->mCategories[0]['link']
        );
        $this->assertEquals(
            'index.php?DepartmentID=1&CategoryID=102',
            $categoriesList->mCategories[1]['link']
        );
    }

    public function testBuildCategoryLink()
    {
        $categoriesList = new CategoriesList();
        $link = $categoriesList->buildCategoryLink(101);
        $this->assertEquals('index.php?DepartmentID=0&CategoryID=101', $link);

        // If you set a department ID
        $categoriesList->mDepartmentSelected = 1;
        $link = $categoriesList->buildCategoryLink(101);
        $this->assertEquals('index.php?DepartmentID=1&CategoryID=101', $link);
    }

}
