<?php

namespace Hatshop\Core\Tests\Integration;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * Integration tests to verify Smarty plugins reference the correct class methods.
 *
 * These tests ensure that the refactored class structure is properly used:
 * - Catalog: getDepartments, getDepartmentDetails, getCategoriesInDepartment, getCategoryDetails
 * - CatalogProducts: getProductsInCategory, getProductsOnDepartmentDisplay, getProductsOnCatalogDisplay, getProductDetails
 * - CatalogSearch: search
 * - CatalogAdmin: getCategoryProducts, addProductToCategory, updateProduct, deleteProduct, etc.
 * - CategoryAdmin: getDepartmentCategories, addCategory, updateCategory, deleteCategory, getCategories
 * - DepartmentAdmin: getDepartmentsWithDescriptions, addDepartment, updateDepartment, deleteDepartment
 */
class SmartyPluginClassReferencesTest extends TestCase
{
    private const SMARTY_PLUGINS_PATH = __DIR__ . '/../../../app/html/presentation/smarty_plugins/';

    /**
     * @var array<string, string[]> Maps class names to their expected methods
     */
    private array $classMethodMap = [
        'Hatshop\Core\Catalog' => [
            'getDepartments',
            'getDepartmentDetails',
            'getCategoriesInDepartment',
            'getCategoryDetails',
        ],
        'Hatshop\Core\CatalogProducts' => [
            'getProductsInCategory',
            'getProductsOnDepartmentDisplay',
            'getProductsOnCatalogDisplay',
            'getProductDetails',
            'getRecommendations',
            'getProductReviews',
            'createProductReview',
        ],
        'Hatshop\Core\CatalogSearch' => [
            'search',
        ],
        'Hatshop\Core\CatalogAdmin' => [
            'getCategoryProducts',
            'addProductToCategory',
            'updateProduct',
            'deleteProduct',
            'removeProductFromCategory',
            'getProductInfo',
            'getCategoriesForProduct',
            'setProductDisplayOption',
            'assignProductToCategory',
            'moveProductToCategory',
            'setImage',
            'setThumbnail',
        ],
        'Hatshop\Core\CategoryAdmin' => [
            'getDepartmentCategories',
            'addCategory',
            'updateCategory',
            'deleteCategory',
            'getCategories',
        ],
        'Hatshop\Core\DepartmentAdmin' => [
            'getDepartmentsWithDescriptions',
            'addDepartment',
            'updateDepartment',
            'deleteDepartment',
        ],
    ];

    /**
     * Test that CatalogProducts class has all expected methods.
     */
    public function testCatalogProductsHasExpectedMethods(): void
    {
        $this->assertClassHasMethods(
            'Hatshop\Core\CatalogProducts',
            $this->classMethodMap['Hatshop\Core\CatalogProducts']
        );
    }

    /**
     * Test that CatalogSearch class has all expected methods.
     */
    public function testCatalogSearchHasExpectedMethods(): void
    {
        $this->assertClassHasMethods(
            'Hatshop\Core\CatalogSearch',
            $this->classMethodMap['Hatshop\Core\CatalogSearch']
        );
    }

    /**
     * Test that CatalogAdmin class has all expected methods.
     */
    public function testCatalogAdminHasExpectedMethods(): void
    {
        $this->assertClassHasMethods(
            'Hatshop\Core\CatalogAdmin',
            $this->classMethodMap['Hatshop\Core\CatalogAdmin']
        );
    }

    /**
     * Test that CategoryAdmin class has all expected methods.
     */
    public function testCategoryAdminHasExpectedMethods(): void
    {
        $this->assertClassHasMethods(
            'Hatshop\Core\CategoryAdmin',
            $this->classMethodMap['Hatshop\Core\CategoryAdmin']
        );
    }

    /**
     * Test that DepartmentAdmin class has all expected methods.
     */
    public function testDepartmentAdminHasExpectedMethods(): void
    {
        $this->assertClassHasMethods(
            'Hatshop\Core\DepartmentAdmin',
            $this->classMethodMap['Hatshop\Core\DepartmentAdmin']
        );
    }

    /**
     * Test that Catalog class has only its expected methods (not moved ones).
     */
    public function testCatalogHasOnlyBaselineMethods(): void
    {
        $this->assertClassHasMethods(
            'Hatshop\Core\Catalog',
            $this->classMethodMap['Hatshop\Core\Catalog']
        );

        // Verify methods that should NOT be in Catalog
        $movedMethods = [
            'getProductDetails',
            'getProductsInCategory',
            'search',
            'addDepartment',
            'getCategoryProducts',
        ];

        $reflection = new ReflectionClass('Hatshop\Core\Catalog');
        foreach ($movedMethods as $method) {
            $this->assertFalse(
                $reflection->hasMethod($method),
                "Method '$method' should not exist in Catalog class (moved to specialized class)"
            );
        }
    }

