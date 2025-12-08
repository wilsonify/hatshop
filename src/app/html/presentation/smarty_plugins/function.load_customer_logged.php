<?php

use Hatshop\Core\Customer;

/**
 * Smarty plugin for customer logged status.
 */
function smarty_function_load_customer_logged(array $params, $smarty): void
{
    $customerLogged = new CustomerLogged();
    $smarty->assign($params['assign'], $customerLogged);
}

/**
 * Presentation class for customer logged status.
 */
class CustomerLogged
{
    public bool $mIsLogged = false;
    public string $mCustomerName = '';
    public string $mLogout = '';
    public string $mUpdateAccountDetails = '';
    public string $mUpdateAddressDetails = '';
    public string $mUpdateCreditCardDetails = '';

    public function __construct()
    {
        $urlBase = $this->getUrlBase();
        $urlParameterPrefix = empty($_GET) ? '?' : '&';

        $this->mLogout = $urlBase . $urlParameterPrefix . 'Logout';
        $this->mUpdateAccountDetails = $urlBase . $urlParameterPrefix . 'UpdateAccountDetails';
        $this->mUpdateAddressDetails = $urlBase . $urlParameterPrefix . 'UpdateAddressDetails';
        $this->mUpdateCreditCardDetails = $urlBase . $urlParameterPrefix . 'UpdateCreditCardDetails';

        if (Customer::isAuthenticated()) {
            $this->mIsLogged = true;
            $customerData = Customer::get();
            $this->mCustomerName = $customerData['name'] ?? '';
        }
    }

    private function getUrlBase(): string
    {
        $requestUri = getenv('REQUEST_URI') ?: '';
        return substr($requestUri, strrpos($requestUri, '/') + 1);
    }
}
