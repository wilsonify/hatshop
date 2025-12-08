<?php

/**
 * Smarty plugin function for checkout information display and order placement.
 *
 * Chapter 12: Storing Customer Orders - Checkout
 *
 * @param array                    $params Smarty parameters
 * @param Smarty\Smarty            $smarty Smarty instance
 * @return void
 */
function smarty_function_load_checkout_info(array $params, $smarty): void
{
    $checkoutInfo = new CheckoutInfo();
    $checkoutInfo->init();
    $smarty->assign($params['assign'], $checkoutInfo);
}

/**
 * Checkout information class for handling order review and placement.
 */
class CheckoutInfo
{
    /**
     * Cart items for checkout.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $mCartItems = [];

    public string $mTotalAmountLabel = '0.00';
    public string $mCreditCardNote = '';
    public string $mEditCart = 'index.php?CartAction';
    public string $mOrderButtonVisible = '';
    public string $mNoShippingAddress = 'no';
    public string $mNoCreditCard = 'no';
    public string $mContinueShopping = '';
    public string $mCheckoutInfoLink = '';
    public string $mShippingRegion = '';

    /**
     * Plain credit card data.
     *
     * @var array<string, string>
     */
    public array $mPlainCreditCard = [];

    /**
     * Customer data.
     *
     * @var array<string, mixed>
     */
    public array $mCustomerData = [];

    /**
     * Available shipping options.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $mShippings = [];

    // Private state
    private bool $placeOrder = false;

    /**
     * Constructor - checks if order should be placed.
     */
    public function __construct()
    {
        if (isset($_POST['sended'])) {
            $this->placeOrder = true;
        }
    }

    /**
     * Initializes checkout information or processes order placement.
     */
    public function init(): void
    {
        // If the Place Order button was clicked, save the order to database
        if ($this->placeOrder) {
            $this->processOrder();
            return;
        }

        $this->loadCheckoutData();
    }

    /**
     * Processes the order placement.
     */
    private function processOrder(): void
    {
        $this->mCustomerData = \Hatshop\Core\Customer::get();
        $taxId = $this->determineTaxId($this->mCustomerData['shipping_region_id'] ?? 0);

        $shippingId = isset($_POST['shipping']) ? (int) $_POST['shipping'] : 0;
        $customerId = $this->mCustomerData['customer_id'] ?? 0;

        \Hatshop\Core\ShoppingCart::createOrder($customerId, $shippingId, $taxId);

        $this->redirectAfterOrder();
    }

    /**
     * Determines the tax ID based on shipping region.
     *
     * @param int $shippingRegionId Shipping region ID
     * @return int Tax ID
     */
    private function determineTaxId(int $shippingRegionId): int
    {
        // US shipping region gets tax ID 1, others get tax ID 2
        return $shippingRegionId === 2 ? 1 : 2;
    }

    /**
     * Redirects after successful order placement.
     */
    private function redirectAfterOrder(): void
    {
        $redirectLink = 'https://' . getenv('HATSHOP_HTTP_SERVER_HOST');

        // Add port if not standard
        $httpPort = defined('HTTP_SERVER_PORT') ? constant('HTTP_SERVER_PORT') : '80';
        if ($httpPort !== '80') {
            $redirectLink .= ':' . $httpPort;
        }

        $redirectLink .= '/index.php';

        header('Location:' . $redirectLink);
        exit;
    }

    /**
     * Loads checkout data for display.
     */
    private function loadCheckoutData(): void
    {
        $this->mCheckoutInfoLink = $this->extractUrlBase();

        // Load cart items and total
        $this->mCartItems = \Hatshop\Core\ShoppingCart::getCartProducts();
        $this->mTotalAmountLabel = \Hatshop\Core\ShoppingCart::getTotalAmount();

        // Continue shopping link
        $this->mContinueShopping = $_SESSION['page_link'] ?? 'index.php';

        // Load customer data
        $this->mCustomerData = \Hatshop\Core\Customer::get();

        $this->validateCreditCard();
        $this->validateShippingAddress();

        // Load shipping options if both credit card and address are present
        if ($this->mNoCreditCard === 'no' && $this->mNoShippingAddress === 'no') {
            $this->mShippings = \Hatshop\Core\Orders::getShippingInfo(
                $this->mCustomerData['shipping_region_id']
            );
        }
    }

    /**
     * Validates credit card presence.
     */
    private function validateCreditCard(): void
    {
        if (empty($this->mCustomerData['credit_card'])) {
            $this->mOrderButtonVisible = 'disabled="disabled"';
            $this->mNoCreditCard = 'yes';
        } else {
            $this->mPlainCreditCard = \Hatshop\Core\Customer::decryptCreditCard(
                $this->mCustomerData['credit_card']
            );

            $this->mCreditCardNote = 'Credit card to use: ' .
                ($this->mPlainCreditCard['card_type'] ?? '') .
                '<br />Card number: ' .
                ($this->mPlainCreditCard['card_number_x'] ?? '');
        }
    }

    /**
     * Validates shipping address presence.
     */
    private function validateShippingAddress(): void
    {
        if (empty($this->mCustomerData['address_1'])) {
            $this->mOrderButtonVisible = 'disabled="disabled"';
            $this->mNoShippingAddress = 'yes';
        } else {
            $shippingRegions = \Hatshop\Core\Customer::getShippingRegions();

            foreach ($shippingRegions as $item) {
                if ($item['shipping_region_id'] == ($this->mCustomerData['shipping_region_id'] ?? 0)) {
                    $this->mShippingRegion = $item['shipping_region'];
                    break;
                }
            }
        }
    }

    /**
     * Extracts the URL base from REQUEST_URI.
     *
     * @return string
     */
    private function extractUrlBase(): string
    {
        $requestUri = getenv('REQUEST_URI') ?: '';
        $lastSlash = strrpos($requestUri, '/');
        if ($lastSlash !== false) {
            return substr($requestUri, $lastSlash + 1);
        }
        return '';
    }
}
