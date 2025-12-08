<?php

namespace Hatshop\Core;

/**
 * Feature flags system for enabling/disabling chapter features.
 *
 * Features are controlled via environment variables prefixed with HATSHOP_FEATURE_
 * Example: HATSHOP_FEATURE_SEARCH=true enables the search feature.
 */
class FeatureFlags
{
    // Chapter 2: Basic foundations
    public const FEATURE_DEPARTMENTS = 'departments';

    // Chapter 3: Product Catalog Part I
    public const FEATURE_CATEGORIES = 'categories';

    // Chapter 4: Product Catalog Part II
    public const FEATURE_PRODUCTS = 'products';
    public const FEATURE_PRODUCT_DETAILS = 'product_details';
    public const FEATURE_PAGINATION = 'pagination';

    // Chapter 5: Search
    public const FEATURE_SEARCH = 'search';

    // Chapter 6: PayPal Payments
    public const FEATURE_PAYPAL = 'paypal';

    // Chapter 7: Catalog Administration
    public const FEATURE_CATALOG_ADMIN = 'catalog_admin';

    // Future chapters
    public const FEATURE_SHOPPING_CART = 'shopping_cart';
    public const FEATURE_CUSTOMER_ORDERS = 'customer_orders';
    public const FEATURE_PRODUCT_RECOMMENDATIONS = 'product_recommendations';
    public const FEATURE_CUSTOMER_DETAILS = 'customer_details';
    public const FEATURE_ORDER_PIPELINE = 'order_pipeline';
    public const FEATURE_CREDIT_CARD = 'credit_card';
    public const FEATURE_PRODUCT_REVIEWS = 'product_reviews';

    /** @var array<string, bool> Cache of feature flag states */
    private static array $flags = [];

    /** @var array<string, bool> Default states for features */
    private static array $defaults = [
        self::FEATURE_DEPARTMENTS => true,
        self::FEATURE_CATEGORIES => true,
        self::FEATURE_PRODUCTS => true,
        self::FEATURE_PRODUCT_DETAILS => true,
        self::FEATURE_PAGINATION => true,
        self::FEATURE_SEARCH => true,
        self::FEATURE_PAYPAL => false,
        self::FEATURE_CATALOG_ADMIN => false,
        self::FEATURE_SHOPPING_CART => false,
        self::FEATURE_CUSTOMER_ORDERS => false,
        self::FEATURE_PRODUCT_RECOMMENDATIONS => false,
        self::FEATURE_CUSTOMER_DETAILS => false,
        self::FEATURE_ORDER_PIPELINE => false,
        self::FEATURE_CREDIT_CARD => false,
        self::FEATURE_PRODUCT_REVIEWS => false,
    ];

    /**
     * Check if a feature is enabled.
     *
     * @param string $feature The feature constant to check
     * @return bool True if the feature is enabled, false otherwise
     */
    public static function isEnabled(string $feature): bool
    {
        if (!isset(self::$flags[$feature])) {
            self::$flags[$feature] = self::loadFlag($feature);
        }
        return self::$flags[$feature];
    }

    /**
     * Load a feature flag from environment variable.
     *
     * @param string $feature The feature name
     * @return bool The flag value
     */
    private static function loadFlag(string $feature): bool
    {
        $envKey = 'HATSHOP_FEATURE_' . strtoupper($feature);
        $envValue = getenv($envKey);

        if ($envValue === false) {
            return self::$defaults[$feature] ?? false;
        }

        return filter_var($envValue, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Enable a feature programmatically (useful for testing).
     *
     * @param string $feature The feature to enable
     */
    public static function enable(string $feature): void
    {
        self::$flags[$feature] = true;
    }

    /**
     * Disable a feature programmatically (useful for testing).
     *
     * @param string $feature The feature to disable
     */
    public static function disable(string $feature): void
    {
        self::$flags[$feature] = false;
    }

    /**
     * Reset all flags to default state (useful for testing).
     */
    public static function reset(): void
    {
        self::$flags = [];
    }

    /**
     * Get all feature flags and their current states.
     *
     * @return array<string, bool> Array of feature names and their states
     */
    public static function getAllFlags(): array
    {
        $allFeatures = array_keys(self::$defaults);
        $result = [];

        foreach ($allFeatures as $feature) {
            $result[$feature] = self::isEnabled($feature);
        }

        return $result;
    }

    /**
     * Set the chapter level - enables all features up to and including the specified chapter.
     *
     * @param int $chapter Chapter number (2-17)
     */
    public static function setChapterLevel(int $chapter): void
    {
        // Map chapters to features
        $chapterFeatures = [
            2 => [self::FEATURE_DEPARTMENTS],
            3 => [self::FEATURE_DEPARTMENTS, self::FEATURE_CATEGORIES],
            4 => [self::FEATURE_DEPARTMENTS, self::FEATURE_CATEGORIES, self::FEATURE_PRODUCTS,
                  self::FEATURE_PRODUCT_DETAILS, self::FEATURE_PAGINATION],
            5 => [self::FEATURE_DEPARTMENTS, self::FEATURE_CATEGORIES, self::FEATURE_PRODUCTS,
                  self::FEATURE_PRODUCT_DETAILS, self::FEATURE_PAGINATION, self::FEATURE_SEARCH],
            6 => [self::FEATURE_DEPARTMENTS, self::FEATURE_CATEGORIES, self::FEATURE_PRODUCTS,
                  self::FEATURE_PRODUCT_DETAILS, self::FEATURE_PAGINATION, self::FEATURE_SEARCH,
                  self::FEATURE_PAYPAL],
            7 => [self::FEATURE_DEPARTMENTS, self::FEATURE_CATEGORIES, self::FEATURE_PRODUCTS,
                  self::FEATURE_PRODUCT_DETAILS, self::FEATURE_PAGINATION, self::FEATURE_SEARCH,
                  self::FEATURE_PAYPAL, self::FEATURE_CATALOG_ADMIN],
            8 => [self::FEATURE_DEPARTMENTS, self::FEATURE_CATEGORIES, self::FEATURE_PRODUCTS,
                  self::FEATURE_PRODUCT_DETAILS, self::FEATURE_PAGINATION, self::FEATURE_SEARCH,
                  self::FEATURE_PAYPAL, self::FEATURE_CATALOG_ADMIN, self::FEATURE_SHOPPING_CART],
            9 => [self::FEATURE_DEPARTMENTS, self::FEATURE_CATEGORIES, self::FEATURE_PRODUCTS,
                  self::FEATURE_PRODUCT_DETAILS, self::FEATURE_PAGINATION, self::FEATURE_SEARCH,
                  self::FEATURE_PAYPAL, self::FEATURE_CATALOG_ADMIN, self::FEATURE_SHOPPING_CART,
                  self::FEATURE_CUSTOMER_ORDERS],
        ];

        // Disable all features first
        foreach (array_keys(self::$defaults) as $feature) {
            self::disable($feature);
        }

        // Enable features for the specified chapter level
        if (isset($chapterFeatures[$chapter])) {
            foreach ($chapterFeatures[$chapter] as $feature) {
                self::enable($feature);
            }
        }
    }
}
