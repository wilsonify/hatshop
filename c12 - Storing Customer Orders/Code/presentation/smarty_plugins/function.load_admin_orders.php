<?php
// Plugin functions inside plugin files must be named: smarty_type_name
function smarty_function_load_admin_orders($params, $smarty)
{
  // Create AdminOrders object
  $admin_orders = new AdminOrders();
  $admin_orders->init();

  // Assign template variable
  $smarty->assign($params['assign'], $admin_orders);
}

/* Presentation tier class that supports order administration
   functionality */
class AdminOrders
{
  // Public variables available in smarty template
  public $mOrders;
  public $mStartDate;
  public $mEndDate;
  public $mRecordCount = 20;
  public $mOrderStatusOptions;
  public $mSelectedStatus = 0;
  public $mErrorMessage = '';
  public $mCustomers;
  public $mCustomerId;
  public $mOrderId;

  // Class constructor
  public function __construct()
  {
    /* Save the link to the current page in the AdminOrdersPageLink
       session variable; it will be used to create the
       "back to admin orders ..." link in admin order details pages */
    $_SESSION['admin_orders_page_link'] =
      str_replace(VIRTUAL_LOCATION, '', getenv('REQUEST_URI'));

    $this->mOrderStatusOptions = Orders::$mOrderStatusOptions;
  }

  public function init()
  {
    // If the "Show the most recent x orders" filter is in action ...
    if (isset ($_GET['submitMostRecent']))
    {
      // If the record count value is not a valid integer, display error
      if ((string)(int)$_GET['recordCount'] == (string)$_GET['recordCount'])
      {
        $this->mRecordCount = (int)$_GET['recordCount'];
        $this->mOrders = Orders::GetMostRecentOrders($this->mRecordCount);
      }
      else
        $this->mErrorMessage = $_GET['recordCount'] . ' is not a number.';
    }

    /* If the "Show all records created between date_1 and date_2"
       filter is in action ... */
    if (isset ($_GET['submitBetweenDates']))
    {
      $this->mStartDate = $_GET['startDate'];
      $this->mEndDate = $_GET['endDate'];

      // Check if the start date is in accepted format
      if (($this->mStartDate == '') ||
          ($timestamp = strtotime($this->mStartDate)) == -1)
        $this->mErrorMessage = 'The start date is invalid. ';
      else
        // Transform date to YYYY/MM/DD HH:MM:SS format
        $this->mStartDate =
          strftime('%Y/%m/%d %H:%M:%S', strtotime($this->mStartDate));

      // Check if the end date is in accepted format
      if (($this->mEndDate == '') ||
          ($timestamp = strtotime($this->mEndDate)) == -1)
        $this->mErrorMessage .= 'The end date is invalid.';
      else
        // Transform date to YYYY/MM/DD HH:MM:SS format
        $this->mEndDate =
          strftime('%Y/%m/%d %H:%M:%S', strtotime($this->mEndDate));

      // Check if start date is more recent than the end date
      if ((empty ($this->mErrorMessage)) &&
          (strtotime($this->mStartDate) > strtotime($this->mEndDate)))
        $this->mErrorMessage .=
          'The start date should be more recent than the end date.';

      // If there are no errors, get the orders between the two dates
      if (empty($this->mErrorMessage))
        $this->mOrders = Orders::GetOrdersBetweenDates(
                           $this->mStartDate, $this->mEndDate);
    }

    // If "Show orders by status" filter is in action ...
    if (isset ($_GET['submitOrdersByStatus']))
    {
      $this->mSelectedStatus = $_GET['status'];
      $this->mOrders = Orders::GetOrdersByStatus($this->mSelectedStatus);
    }

    // If the "Show orders by customer ID" filter is in action ...
    if (isset ($_GET['submitByCustomer']))
    {
      if (empty ($_GET['customer_id']))
        $this->mErrorMessage = 'No customer has been selected';
      else
      {
        $this->mCustomerId = $_GET['customer_id'];
        $this->mOrders = Orders::GetByCustomerId($this->mCustomerId);
      }
    }

    // If the "Get order by ID" filter is in action ...
    if (isset ($_GET['submitByOrderId']))
    {
      if (empty ($_GET['orderId']))
        $this->mErrorMessage = 'You must enter an order ID.';
      else
      {
        $this->mOrderId = $_GET['orderId'];
        $this->mOrders = Orders::GetOrderShortDetails($this->mOrderId);
      }
    }

    $this->mCustomers = Customer::GetCustomersList();

    // Build View Details link
    for ($i = 0; $i < count($this->mOrders); $i++)
    {
      $this->mOrders[$i]['onclick'] =
        'admin.php?Page=OrderDetails&OrderId=' .
        $this->mOrders[$i]['order_id'];
    }
  }
}

