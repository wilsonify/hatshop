<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../presentation/smarty_plugins/05.function.load_products_list.php';
use PHPUnit\Framework\TestCase;
use Smarty\Smarty;

class p05_SmartyFunctionLoadProductsListTest extends TestCase
{
    protected function setUp(): void
    {
        $_GET = [];
    }

    public function testLoadProductsListWithDepartmentID()
    {
        $_GET['DepartmentID'] = 1;
        $params = ['assign' => 'products_list'];
        $smarty = new Smarty();
        smarty_function_load_products_list($params, $smarty);
        $productsList = $smarty->getTemplateVars('products_list');

        $this->assertInstanceOf(ProductsList::class, $productsList);
        $this->assertNotEmpty($productsList->mProducts);
    }

    public function testLoadProductsListWithCategoryID()
    {
        $_GET['CategoryID'] = 2;
        $params = ['assign' => 'products_list'];
        $smarty = new Smarty();
        smarty_function_load_products_list($params, $smarty);
        $productsList = $smarty->getTemplateVars('products_list');

        $this->assertInstanceOf(ProductsList::class, $productsList);
        $this->assertNotEmpty($productsList->mProducts);
    }

    public function testLoadProductsListWithoutIDs()
    {
        $params = ['assign' => 'products_list'];
        $smarty = new Smarty();
        smarty_function_load_products_list($params, $smarty);
        $productsList = $smarty->getTemplateVars('products_list');

        $this->assertInstanceOf(ProductsList::class, $productsList);
        $this->assertNotEmpty($productsList->mProducts);
    }
}