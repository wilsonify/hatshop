<?php

namespace Hatshop\Core\Tests\Integration;

use PHPUnit\Framework\TestCase;

/**
 * Integration tests that verify the production deployment is working correctly.
 *
 * These tests make HTTP requests to the deployed application to ensure
 * that the code is actually running without fatal errors.
 */
class ProductionEndpointTest extends TestCase
{
    private const PROD_BASE_URL = 'https://hatshop.renewed-renaissance.com/prod';

    /**
     * Test that the main index page loads without fatal errors.
     */
    public function testIndexPageLoads(): void
    {
        $response = $this->fetchUrl(self::PROD_BASE_URL . '/index.php');

        $this->assertNotFalse($response, 'Should be able to fetch index page');
        $this->assertStringNotContainsString(
            'Fatal error',
            $response,
            'Index page should not contain fatal errors'
        );
        $this->assertStringNotContainsString(
            'Uncaught Error',
            $response,
            'Index page should not contain uncaught errors'
        );
        $this->assertStringContainsString(
            'HatShop',
            $response,
            'Index page should contain HatShop branding'
        );
    }

    /**
     * Test that product detail page loads without fatal errors.
     *
     * This specifically tests the fix for Catalog::getProductDetails()
     * which should be CatalogProducts::getProductDetails().
     */
    public function testProductDetailPageLoads(): void
    {
        $response = $this->fetchUrl(self::PROD_BASE_URL . '/index.php?ProductID=7');

        $this->assertNotFalse($response, 'Should be able to fetch product detail page');
        $this->assertStringNotContainsString(
            'Fatal error',
            $response,
            'Product detail page should not contain fatal errors'
        );
        $this->assertStringNotContainsString(
            'Call to undefined method Hatshop\Core\Catalog::getProductDetails',
            $response,
            'Product page should not have Catalog::getProductDetails error - method moved to CatalogProducts'
        );
        $this->assertStringNotContainsString(
            'Uncaught Error',
            $response,
            'Product detail page should not contain uncaught errors'
        );
    }

    /**
     * Test that department page loads without fatal errors.
     */
    public function testDepartmentPageLoads(): void
    {
        $response = $this->fetchUrl(self::PROD_BASE_URL . '/index.php?DepartmentID=1');

        $this->assertNotFalse($response, 'Should be able to fetch department page');
        $this->assertStringNotContainsString(
            'Fatal error',
            $response,
            'Department page should not contain fatal errors'
        );
        $this->assertStringNotContainsString(
            'Uncaught Error',
            $response,
            'Department page should not contain uncaught errors'
        );
    }

    /**
     * Test that category page loads without fatal errors.
     */
    public function testCategoryPageLoads(): void
    {
        $response = $this->fetchUrl(self::PROD_BASE_URL . '/index.php?DepartmentID=1&CategoryID=1');

        $this->assertNotFalse($response, 'Should be able to fetch category page');
        $this->assertStringNotContainsString(
            'Fatal error',
            $response,
            'Category page should not contain fatal errors'
        );
        $this->assertStringNotContainsString(
            'Uncaught Error',
            $response,
            'Category page should not contain uncaught errors'
        );
    }

    /**
     * Test that search functionality works without fatal errors.
     */
    public function testSearchPageLoads(): void
    {
        $response = $this->fetchUrl(self::PROD_BASE_URL . '/index.php?SearchResults=hat');

        $this->assertNotFalse($response, 'Should be able to fetch search results page');
        $this->assertStringNotContainsString(
            'Fatal error',
            $response,
            'Search page should not contain fatal errors'
        );
        $this->assertStringNotContainsString(
            'Call to undefined method Hatshop\Core\Catalog::search',
            $response,
            'Search page should not have Catalog::search error - method moved to CatalogSearch'
        );
        $this->assertStringNotContainsString(
            'Uncaught Error',
            $response,
            'Search page should not contain uncaught errors'
        );
    }

    /**
     * Helper method to fetch URL content.
     *
     * @param string $url The URL to fetch
     * @return string|false The response body or false on failure
     */
    private function fetchUrl(string $url): string|false
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'ignore_errors' => true, // Get response body even on HTTP errors
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        return @file_get_contents($url, false, $context);
    }
}
