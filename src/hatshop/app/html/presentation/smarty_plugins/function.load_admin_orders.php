<?php

use Hatshop\Core\Orders;
use Hatshop\Core\FeatureFlags;

/**
 * Smarty plugin function for loading admin orders list.
 *
 * @param array $params Parameters passed from template
 * @param Smarty $smarty Smarty instance
 */
function smarty_function_load_admin_orders(array $params, $smarty): void
{
    $adminOrders = new AdminOrders();
    $adminOrders->init();

    $smarty->assign($params['assign'], $adminOrders);
}

/**
 * Presentation tier class for order administration functionality.
 */
class AdminOrders
{
    private const ADMIN_PAGE_PREFIX = 'admin.php?Page=OrderDetails&OrderId=';

    /** @var array Orders list */
    public array $mOrders = [];

    /** @var string Start date filter */
    public string $mStartDate = '';

    /** @var string End date filter */
    public string $mEndDate = '';

    /** @var int Number of recent records to show */
    public int $mRecordCount = 20;

    /** @var array Order status options */
    public array $mOrderStatusOptions;

    /** @var int Selected status filter */
    public int $mSelectedStatus = 0;

    /** @var string Error message */
    public string $mErrorMessage = '';

    public function __construct()
    {
        // Save the link to the current page for back navigation
        $virtualLocation = defined('VIRTUAL_LOCATION') ? constant('VIRTUAL_LOCATION') : '';
        $_SESSION['admin_orders_page_link'] =
            str_replace($virtualLocation, '', getenv('REQUEST_URI') ?: '');

        $this->mOrderStatusOptions = Orders::getStatusOptions();
    }

    /**
     * Initialize the admin orders view based on filter parameters.
     */
    public function init(): void
    {
        $this->handleMostRecentFilter();
        $this->handleBetweenDatesFilter();
        $this->handleStatusFilter();
        $this->buildOrderLinks();
    }

    /**
     * Handle "Show most recent X orders" filter.
     */
    private function handleMostRecentFilter(): void
    {
        if (!isset($_GET['submitMostRecent'])) {
            return;
        }

        $recordCount = $_GET['recordCount'] ?? '';

        if ((string)(int)$recordCount !== (string)$recordCount) {
            $this->mErrorMessage = $recordCount . ' is not a number.';
            return;
        }

        $this->mRecordCount = (int)$recordCount;
        $this->mOrders = Orders::getMostRecentOrders($this->mRecordCount);
    }

    /**
     * Handle "Show orders between dates" filter.
     */
    private function handleBetweenDatesFilter(): void
    {
        if (!isset($_GET['submitBetweenDates'])) {
            return;
        }

        $this->mStartDate = $_GET['startDate'] ?? '';
        $this->mEndDate = $_GET['endDate'] ?? '';

        // Validate start date
        if ($this->mStartDate === '' || strtotime($this->mStartDate) === false) {
            $this->mErrorMessage = 'The start date is invalid. ';
        } else {
            $this->mStartDate = date('Y/m/d H:i:s', strtotime($this->mStartDate));
        }

        // Validate end date
        if ($this->mEndDate === '' || strtotime($this->mEndDate) === false) {
            $this->mErrorMessage .= 'The end date is invalid.';
        } else {
            $this->mEndDate = date('Y/m/d H:i:s', strtotime($this->mEndDate));
        }

        // Check date order
        if (empty($this->mErrorMessage) &&
            strtotime($this->mStartDate) > strtotime($this->mEndDate)) {
            $this->mErrorMessage .= 'The start date should be more recent than the end date.';
        }

        // Get orders if no errors
        if (empty($this->mErrorMessage)) {
            $this->mOrders = Orders::getOrdersBetweenDates($this->mStartDate, $this->mEndDate);
        }
    }

    /**
     * Handle "Show orders by status" filter.
     */
    private function handleStatusFilter(): void
    {
        if (!isset($_GET['submitOrdersByStatus'])) {
            return;
        }

        $this->mSelectedStatus = (int)($_GET['status'] ?? 0);
        $this->mOrders = Orders::getOrdersByStatus($this->mSelectedStatus);
    }

    /**
     * Build view details links for each order.
     */
    private function buildOrderLinks(): void
    {
        $count = count($this->mOrders);
        for ($i = 0; $i < $count; $i++) {
            $this->mOrders[$i]['onclick'] =
                self::ADMIN_PAGE_PREFIX . $this->mOrders[$i]['order_id'];
        }
    }
}
