<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../presentation/smarty_plugins/function.load_categories_list.php';
use PHPUnit\Framework\TestCase;
use Smarty\Smarty;

class SmartyFunctionLoadCategoriesListTest extends TestCase
{
    protected function setUp(): void
    {
        $_GET = [];
    }

public function testConstructorValidQuery()
{
    // Mock the $_GET superglobal
    $_GET['DepartmentID'] = 1;
    $_GET['CategoryID'] = 2;

    // Create an instance of CategoriesList
    $categoriesList = new CategoriesList();

    // Assert that the values are set correctly
    $this->assertEquals(1, $categoriesList->mDepartmentSelected);
    $this->assertEquals(2, $categoriesList->mCategorySelected);

    // Clean up $_GET to avoid side effects
    unset($_GET['DepartmentID'], $_GET['CategoryID']);
}

public function testInitWithCategories()
{
    $_GET['DepartmentID'] = 1;

    // Predefined test data
    $mockCategories = [
        ['category_id' => 101, 'name' => 'Category 101'],
        ['category_id' => 102, 'name' => 'Category 102'],
    ];

    // Create an instance of CategoriesList with test data
    $categoriesList = new CategoriesList(fn($departmentId) => $mockCategories);
    $categoriesList->initialize();

    // Assertions
    $this->assertCount(2, $categoriesList->mCategories);
    $this->assertEquals(
        'index.php?DepartmentID=1&CategoryID=101',
        $categoriesList->mCategories[0]['link']
    );

    // Clean up $_GET
    unset($_GET['DepartmentID']);
}
}

