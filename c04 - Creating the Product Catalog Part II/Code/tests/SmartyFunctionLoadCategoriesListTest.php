<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../presentation/smarty_plugins/function.load_categories_list.php';
use PHPUnit\Framework\TestCase;
use Smarty\Smarty;

class SmartyFunctionLoadCategoriesListTest extends TestCase
{
    protected function setUp(): void
    {

    }

//     public function testConstructorValidQuery()
//     {
//         $categoriesList = new CategoriesList();
//         $categoriesList['DepartmentID'] = 1;
//         $categoriesList['CategoryID'] = 2;
//         $this->assertEquals(1, $categoriesList->mDepartmentSelected);
//         $this->assertEquals(2, $categoriesList->mCategorySelected);
//     }
//
//     public function testInitWithCategories()
//     {
//         $_GET['DepartmentID'] = 1;
//         $mockCategories = [
//             ['category_id' => 101, 'name' => 'Category 101'],
//             ['category_id' => 102, 'name' => 'Category 102']
//         ];
//         Catalog::shouldReceive('GetCategoriesInDepartment')->with(1)->andReturn($mockCategories);
//
//         $categoriesList = new CategoriesList();
//         $categoriesList->init();
//
//         $this->assertCount(2, $categoriesList->mCategories);
//         $this->assertEquals(
//             'index.php?DepartmentID=1&CategoryID=101',
//             $categoriesList->mCategories[0]['link']
//         );
//     }
}

