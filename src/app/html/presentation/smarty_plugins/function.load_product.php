<?php

use Hatshop\Core\Catalog;
use Hatshop\Core\Config;
use Hatshop\Core\FeatureFlags;

// Plugin functions inside plugin files must be named: smarty_type_name
function smarty_function_load_product($params, $smarty)
{
    // Create Product object
    $product = new Product();
    $product->init();

    // Assign template variable
    $smarty->assign($params['assign'], $product);

    // Assign PayPal configuration if feature is enabled
    if (FeatureFlags::isEnabled(FeatureFlags::FEATURE_PAYPAL)) {
        $smarty->assign('paypal_url', Config::get('paypal_url'));
        $smarty->assign('paypal_email', Config::get('paypal_email'));
        $smarty->assign('paypal_return_url', Config::get('paypal_return_url'));
        $smarty->assign('paypal_cancel_url', Config::get('paypal_cancel_url'));
        $smarty->assign('paypal_currency_code', Config::get('paypal_currency_code'));
    }
}

// Handles product details
class Product
{
    // Public variables to be used in Smarty template
    public $mProduct;
    public $mPageLink = 'index.php';
    public $mAddToCartLink = '';

    // Private stuff
    private $_mProductId;

    // Class constructor
    public function __construct()
    {
        // Variable initialization
        if (isset($_GET['ProductID'])) {
            $this->_mProductId = (int)$_GET['ProductID'];
        } else {
            trigger_error('ProductID required in product.php');
        }
    }

    public function init()
    {
        // Get product details from business tier
        $this->mProduct = Catalog::getProductDetails($this->_mProductId);

        if (isset($_SESSION['page_link'])) {
            $this->mPageLink = $_SESSION['page_link'];
        }

        // Build add to cart link if shopping cart is enabled
        if (FeatureFlags::isEnabled(FeatureFlags::FEATURE_SHOPPING_CART)) {
            $this->mAddToCartLink = 'index.php?CartAction=' . Config::get('cart_action_add') .
                                    '&ProductID=' . $this->_mProductId;
        }
    }
}
