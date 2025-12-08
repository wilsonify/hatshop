<?php

/**
 * Smarty plugin function for customer address management.
 *
 * Chapter 11: Managing Customer Details - Address Management
 *
 * @param array                    $params Smarty parameters
 * @param Smarty\Smarty            $smarty Smarty instance
 * @return void
 */
function smarty_function_load_customer_address(array $params, $smarty): void
{
    $customerAddress = new CustomerAddress();
    $customerAddress->init();
    $smarty->assign($params['assign'], $customerAddress);
}

/**
 * Customer address management class for handling address form validation and submission.
 */
class CustomerAddress
{
    // Form target and navigation
    public string $mCustomerAddressTarget = '';
    public string $mReturnLink = '';
    public string $mReturnLinkProtocol = 'http';

    // Address fields
    public string $mAddress1 = '';
    public string $mAddress2 = '';
    public string $mCity = '';
    public string $mRegion = '';
    public string $mPostalCode = '';
    public string $mCountry = '';
    public string $mShippingRegion = '';

    /**
     * @var array<int, string>
     */
    public array $mShippingRegions = [];

    // Error flags
    public int $mAddress1Error = 0;
    public int $mCityError = 0;
    public int $mRegionError = 0;
    public int $mPostalCodeError = 0;
    public int $mCountryError = 0;
    public int $mShippingRegionError = 0;

    // Private state
    private int $errorCount = 0;
    private bool $hasData = false;

    /**
     * Constructor - processes form submission data.
     */
    public function __construct()
    {
        $urlBase = $this->extractUrlBase();
        $urlParameterPrefix = count($_GET) === 1 ? '?' : '&';

        $this->mCustomerAddressTarget = $urlBase;
        $this->mReturnLink = str_replace($urlParameterPrefix . 'UpdateAddressDetails', '', $urlBase);

        if (isset($_GET['Checkout']) && defined('USE_SSL') && USE_SSL !== 'no') {
            $this->mReturnLinkProtocol = 'https';
        }

        if (isset($_POST['sended'])) {
            $this->hasData = true;
            $this->validateFormData();
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

    /**
     * Validates form data from POST submission.
     */
    private function validateFormData(): void
    {
        // Address 1 is required
        if (empty($_POST['address1'])) {
            $this->mAddress1Error = 1;
            $this->errorCount++;
        } else {
            $this->mAddress1 = $_POST['address1'];
        }

        // Address 2 is optional
        if (isset($_POST['address2'])) {
            $this->mAddress2 = $_POST['address2'];
        }

        // City is required
        if (empty($_POST['city'])) {
            $this->mCityError = 1;
            $this->errorCount++;
        } else {
            $this->mCity = $_POST['city'];
        }

        // Region is required
        if (empty($_POST['region'])) {
            $this->mRegionError = 1;
            $this->errorCount++;
        } else {
            $this->mRegion = $_POST['region'];
        }

        // Postal code is required
        if (empty($_POST['postalCode'])) {
            $this->mPostalCodeError = 1;
            $this->errorCount++;
        } else {
            $this->mPostalCode = $_POST['postalCode'];
        }

        // Country is required
        if (empty($_POST['country'])) {
            $this->mCountryError = 1;
            $this->errorCount++;
        } else {
            $this->mCountry = $_POST['country'];
        }

        // Shipping region must be selected (not the default value 1)
        if (isset($_POST['shippingRegion']) && $_POST['shippingRegion'] == 1) {
            $this->mShippingRegionError = 1;
            $this->errorCount++;
        } elseif (isset($_POST['shippingRegion'])) {
            $this->mShippingRegion = $_POST['shippingRegion'];
        }
    }

    /**
     * Initializes the address form with existing data or processes submission.
     */
    public function init(): void
    {
        // Load shipping regions for dropdown
        $shippingRegions = \Hatshop\Core\Customer::getShippingRegions();

        foreach ($shippingRegions as $item) {
            $this->mShippingRegions[$item['shipping_region_id']] = $item['shipping_region'];
        }

        if (!$this->hasData) {
            // No form submission - load existing customer data
            $customerData = \Hatshop\Core\Customer::get();

            if (!empty($customerData)) {
                $this->mAddress1 = $customerData['address_1'] ?? '';
                $this->mAddress2 = $customerData['address_2'] ?? '';
                $this->mCity = $customerData['city'] ?? '';
                $this->mRegion = $customerData['region'] ?? '';
                $this->mPostalCode = $customerData['postal_code'] ?? '';
                $this->mCountry = $customerData['country'] ?? '';
                $this->mShippingRegion = $customerData['shipping_region_id'] ?? '';
            }
        } elseif ($this->errorCount === 0) {
            // Valid submission - update address
            \Hatshop\Core\Customer::updateAddressDetails([
                'address1' => $this->mAddress1,
                'address2' => $this->mAddress2,
                'city' => $this->mCity,
                'region' => $this->mRegion,
                'postalCode' => $this->mPostalCode,
                'country' => $this->mCountry,
                'shippingRegionId' => (int) $this->mShippingRegion,
            ]);

            $this->redirectAfterSave();
        }
    }

    /**
     * Redirects to the return link after successful save.
     */
    private function redirectAfterSave(): void
    {
        $redirectLink = 'https://' . getenv('HATSHOP_HTTP_SERVER_HOST');

        // Add port if not standard
        $httpPort = defined('HTTP_SERVER_PORT') ? HTTP_SERVER_PORT : '80';
        if ($httpPort !== '80') {
            $redirectLink .= ':' . $httpPort;
        }

        $redirectLink .= $this->mReturnLink;

        header('Location:' . $redirectLink);
        exit;
    }
}
