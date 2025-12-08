<?php

/**
 * Smarty plugin: function.load_cart_summary.php
 *
 * Loads cart summary data for sidebar display.
 * Shows total items and total amount in the shopping cart.
 */

use Hatshop\Core\Config;
use Hatshop\Core\ShoppingCart;

/**
 * Cart summary data class.
 */
class CartSummary
{
    /** @var string Total amount in cart */
    public string $mTotalAmount = '0.00';

    /** @var array<int, array<string, mixed>> Cart items */
    public array $mItems = [];

    /** @var bool Whether cart is empty */
    public bool $mEmptyCart = true;

    public function __construct()
    {
        $this->init();
    }

    /**
     * Initialize cart summary data.
     */
    private function init(): void
    {
        // Get total amount
        $this->mTotalAmount = ShoppingCart::getTotalAmount();

        // Get shopping cart products
        $this->mItems = ShoppingCart::getCartProducts(Config::get('cart_get_products'));

        // Check if cart is empty
        $this->mEmptyCart = empty($this->mItems);
    }
}

/**
 * Smarty function to load cart summary.
 *
 * @param array<string, mixed> $params Parameters passed from template
 * @param Smarty\Template $smarty The Smarty template instance
 * @return string|null Rendered output or null if assigned to variable
 */
function smarty_function_load_cart_summary(array $params, \Smarty\Template $smarty): ?string
{
    $cartSummary = new CartSummary();

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $cartSummary);
        return null;
    }

    return '';
}
