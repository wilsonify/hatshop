<?php

use Hatshop\Core\Orders;

/**
 * Smarty plugin function for loading admin order details.
 *
 * @param array $params Parameters passed from template
 * @param Smarty $smarty Smarty instance
 */
function smarty_function_load_admin_order_details(array $params, $smarty): void
{
    $adminOrderDetails = new AdminOrderDetails();
    $adminOrderDetails->init();

    $smarty->assign($params['assign'], $adminOrderDetails);
}

/**
 * Presentation tier class for administering order details.
 */
class AdminOrderDetails
{
    /** @var int Order ID */
    public int $mOrderId = 0;

    /** @var array|null Order info */
    public ?array $mOrderInfo = null;

    /** @var array Order line items */
    public array $mOrderDetails = [];

    /** @var bool Whether edit mode is enabled */
    public bool $mEditEnabled = false;

    /** @var array Order status options */
    public array $mOrderStatusOptions;

    /** @var string Back link to orders list */
    public string $mAdminOrdersPageLink = '';

    public function __construct()
    {
        // Get the back link from session
        $this->mAdminOrdersPageLink = $_SESSION['admin_orders_page_link'] ?? 'admin.php?Page=Orders';

        // We receive the order ID in the query string
        if (isset($_GET['OrderId'])) {
            $this->mOrderId = (int)$_GET['OrderId'];
        } else {
            trigger_error('OrderId parameter is required');
        }

        $this->mOrderStatusOptions = Orders::getStatusOptions();
    }

    /**
     * Initialize class members.
     */
    public function init(): void
    {
        $this->handleUpdate();
        $this->loadOrderData();
        $this->checkEditMode();
    }

    /**
     * Handle order update if submitted.
     */
    private function handleUpdate(): void
    {
        if (!isset($_GET['submitUpdate'])) {
            return;
        }

        Orders::updateOrder(
            $this->mOrderId,
            (int)($_GET['status'] ?? 0),
            $_GET['comments'] ?? '',
            $_GET['customerName'] ?? '',
            $_GET['shippingAddress'] ?? '',
            $_GET['customerEmail'] ?? ''
        );
    }

    /**
     * Load order info and details from database.
     */
    private function loadOrderData(): void
    {
        $this->mOrderInfo = Orders::getOrderInfo($this->mOrderId);
        $this->mOrderDetails = Orders::getOrderDetails($this->mOrderId);
    }

    /**
     * Check if edit mode should be enabled.
     */
    private function checkEditMode(): void
    {
        $this->mEditEnabled = isset($_GET['submitEdit']);
    }
}
