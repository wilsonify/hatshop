<?php

namespace Hatshop\Core;

/**
 * Business tier class for order management (Chapter 9).
 *
 * Provides methods for retrieving and updating customer orders.
 */
class Orders
{
    /**
     * Order status options array indexed by status code.
     */
    public const ORDER_STATUS_OPTIONS = [
        0 => 'placed',
        1 => 'verified',
        2 => 'completed',
        3 => 'canceled',
    ];

    /**
     * Get the most recent orders.
     *
     * @param int $howMany Number of orders to retrieve
     * @return array Order records
     */
    public static function getMostRecentOrders(int $howMany): array
    {
        $sql = 'SELECT * FROM orders_get_most_recent_orders(:how_many);';
        $params = [':how_many' => $howMany];
        $result = DatabaseHandler::prepare($sql);

        return DatabaseHandler::getAll($result, $params);
    }

    /**
     * Get orders between two dates.
     *
     * @param string $startDate Start date in YYYY/MM/DD HH:MM:SS format
     * @param string $endDate End date in YYYY/MM/DD HH:MM:SS format
     * @return array Order records
     */
    public static function getOrdersBetweenDates(string $startDate, string $endDate): array
    {
        $sql = 'SELECT * FROM orders_get_orders_between_dates(:start_date, :end_date);';
        $params = [':start_date' => $startDate, ':end_date' => $endDate];
        $result = DatabaseHandler::prepare($sql);

        return DatabaseHandler::getAll($result, $params);
    }

    /**
     * Get orders by status.
     *
     * @param int $status Status code (0=placed, 1=verified, 2=completed, 3=canceled)
     * @return array Order records
     */
    public static function getOrdersByStatus(int $status): array
    {
        $sql = 'SELECT * FROM orders_get_orders_by_status(:status);';
        $params = [':status' => $status];
        $result = DatabaseHandler::prepare($sql);

        return DatabaseHandler::getAll($result, $params);
    }

    /**
     * Get details of a specific order.
     *
     * @param int $orderId Order ID
     * @return array|null Order info or null if not found
     */
    public static function getOrderInfo(int $orderId): ?array
    {
        $sql = 'SELECT * FROM orders_get_order_info(:order_id);';
        $params = [':order_id' => $orderId];
        $result = DatabaseHandler::prepare($sql);

        return DatabaseHandler::getRow($result, $params);
    }

    /**
     * Get products that belong to a specific order.
     *
     * @param int $orderId Order ID
     * @return array Order detail records (product_id, product_name, quantity, unit_cost, subtotal)
     */
    public static function getOrderDetails(int $orderId): array
    {
        $sql = 'SELECT * FROM orders_get_order_details(:order_id);';
        $params = [':order_id' => $orderId];
        $result = DatabaseHandler::prepare($sql);

        return DatabaseHandler::getAll($result, $params);
    }

    /**
     * Update order details.
     *
     * @param int $orderId Order ID
     * @param int $status New status code
     * @param string $comments Order comments
     * @param string $customerName Customer name
     * @param string $shippingAddress Shipping address
     * @param string $customerEmail Customer email
     */
    public static function updateOrder(
        int $orderId,
        int $status,
        string $comments,
        string $customerName,
        string $shippingAddress,
        string $customerEmail
    ): void {
        $sql = 'SELECT orders_update_order(:order_id, :status, :comments,
                     :customer_name, :shipping_address, :customer_email);';
        $params = [
            ':order_id' => $orderId,
            ':status' => $status,
            ':comments' => $comments,
            ':customer_name' => $customerName,
            ':shipping_address' => $shippingAddress,
            ':customer_email' => $customerEmail,
        ];
        $result = DatabaseHandler::prepare($sql);