    /**
     * Test function.load_product.php uses CatalogProducts::getProductDetails.
     */
    public function testLoadProductPluginUsesCatalogProducts(): void
    {
        $content = $this->getPluginContent('function.load_product.php');

        $this->assertStringContainsString(
            'use Hatshop\Core\CatalogProducts;',
            $content,
            'function.load_product.php should import CatalogProducts'
        );

        $this->assertStringContainsString(
            'CatalogProducts::getProductDetails',
            $content,
            'function.load_product.php should call CatalogProducts::getProductDetails'
        );

        $this->assertStringNotContainsString(
            'Catalog::getProductDetails',
            $content,
            'function.load_product.php should NOT call Catalog::getProductDetails'
        );
    }

    /**
     * Test function.load_products_list.php uses correct classes.
     */
    public function testLoadProductsListPluginUsesCorrectClasses(): void
    {
        $content = $this->getPluginContent('function.load_products_list.php');

        $this->assertStringContainsString(
            'use Hatshop\Core\CatalogProducts;',
            $content,
            'function.load_products_list.php should import CatalogProducts'
        );

        $this->assertStringContainsString(
            'use Hatshop\Core\CatalogSearch;',
            $content,
            'function.load_products_list.php should import CatalogSearch'
        );

        // Verify correct method calls
        $this->assertStringContainsString(
            'CatalogProducts::getProductsInCategory',
            $content,
            'Should call CatalogProducts::getProductsInCategory'
        );

        $this->assertStringContainsString(
            'CatalogProducts::getProductsOnDepartmentDisplay',
            $content,
            'Should call CatalogProducts::getProductsOnDepartmentDisplay'
        );

        $this->assertStringContainsString(
            'CatalogProducts::getProductsOnCatalogDisplay',
            $content,
            'Should call CatalogProducts::getProductsOnCatalogDisplay'
        );

        $this->assertStringContainsString(
            'CatalogSearch::search',
            $content,
            'Should call CatalogSearch::search'
        );
    }

    /**
     * Test function.load_admin_departments.php uses DepartmentAdmin.
     */
    public function testLoadAdminDepartmentsPluginUsesDepartmentAdmin(): void
    {
        $content = $this->getPluginContent('function.load_admin_departments.php');

        $this->assertStringContainsString(
            'use Hatshop\Core\DepartmentAdmin;',
            $content,
            'function.load_admin_departments.php should import DepartmentAdmin'
        );

        $this->assertStringContainsString(
            'DepartmentAdmin::addDepartment',
            $content,
            'Should call DepartmentAdmin::addDepartment'
        );

        $this->assertStringContainsString(
            'DepartmentAdmin::updateDepartment',
            $content,
            'Should call DepartmentAdmin::updateDepartment'
        );

        $this->assertStringContainsString(
            'DepartmentAdmin::deleteDepartment',
            $content,
            'Should call DepartmentAdmin::deleteDepartment'
        );

        $this->assertStringContainsString(
            'DepartmentAdmin::getDepartmentsWithDescriptions',
            $content,
            'Should call DepartmentAdmin::getDepartmentsWithDescriptions'
        );
    }

    /**
     * Test function.load_admin_categories.php uses CategoryAdmin.
     */
    public function testLoadAdminCategoriesPluginUsesCategoryAdmin(): void
    {
        $content = $this->getPluginContent('function.load_admin_categories.php');

        $this->assertStringContainsString(
            'use Hatshop\Core\CategoryAdmin;',
            $content,
            'function.load_admin_categories.php should import CategoryAdmin'
        );

        $this->assertStringContainsString(
            'CategoryAdmin::addCategory',
            $content,
            'Should call CategoryAdmin::addCategory'
        );

        $this->assertStringContainsString(
            'CategoryAdmin::updateCategory',
            $content,
            'Should call CategoryAdmin::updateCategory'
        );

        $this->assertStringContainsString(
            'CategoryAdmin::deleteCategory',
            $content,
            'Should call CategoryAdmin::deleteCategory'
        );

        $this->assertStringContainsString(
            'CategoryAdmin::getDepartmentCategories',
            $content,
            'Should call CategoryAdmin::getDepartmentCategories'
        );

        // Should still use Catalog for getDepartmentDetails
        $this->assertStringContainsString(
            'Catalog::getDepartmentDetails',
            $content,
            'Should still call Catalog::getDepartmentDetails'
        );
    }

    /**
     * Test function.load_admin_products.php uses CatalogAdmin.
     */
    public function testLoadAdminProductsPluginUsesCatalogAdmin(): void
    {
        $content = $this->getPluginContent('function.load_admin_products.php');

        $this->assertStringContainsString(
            'use Hatshop\Core\CatalogAdmin;',
            $content,
            'function.load_admin_products.php should import CatalogAdmin'
        );

        $this->assertStringContainsString(
            'CatalogAdmin::addProductToCategory',
            $content,
            'Should call CatalogAdmin::addProductToCategory'
        );

        $this->assertStringContainsString(
            'CatalogAdmin::updateProduct',
            $content,
            'Should call CatalogAdmin::updateProduct'
        );

        $this->assertStringContainsString(
            'CatalogAdmin::getCategoryProducts',
            $content,
            'Should call CatalogAdmin::getCategoryProducts'
        );
    }

