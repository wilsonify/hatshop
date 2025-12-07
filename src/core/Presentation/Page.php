<?php

namespace Hatshop\Core\Presentation;

use Smarty\Smarty;
use Hatshop\Core\Config;
use Hatshop\Core\FeatureFlags;

/**
 * Base Page class that extends Smarty.
 *
 * This class handles template rendering with feature flag awareness.
 */
class Page extends Smarty
{
    /**
     * Constructor - sets up Smarty directories and registers plugins.
     */
    public function __construct()
    {
        parent::__construct();

        $this->template_dir = Config::get('template_dir');
        $this->compile_dir = Config::get('compile_dir');
        $this->config_dir = Config::get('config_dir');

        $this->registerCorePlugins();
        $this->registerFeaturePlugins();
    }

    /**
     * Register core Smarty plugins that are always available.
     */
    private function registerCorePlugins(): void
    {
        // Modifier for link preparation
        $this->registerPlugin('modifier', 'prepare_link', [Link::class, 'prepareLink']);
    }

    /**
     * Register feature-specific Smarty plugins based on feature flags.
     */
    private function registerFeaturePlugins(): void
    {
        // Chapter 2: Departments
        if (FeatureFlags::isEnabled(FeatureFlags::FEATURE_DEPARTMENTS)) {
            $this->registerPlugin('function', 'load_departments_list',
                [DepartmentsListPlugin::class, 'execute']);
            $this->registerPlugin('function', 'load_department',
                [DepartmentPlugin::class, 'execute']);
        }

        // Chapter 3: Categories
        if (FeatureFlags::isEnabled(FeatureFlags::FEATURE_CATEGORIES)) {
            $this->registerPlugin('function', 'load_categories_list',
                [CategoriesListPlugin::class, 'execute']);
        }

        // Chapter 4: Products
        if (FeatureFlags::isEnabled(FeatureFlags::FEATURE_PRODUCTS)) {
            $this->registerPlugin('function', 'load_products_list',
                [ProductsListPlugin::class, 'execute']);
        }

        if (FeatureFlags::isEnabled(FeatureFlags::FEATURE_PRODUCT_DETAILS)) {
            $this->registerPlugin('function', 'load_product',
                [ProductPlugin::class, 'execute']);
        }

        // Chapter 5: Search
        if (FeatureFlags::isEnabled(FeatureFlags::FEATURE_SEARCH)) {
            $this->registerPlugin('function', 'load_search_box',
                [SearchBoxPlugin::class, 'execute']);
        }
    }

    /**
     * Check if a feature is enabled (for use in templates).
     *
     * @param string $feature Feature name
     * @return bool Whether the feature is enabled
     */
    public function isFeatureEnabled(string $feature): bool
    {
        return FeatureFlags::isEnabled($feature);
    }
}
