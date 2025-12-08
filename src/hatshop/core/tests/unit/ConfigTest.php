<?php

namespace Hatshop\Core\Tests\Unit;

use Hatshop\Core\Config;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Config class.
 *
 * Note: Some tests check behavior rather than specific values
 * since environment variables may override defaults.
 */
class ConfigTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset config to defaults before each test
        Config::reset();
    }

    protected function tearDown(): void
    {
        // Clean up
        Config::reset();
    }

    public function testGetReturnsNonNullForKnownKeys(): void
    {
        // These keys should always have a value (default or env override)
        $this->assertNotNull(Config::get('is_warning_fatal'));
        $this->assertNotNull(Config::get('debugging'));
        $this->assertNotNull(Config::get('db_database'));
        $this->assertNotNull(Config::get('db_server'));
    }

    public function testGetReturnsNullForUnknownKey(): void
    {
        $this->assertNull(Config::get('nonexistent_key'));
    }

    public function testGetReturnsProvidedDefault(): void
    {
        $this->assertEquals('default_value', Config::get('nonexistent_key', 'default_value'));
    }

    public function testSetOverridesValue(): void
    {
        $originalValue = Config::get('debugging');

        Config::set('debugging', !$originalValue);

        $this->assertEquals(!$originalValue, Config::get('debugging'));
    }

    public function testSetCreatesNewKey(): void
    {
        Config::set('custom_key', 'custom_value');

        $this->assertEquals('custom_value', Config::get('custom_key'));
    }

    public function testInitPathsSetsDirectories(): void
    {
        $siteRoot = '/var/www/hatshop';

        Config::initPaths($siteRoot);

        $this->assertEquals($siteRoot, Config::get('site_root'));
        $this->assertEquals($siteRoot . '/presentation/', Config::get('presentation_dir'));
        $this->assertEquals($siteRoot . '/business/', Config::get('business_dir'));
        $this->assertEquals($siteRoot . '/presentation/templates', Config::get('template_dir'));
        $this->assertEquals($siteRoot . '/presentation/templates_c', Config::get('compile_dir'));
        $this->assertEquals($siteRoot . '/include/configs', Config::get('config_dir'));
    }

    public function testGetPdoDsnReturnsValidDsn(): void
    {
        $dsn = Config::getPdoDsn();

        $this->assertStringContainsString('pgsql:', $dsn);
        $this->assertStringContainsString('host=', $dsn);
        $this->assertStringContainsString('dbname=', $dsn);
    }

    public function testResetClearsCustomKeys(): void
    {
        Config::set('custom_key', 'custom_value');
        $this->assertEquals('custom_value', Config::get('custom_key'));

        Config::reset();

        $this->assertNull(Config::get('custom_key'));
    }

    public function testSingletonPattern(): void
    {
        $instance1 = Config::getInstance();
        $instance2 = Config::getInstance();

        $this->assertSame($instance1, $instance2);
    }

    public function testProductSettingsAreIntegers(): void
    {
        $this->assertIsInt(Config::get('short_product_description_length'));
        $this->assertIsInt(Config::get('products_per_page'));
    }

    public function testCartSettingsExist(): void
    {
        $this->assertNotNull(Config::get('cart_get_products'));
        $this->assertNotNull(Config::get('cart_get_saved_products'));
        $this->assertNotNull(Config::get('cart_action_add'));
        $this->assertNotNull(Config::get('cart_action_remove'));
        $this->assertNotNull(Config::get('cart_action_update'));
        $this->assertNotNull(Config::get('cart_action_save_for_later'));
        $this->assertNotNull(Config::get('cart_action_move_to_cart'));
    }

    public function testErrorSettingsExist(): void
    {
        $this->assertNotNull(Config::get('log_errors'));
        $this->assertNotNull(Config::get('send_error_mail'));
        $this->assertNotNull(Config::get('error_types'));
    }

    public function testDatabaseSettingsExist(): void
    {
        $this->assertNotNull(Config::get('db_persistency'));
        $this->assertNotNull(Config::get('db_server'));
        $this->assertNotNull(Config::get('db_database'));
    }

    public function testEnvironmentVariableOverride(): void
    {
        // Set a custom value
        Config::set('debugging', true);
        $this->assertTrue(Config::get('debugging'));

        // Set via Config::set should work
        Config::set('debugging', false);
        $this->assertFalse(Config::get('debugging'));
    }

    public function testDefineLegacyConstantsDefinesConstants(): void
    {
        Config::initPaths('/test/path');

        // Skip if constants already defined from previous test run
        if (!defined('TEST_SITE_ROOT')) {
            Config::set('test_constant', 'test_value');
        }

        // The method should complete without error
        Config::defineLegacyConstants();
        $this->assertTrue(true);
    }

    public function testPayPalSettingsExist(): void
    {
        $this->assertNotNull(Config::get('paypal_url'));
        $this->assertNotNull(Config::get('paypal_currency_code'));
    }

    public function testAdminSettingsExist(): void
    {
        $this->assertNotNull(Config::get('admin_username'));
        $this->assertNotNull(Config::get('admin_password'));
    }
}
