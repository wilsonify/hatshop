<?php

namespace Hatshop\Core\Presentation;

use Hatshop\Core\Catalog;
use Smarty\Smarty;

/**
 * Smarty plugin for loading product details.
 */
class ProductPlugin
{
    /**
     * Execute the plugin.
     *
     * @param array $params Plugin parameters
     * @param Smarty $smarty Smarty instance
     */
    public static function execute(array $params, Smarty $smarty): void
    {
        $product = new Product();
        $smarty->assign($params['assign'], $product);
    }
}

/**
 * Data object for product details.
 */
class Product
{
    /** @var int|null Product ID */
    public ?int $mProductId = null;

    /** @var string Product name */
    public string $mName = '';

    /** @var string Product description */
    public string $mDescription = '';

    /** @var string Product price */
    public string $mPrice = '';

    /** @var string Product image */
    public string $mImage = '';

    /** @var string Product thumbnail */
    public string $mThumbnail = '';

    /** @var string Discounted price */
    public string $mDiscountedPrice = '';

    public function __construct()
    {
        if (isset($_GET['ProductID'])) {
            $this->mProductId = (int) $_GET['ProductID'];

            $details = Catalog::getProductDetails($this->mProductId);

            if ($details) {
                $this->mName = $details['name'] ?? '';
                $this->mDescription = $details['description'] ?? '';
                $this->mPrice = $details['price'] ?? '';
                $this->mImage = $details['image'] ?? '';
                $this->mThumbnail = $details['thumbnail'] ?? '';
                $this->mDiscountedPrice = $details['discounted_price'] ?? '';
            }
        }
    }
}
