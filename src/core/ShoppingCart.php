<?php

namespace Hatshop\Core;

/**
 * Shopping cart business tier class.
 *
 * Provides methods for shopping cart functionality including:
 * - Cart ID management (session + cookie based)
 * - Add/update/remove products from cart
 * - Save for later functionality
 * - Cart totals and product listing
 * - Admin functions for cleaning old carts
 */
class ShoppingCart
{
    // URL parameter constants
    private const CART_ACTION_PARAM = 'CartAction=';
    private const PRODUCT_ID_PARAM = '&ProductID=';
    /**
     * Set the cart ID in session and cookie.
     *
     * Generates a unique cart ID if none exists, stores it in both
     * session and a 7-day cookie for persistent cart functionality.
     */
    public static function setCartId(): void
    {
        // If the cart ID hasn't been already set
        if (!isset($_SESSION['cart_id'])) {
            // Check if the cart_id cookie exists
            if (isset($_COOKIE['cart_id'])) {
                // Get cart id from cookie
                $_SESSION['cart_id'] = $_COOKIE['cart_id'];
            } else {
                // Generate cart id and save it to cookie and session
                $_SESSION['cart_id'] = hash('sha512', random_bytes(32));
            }
            // Set cookie for 7 days
            setcookie('cart_id', $_SESSION['cart_id'], time() + 7 * 24 * 60 * 60);
        }
    }

    /**
     * Get the cart ID, generating one if necessary.
     *
     * @return string The cart ID
     */
    public static function getCartId(): string
    {
        if (!isset($_SESSION['cart_id'])) {
            self::setCartId();
        }
        return $_SESSION['cart_id'];
    }

    /**
     * Add a product to the shopping cart.
     *
     * If the product already exists in the cart, increments the quantity.
     *
     * @param int $productId The ID of the product to add
     */
    public static function addProduct(int $productId): void
    {
        $cartId = self::getCartId();

        // Create the SQL query
        $sql = 'SELECT shopping_cart_add_product(:cart_id, :product_id);';

        // Build the parameters array
        $params = [':cart_id' => $cartId, ':product_id' => $productId];

        // Prepare and execute the query
        $stmt = DatabaseHandler::prepare($sql);
        if ($stmt !== false) {
            DatabaseHandler::execute($stmt, $params);
        }
    }

    /**
     * Update product quantities in the cart.
     *
     * @param array<int, int> $productQuantities Associative array of product_id => quantity
     */
    public static function update(array $productQuantities): void
    {
        $cartId = self::getCartId();

        // Create the SQL query
        $sql = 'SELECT shopping_cart_update(:cart_id, :product_id, :quantity);';

        // Prepare the statement once
        $stmt = DatabaseHandler::prepare($sql);
        if ($stmt === false) {
            return;
        }

        // Update each product's quantity
        foreach ($productQuantities as $productId => $quantity) {
            // Build the parameters array
            $params = [':cart_id' => $cartId, ':product_id' => $productId, ':quantity' => $quantity];

            // Execute the query
            DatabaseHandler::execute($stmt, $params);
        }
    }

    /**
     * Remove a product from the shopping cart.
     *
     * @param int $productId The ID of the product to remove
     */
    public static function removeProduct(int $productId): void
    {
        $cartId = self::getCartId();

        // Create the SQL query
        $sql = 'SELECT shopping_cart_remove_product(:cart_id, :product_id);';

        // Build the parameters array
        $params = [':cart_id' => $cartId, ':product_id' => $productId];

        // Prepare and execute the query
        $stmt = DatabaseHandler::prepare($sql);
        if ($stmt !== false) {
            DatabaseHandler::execute($stmt, $params);
        }
    }

    /**
     * Save a product for later (move from cart to "saved for later" list).
     *
     * @param int $productId The ID of the product to save for later
     */
    public static function saveProductForLater(int $productId): void
    {
        $cartId = self::getCartId();

        // Create the SQL query
        $sql = 'SELECT shopping_cart_save_product_for_later(:cart_id, :product_id);';

        // Build the parameters array
        $params = [':cart_id' => $cartId, ':product_id' => $productId];

        // Prepare and execute the query
        $stmt = DatabaseHandler::prepare($sql);
        if ($stmt !== false) {
            DatabaseHandler::execute($stmt, $params);
        }
    }

