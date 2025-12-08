<?php

/**
 * Smarty plugin: function.load_cart_details.php
 *
 * Loads cart details for the full cart page.
 * Handles cart actions (add, remove, update, save for later, move to cart).
 */

use Hatshop\Core\Config;
use Hatshop\Core\ShoppingCart;

/**
 * Cart details data class.
 *
 * Processes cart actions and provides cart data for display.
 */
class CartDetails
{
    // URL constants
    private const CART_URL_BASE = 'index.php?CartAction=';
    private const PRODUCT_ID_PARAM = '&ProductID=';

    /** @var array<int, array<string, mixed>> Active cart products */
    public array $mCartProducts = [];

    /** @var array<int, array<string, mixed>> Saved for later products */
    public array $mSavedCartProducts = [];

    /** @var string Total amount in cart */
    public string $mTotalAmount = '0.00';

    /** @var bool Whether cart is empty */
    public bool $mIsCartNowEmpty = true;

    /** @var bool Whether saved items list is empty */
    public bool $mIsCartLaterEmpty = true;

    /** @var string Form action URL for cart updates */
    public string $mCartDetailsTarget = '';

    /** @var string Link to continue shopping */
    public string $mContinueShoppingLink = '';

    /** @var int|null The current cart action */
    private ?int $cartAction = null;

    public function __construct()
    {
        $this->init();
    }

    /**
     * Initialize cart details and process actions.
     */
    private function init(): void
    {
        // Validate and process CartAction
        if (isset($_GET['CartAction'])) {
            $this->cartAction = (int)$_GET['CartAction'];
        } else {
            trigger_error('CartAction not set', E_USER_ERROR);
            return;
        }

        // Process product-related actions (add, remove, save for later, move to cart)
        if ($this->isProductAction()) {
            $this->processProductAction();
        }

        // Set form action target
        $this->mCartDetailsTarget = self::CART_URL_BASE . Config::get('cart_action_update');

        // Process update action
        $this->processUpdateAction();

        // Get cart products
        $this->mCartProducts = ShoppingCart::getCartProducts(Config::get('cart_get_products'));
        $this->mSavedCartProducts = ShoppingCart::getCartProducts(Config::get('cart_get_saved_products'));

        // Build links for products
        $this->buildProductLinks($this->mCartProducts, 'save_for_later_link', 'remove_link');
        $this->buildProductLinks($this->mSavedCartProducts, 'move_to_cart_link', 'remove_link');

        // Get cart totals
        $this->mTotalAmount = ShoppingCart::getTotalAmount();
        $this->mIsCartNowEmpty = empty($this->mCartProducts);
        $this->mIsCartLaterEmpty = empty($this->mSavedCartProducts);

        // Set continue shopping link
        $this->mContinueShoppingLink = $_SESSION['page_link'] ?? 'index.php';
    }

    /**
     * Check if the action requires a product ID.
     */
    private function isProductAction(): bool
    {
        $productActions = [
            Config::get('cart_action_add'),
            Config::get('cart_action_remove'),
            Config::get('cart_action_save_for_later'),
            Config::get('cart_action_move_to_cart'),
        ];

        return in_array($this->cartAction, $productActions, true);
    }

    /**
     * Process product-related cart actions.
     */
    private function processProductAction(): void
    {
        if (!isset($_GET['ProductID'])) {
            trigger_error('ProductID not set', E_USER_ERROR);
            return;
        }

        $productId = (int)$_GET['ProductID'];

        switch ($this->cartAction) {
            case Config::get('cart_action_add'):
                ShoppingCart::addProduct($productId);
                break;
            case Config::get('cart_action_remove'):
                ShoppingCart::removeProduct($productId);
                break;
            case Config::get('cart_action_save_for_later'):
                ShoppingCart::saveProductForLater($productId);
                break;
            case Config::get('cart_action_move_to_cart'):
                ShoppingCart::moveProductToCart($productId);
                break;
            default:
                // Unknown action, do nothing
                break;
        }
    }

    /**
     * Process quantity update action.
     */
    private function processUpdateAction(): void
    {
        if ($this->cartAction === Config::get('cart_action_update')) {
            $productQuantities = [];

            foreach ($_POST as $key => $value) {
                // Check if the key matches itemQty_X pattern
                if (strpos($key, 'itemQty_') === 0) {
                    $productId = (int)substr($key, 8); // Extract ID from itemQty_X
                    $quantity = (int)$value;
                    $productQuantities[$productId] = $quantity;
                }
            }

            if (!empty($productQuantities)) {
                ShoppingCart::update($productQuantities);
            }
        }
    }

    /**
     * Build action links for cart products.
     *
     * @param array<int, array<string, mixed>> &$products Products array to modify
     * @param string $actionLink1 First action link key
     * @param string $actionLink2 Second action link key
     */
    private function buildProductLinks(array &$products, string $actionLink1, string $actionLink2): void
    {
        $actionConfig = [
            'save_for_later_link' => Config::get('cart_action_save_for_later'),
            'move_to_cart_link' => Config::get('cart_action_move_to_cart'),
            'remove_link' => Config::get('cart_action_remove'),
        ];

        foreach ($products as &$product) {
            $productId = $product['product_id'];
            $product[$actionLink1] = self::CART_URL_BASE . $actionConfig[$actionLink1] .
                                     self::PRODUCT_ID_PARAM . $productId;
            $product[$actionLink2] = self::CART_URL_BASE . $actionConfig[$actionLink2] .
                                     self::PRODUCT_ID_PARAM . $productId;
        }
    }
}

/**
 * Smarty function to load cart details.
 *
 * @param array<string, mixed> $params Parameters passed from template
 * @param Smarty\Template $smarty The Smarty template instance
 * @return string|null Rendered output or null if assigned to variable
 */
function smarty_function_load_cart_details(array $params, \Smarty\Template $smarty): ?string
{
    $cartDetails = new CartDetails();

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $cartDetails);
        return null;
    }

    return '';
}