        DatabaseHandler::execute($result, $params);
    }

    /**
     * Get status options array for use in templates.
     *
     * @return array Status options indexed by status code
     */
    public static function getStatusOptions(): array
    {
        return self::ORDER_STATUS_OPTIONS;
    }

    /**
     * Get orders by customer ID (Chapter 12).
     *
     * @param int $customerId Customer ID
     * @return array Orders for the customer
     */
    public static function getByCustomerId(int $customerId): array
    {
        $sql = 'SELECT * FROM orders_get_by_customer_id(:customer_id);';
        $params = [':customer_id' => $customerId];
        $result = DatabaseHandler::prepare($sql);

        return DatabaseHandler::getAll($result, $params) ?? [];
    }

    /**
     * Get short order details (Chapter 12).
     *
     * @param int $orderId Order ID
     * @return array Short order details
     */
    public static function getOrderShortDetails(int $orderId): array
    {
        $sql = 'SELECT * FROM orders_get_order_short_details(:order_id);';
        $params = [':order_id' => $orderId];
        $result = DatabaseHandler::prepare($sql);

        return DatabaseHandler::getAll($result, $params) ?? [];
    }

    /**
     * Get shipping info for a region (Chapter 12).
     *
     * @param int $shippingRegionId Shipping region ID
     * @return array Shipping options
     */
    public static function getShippingInfo(int $shippingRegionId): array
    {
        $sql = 'SELECT * FROM orders_get_shipping_info(:shipping_region_id);';
        $params = [':shipping_region_id' => $shippingRegionId];
        $result = DatabaseHandler::prepare($sql);

        return DatabaseHandler::getAll($result, $params) ?? [];
    }

    /**
     * Create an audit record for order processing (Chapter 13).
     *
     * @param int $orderId Order ID
     * @param string $message Audit message
     * @param int $messageNumber Message number code
     */
    public static function createAudit(int $orderId, string $message, int $messageNumber): void
    {
        $sql = 'SELECT orders_create_audit(:order_id, :message, :code);';
        $params = [
            ':order_id' => $orderId,
            ':message' => $message,
            ':code' => $messageNumber,
        ];
        $result = DatabaseHandler::prepare($sql);

        DatabaseHandler::execute($result, $params);
    }

    /**
     * Update order status (Chapter 13).
     *
     * @param int $orderId Order ID
     * @param int $status New status code
     */
    public static function updateOrderStatus(int $orderId, int $status): void
    {
        $sql = 'SELECT orders_update_status(:order_id, :status);';
        $params = [
            ':order_id' => $orderId,
            ':status' => $status,
        ];
        $result = DatabaseHandler::prepare($sql);

        DatabaseHandler::execute($result, $params);
    }

    /**
     * Set the authorization code and reference for an order (Chapter 13).
     *
     * @param int $orderId Order ID
     * @param string $authCode Authorization code from payment processor
     * @param string $reference Reference number
     */
    public static function setOrderAuthCodeAndReference(
        int $orderId,
        string $authCode,
        string $reference
    ): void {
        $sql = 'SELECT orders_set_auth_code(:order_id, :auth_code, :reference);';
        $params = [
            ':order_id' => $orderId,
            ':auth_code' => $authCode,
            ':reference' => $reference,
        ];
        $result = DatabaseHandler::prepare($sql);

        DatabaseHandler::execute($result, $params);
    }

    /**
     * Set the date when an order was shipped (Chapter 14).
     *
     * @param int $orderId Order ID
     */
    public static function setDateShipped(int $orderId): void
    {
        $sql = 'SELECT orders_set_date_shipped(:order_id);';
        $params = [':order_id' => $orderId];
        $result = DatabaseHandler::prepare($sql);

        DatabaseHandler::execute($result, $params);
    }

    /**
     * Get orders awaiting processing at specific pipeline stage (Chapter 13).
     *
     * @param int $status Status code to filter by
     * @return array Orders awaiting processing
     */
    public static function getOrdersToProcess(int $status): array
    {
        $sql = 'SELECT * FROM orders_get_orders_to_process(:status);';
        $params = [':status' => $status];
        $result = DatabaseHandler::prepare($sql);

        return DatabaseHandler::getAll($result, $params) ?? [];
    }

    /**
     * Get audit trail for an order (Chapter 13).
     *
     * @param int $orderId Order ID
     * @return array Audit records
     */
    public static function getAuditTrail(int $orderId): array
    {
        $sql = 'SELECT * FROM orders_get_audit_trail(:order_id);';
        $params = [':order_id' => $orderId];
        $result = DatabaseHandler::prepare($sql);

        return DatabaseHandler::getAll($result, $params) ?? [];
    }
}
