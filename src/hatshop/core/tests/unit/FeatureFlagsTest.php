<?php

namespace Hatshop\Core\Tests\Unit;

use Hatshop\Core\FeatureFlags;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for FeatureFlags class.
 */
class FeatureFlagsTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset feature flags before each test
        FeatureFlags::reset();
    }

    protected function tearDown(): void
    {
        // Clean up any environment variables set during tests
        putenv('HATSHOP_FEATURE_DEPARTMENTS');
        putenv('HATSHOP_FEATURE_SEARCH');
        putenv('HATSHOP_FEATURE_PAYPAL');
        FeatureFlags::reset();
    }

    public function testDefaultDepartmentsEnabled(): void
    {
        $this->assertTrue(FeatureFlags::isEnabled(FeatureFlags::FEATURE_DEPARTMENTS));
    }

    public function testDefaultCategoriesEnabled(): void
    {
        $this->assertTrue(FeatureFlags::isEnabled(FeatureFlags::FEATURE_CATEGORIES));
    }

    public function testDefaultPaypalDisabled(): void
    {
        $this->assertFalse(FeatureFlags::isEnabled(FeatureFlags::FEATURE_PAYPAL));
    }

    public function testDefaultShoppingCartDisabled(): void
    {
        $this->assertFalse(FeatureFlags::isEnabled(FeatureFlags::FEATURE_SHOPPING_CART));
    }

    public function testEnableFeature(): void
    {
        $this->assertFalse(FeatureFlags::isEnabled(FeatureFlags::FEATURE_PAYPAL));
        
        FeatureFlags::enable(FeatureFlags::FEATURE_PAYPAL);
        
        $this->assertTrue(FeatureFlags::isEnabled(FeatureFlags::FEATURE_PAYPAL));
    }

    public function testDisableFeature(): void
    {
        $this->assertTrue(FeatureFlags::isEnabled(FeatureFlags::FEATURE_DEPARTMENTS));
        
        FeatureFlags::disable(FeatureFlags::FEATURE_DEPARTMENTS);
        
        $this->assertFalse(FeatureFlags::isEnabled(FeatureFlags::FEATURE_DEPARTMENTS));
    }

    public function testReset(): void
    {
        // Enable a disabled feature
        FeatureFlags::enable(FeatureFlags::FEATURE_PAYPAL);
        $this->assertTrue(FeatureFlags::isEnabled(FeatureFlags::FEATURE_PAYPAL));
        
        // Reset should clear cached values but reloading will use defaults
        FeatureFlags::reset();
        
        // After reset, default is disabled
        $this->assertFalse(FeatureFlags::isEnabled(FeatureFlags::FEATURE_PAYPAL));
    }

    public function testGetAllFlags(): void
    {
        $flags = FeatureFlags::getAllFlags();
        
        $this->assertIsArray($flags);
        $this->assertArrayHasKey(FeatureFlags::FEATURE_DEPARTMENTS, $flags);
        $this->assertArrayHasKey(FeatureFlags::FEATURE_CATEGORIES, $flags);
        $this->assertArrayHasKey(FeatureFlags::FEATURE_PAYPAL, $flags);
        $this->assertArrayHasKey(FeatureFlags::FEATURE_SHOPPING_CART, $flags);
    }

    public function testEnvironmentVariableOverride(): void
    {
        // Set environment variable to enable paypal
        putenv('HATSHOP_FEATURE_PAYPAL=true');
        FeatureFlags::reset();
        
        $this->assertTrue(FeatureFlags::isEnabled(FeatureFlags::FEATURE_PAYPAL));
    }

    public function testEnvironmentVariableDisable(): void
    {
        // Set environment variable to disable departments
        putenv('HATSHOP_FEATURE_DEPARTMENTS=false');
        FeatureFlags::reset();
        
        $this->assertFalse(FeatureFlags::isEnabled(FeatureFlags::FEATURE_DEPARTMENTS));
    }

    public function testSetChapterLevelTwo(): void
    {
        FeatureFlags::setChapterLevel(2);
        
        $this->assertTrue(FeatureFlags::isEnabled(FeatureFlags::FEATURE_DEPARTMENTS));
        $this->assertFalse(FeatureFlags::isEnabled(FeatureFlags::FEATURE_CATEGORIES));
        $this->assertFalse(FeatureFlags::isEnabled(FeatureFlags::FEATURE_SEARCH));
    }

    public function testSetChapterLevelFive(): void
    {
        FeatureFlags::setChapterLevel(5);
        
        $this->assertTrue(FeatureFlags::isEnabled(FeatureFlags::FEATURE_DEPARTMENTS));
        $this->assertTrue(FeatureFlags::isEnabled(FeatureFlags::FEATURE_CATEGORIES));
        $this->assertTrue(FeatureFlags::isEnabled(FeatureFlags::FEATURE_PRODUCTS));
        $this->assertTrue(FeatureFlags::isEnabled(FeatureFlags::FEATURE_SEARCH));
        $this->assertFalse(FeatureFlags::isEnabled(FeatureFlags::FEATURE_PAYPAL));
    }

    public function testSetChapterLevelEight(): void
    {
        FeatureFlags::setChapterLevel(8);
        
        $this->assertTrue(FeatureFlags::isEnabled(FeatureFlags::FEATURE_SHOPPING_CART));
        $this->assertFalse(FeatureFlags::isEnabled(FeatureFlags::FEATURE_CUSTOMER_ORDERS));
    }

    public function testSetChapterLevelTwelve(): void
    {
        FeatureFlags::setChapterLevel(12);
        
        $this->assertTrue(FeatureFlags::isEnabled(FeatureFlags::FEATURE_SHOPPING_CART));
        $this->assertTrue(FeatureFlags::isEnabled(FeatureFlags::FEATURE_CUSTOMER_ORDERS));
        $this->assertTrue(FeatureFlags::isEnabled(FeatureFlags::FEATURE_PRODUCT_RECOMMENDATIONS));
        $this->assertTrue(FeatureFlags::isEnabled(FeatureFlags::FEATURE_CUSTOMER_DETAILS));
        $this->assertTrue(FeatureFlags::isEnabled(FeatureFlags::FEATURE_ORDER_STORAGE));
    }

    public function testFeatureConstantsExist(): void
    {
        $this->assertEquals('departments', FeatureFlags::FEATURE_DEPARTMENTS);
        $this->assertEquals('categories', FeatureFlags::FEATURE_CATEGORIES);
        $this->assertEquals('products', FeatureFlags::FEATURE_PRODUCTS);
        $this->assertEquals('product_details', FeatureFlags::FEATURE_PRODUCT_DETAILS);
        $this->assertEquals('pagination', FeatureFlags::FEATURE_PAGINATION);
        $this->assertEquals('search', FeatureFlags::FEATURE_SEARCH);
        $this->assertEquals('paypal', FeatureFlags::FEATURE_PAYPAL);
        $this->assertEquals('catalog_admin', FeatureFlags::FEATURE_CATALOG_ADMIN);
        $this->assertEquals('shopping_cart', FeatureFlags::FEATURE_SHOPPING_CART);
        $this->assertEquals('customer_orders', FeatureFlags::FEATURE_CUSTOMER_ORDERS);
        $this->assertEquals('product_recommendations', FeatureFlags::FEATURE_PRODUCT_RECOMMENDATIONS);
        $this->assertEquals('customer_details', FeatureFlags::FEATURE_CUSTOMER_DETAILS);
        $this->assertEquals('order_storage', FeatureFlags::FEATURE_ORDER_STORAGE);
    }
}