    /**
     * Test function.load_admin_product.php uses CatalogAdmin and CategoryAdmin.
     */
    public function testLoadAdminProductPluginUsesCorrectClasses(): void
    {
        $content = $this->getPluginContent('function.load_admin_product.php');

        $this->assertStringContainsString(
            'use Hatshop\Core\CatalogAdmin;',
            $content,
            'function.load_admin_product.php should import CatalogAdmin'
        );

        $this->assertStringContainsString(
            'use Hatshop\Core\CategoryAdmin;',
            $content,
            'function.load_admin_product.php should import CategoryAdmin'
        );

        // Verify CatalogAdmin method calls
        $catalogAdminMethods = [
            'setImage',
            'setThumbnail',
            'removeProductFromCategory',
            'setProductDisplayOption',
            'deleteProduct',
            'assignProductToCategory',
            'moveProductToCategory',
            'getProductInfo',
            'getCategoriesForProduct',
        ];

        foreach ($catalogAdminMethods as $method) {
            $this->assertStringContainsString(
                "CatalogAdmin::$method",
                $content,
                "Should call CatalogAdmin::$method"
            );
        }

        // Verify CategoryAdmin::getCategories
        $this->assertStringContainsString(
            'CategoryAdmin::getCategories',
            $content,
            'Should call CategoryAdmin::getCategories'
        );
    }

    /**
     * Test that no plugin incorrectly calls methods on Catalog that were moved.
     *
     * @dataProvider provideMovedMethodCalls
     */
    public function testNoIncorrectCatalogCalls(string $pluginFile, string $incorrectCall): void
    {
        if (!file_exists(self::SMARTY_PLUGINS_PATH . $pluginFile)) {
            $this->markTestSkipped("Plugin file $pluginFile does not exist");
        }

        $content = $this->getPluginContent($pluginFile);

        $this->assertStringNotContainsString(
            $incorrectCall,
            $content,
            "Plugin $pluginFile should not contain incorrect call: $incorrectCall"
        );
    }

    /**
     * Data provider for moved method calls that should not appear.
     *
     * @return array<array{string, string}>
     */
    public static function provideMovedMethodCalls(): array
    {
        return [
            ['function.load_product.php', 'Catalog::getProductDetails'],
            ['function.load_products_list.php', 'Catalog::getProductsInCategory'],
            ['function.load_products_list.php', 'Catalog::getProductsOnDepartmentDisplay'],
            ['function.load_products_list.php', 'Catalog::getProductsOnCatalogDisplay'],
            ['function.load_products_list.php', 'Catalog::search'],
            ['function.load_admin_departments.php', 'Catalog::addDepartment'],
            ['function.load_admin_departments.php', 'Catalog::updateDepartment'],
            ['function.load_admin_departments.php', 'Catalog::deleteDepartment'],
            ['function.load_admin_departments.php', 'Catalog::getDepartmentsWithDescriptions'],
            ['function.load_admin_categories.php', 'Catalog::addCategory'],
            ['function.load_admin_categories.php', 'Catalog::updateCategory'],
            ['function.load_admin_categories.php', 'Catalog::deleteCategory'],
            ['function.load_admin_categories.php', 'Catalog::getDepartmentCategories'],
            ['function.load_admin_products.php', 'Catalog::addProductToCategory'],
            ['function.load_admin_products.php', 'Catalog::updateProduct'],
            ['function.load_admin_products.php', 'Catalog::getCategoryProducts'],
            ['function.load_admin_product.php', 'Catalog::setImage'],
            ['function.load_admin_product.php', 'Catalog::setThumbnail'],
            ['function.load_admin_product.php', 'Catalog::removeProductFromCategory'],
            ['function.load_admin_product.php', 'Catalog::deleteProduct'],
            ['function.load_admin_product.php', 'Catalog::getProductInfo'],
            ['function.load_admin_product.php', 'Catalog::getCategories'],
        ];
    }

    /**
     * Helper to assert a class has all expected methods.
     *
     * @param string $className Fully qualified class name
     * @param string[] $expectedMethods List of method names
     */
    private function assertClassHasMethods(string $className, array $expectedMethods): void
    {
        $this->assertTrue(
            class_exists($className),
            "Class $className should exist"
        );

        $reflection = new ReflectionClass($className);

        foreach ($expectedMethods as $methodName) {
            $this->assertTrue(
                $reflection->hasMethod($methodName),
                "Class $className should have method $methodName"
            );

            $method = $reflection->getMethod($methodName);
            $this->assertTrue(
                $method->isPublic(),
                "Method $className::$methodName should be public"
            );
            $this->assertTrue(
                $method->isStatic(),
                "Method $className::$methodName should be static"
            );
        }
    }

    /**
     * Helper to get plugin file content.
     *
     * @param string $filename Plugin filename
     * @return string File content
     */
    private function getPluginContent(string $filename): string
    {
        $path = self::SMARTY_PLUGINS_PATH . $filename;
        $this->assertFileExists($path, "Plugin file $filename should exist");

        return file_get_contents($path);
    }
}