    /**
     * Move a product from "saved for later" back to the cart.
     *
     * @param int $productId The ID of the product to move to cart
     */
    public static function moveProductToCart(int $productId): void
    {
        $cartId = self::getCartId();

        // Create the SQL query
        $sql = 'SELECT shopping_cart_move_product_to_cart(:cart_id, :product_id);';

        // Build the parameters array
        $params = [':cart_id' => $cartId, ':product_id' => $productId];

        // Prepare and execute the query
        $stmt = DatabaseHandler::prepare($sql);
        if ($stmt !== false) {
            DatabaseHandler::execute($stmt, $params);
        }
    }

    /**
     * Get products from the shopping cart.
     *
     * @param int $cartProductsType Type of products to get:
     *                              - GET_CART_PRODUCTS (1): Active cart products
     *                              - GET_CART_SAVED_PRODUCTS (2): Saved for later products
     * @return array<int, array{name: string, price: string, quantity: int, subtotal: string,
     *               product_id: int, save_for_later_link: string, move_to_cart_link: string,
     *               remove_link: string}> Array of cart products
     */
    public static function getCartProducts(int $cartProductsType): array
    {
        $cartId = self::getCartId();

        // Create the SQL query
        $sql = 'SELECT * FROM shopping_cart_get_products(:cart_id, :cart_products_type);';

        // Build the parameters array
        $params = [':cart_id' => $cartId, ':cart_products_type' => $cartProductsType];

        // Prepare and execute the query
        $stmt = DatabaseHandler::prepare($sql);
        $result = [];
        if ($stmt !== false) {
            $result = DatabaseHandler::getAll($stmt, $params) ?? [];
        }

        // Build action links for each product
        $queryString = '';

        if (isset($_GET['DepartmentID'])) {
            $queryString .= 'DepartmentID=' . (int)$_GET['DepartmentID'];
        }
        if (isset($_GET['CategoryID'])) {
            $queryString .= '&CategoryID=' . (int)$_GET['CategoryID'];
        }
        if (isset($_GET['ProductID'])) {
            $queryString .= '&ProductID=' . (int)$_GET['ProductID'];
        }
        if ($queryString !== '') {
            $queryString .= '&';
        }

        foreach ($result as &$row) {
            $productId = $row['product_id'];
            $row['save_for_later_link'] = $queryString . self::CART_ACTION_PARAM .
                                          Config::get('cart_action_save_for_later') .
                                          self::PRODUCT_ID_PARAM . $productId;
            $row['move_to_cart_link'] = $queryString . self::CART_ACTION_PARAM .
                                        Config::get('cart_action_move_to_cart') .
                                        self::PRODUCT_ID_PARAM . $productId;
            $row['remove_link'] = $queryString . self::CART_ACTION_PARAM .
                                  Config::get('cart_action_remove') .
                                  self::PRODUCT_ID_PARAM . $productId;
        }

        return $result;
    }

    /**
     * Get the total amount of the shopping cart.
     *
     * @return string The total amount formatted as a string
     */
    public static function getTotalAmount(): string
    {
        $cartId = self::getCartId();

        // Create the SQL query
        $sql = 'SELECT shopping_cart_get_total_amount(:cart_id) AS total;';

        // Build the parameters array
        $params = [':cart_id' => $cartId];

        // Prepare and execute the query
        $stmt = DatabaseHandler::prepare($sql);
        if ($stmt === false) {
            return '0.00';
        }

        $result = DatabaseHandler::getRow($stmt, $params);

        return $result['total'] ?? '0.00';
    }

    /**
     * Count the number of old shopping carts.
     *
     * @param int $days Number of days to consider a cart "old"
     * @return int Number of old shopping carts
     */
    public static function countOldShoppingCarts(int $days): int
    {
        // Create the SQL query
        $sql = 'SELECT shopping_cart_count_old_carts(:days) AS count;';

        // Build the parameters array
        $params = [':days' => $days];

        // Prepare and execute the query
        $stmt = DatabaseHandler::prepare($sql);
        if ($stmt === false) {
            return 0;
        }

        $result = DatabaseHandler::getRow($stmt, $params);

        return (int)($result['count'] ?? 0);
    }

    /**
     * Delete old shopping carts.
     *
     * @param int $days Number of days to consider a cart "old"
     */
    public static function deleteOldShoppingCarts(int $days): void
    {
        // Create the SQL query
        $sql = 'SELECT shopping_cart_delete_old_carts(:days);';

        // Build the parameters array
        $params = [':days' => $days];

        // Prepare and execute the query
        $stmt = DatabaseHandler::prepare($sql);
        if ($stmt !== false) {
            DatabaseHandler::execute($stmt, $params);
        }
    }
}
