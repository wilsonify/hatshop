<?php

use Hatshop\Core\Customer;

/**
 * Smarty plugin for customer details form (registration/edit).
 */
function smarty_function_load_customer_details(array $params, $smarty): void
{
    $customerDetails = new CustomerDetails();
    $customerDetails->init();

    $smarty->assign($params['assign'], $customerDetails);
}

/**
 * Presentation class for customer details.
 */
class CustomerDetails
{
    public int $mEditMode = 0;
    public string $mCustomerDetailsTarget = '';
    public string $mReturnLink = '';
    public string $mReturnLinkProtocol = 'http';
    public string $mEmail = '';
    public string $mName = '';
    public string $mPassword = '';
    public ?string $mDayPhone = null;
    public ?string $mEvePhone = null;
    public ?string $mMobPhone = null;
    public int $mNameError = 0;
    public int $mEmailError = 0;
    public int $mPasswordError = 0;
    public int $mPasswordConfirmError = 0;
    public int $mPasswordMatchError = 0;
    public int $mEmailAlreadyTaken = 0;

    private int $errors = 0;
    private bool $haveData = false;

    public function __construct()
    {
        if (Customer::isAuthenticated()) {
            $this->mEditMode = 1;
        }

        $urlBase = $this->getUrlBase();
        $urlParameterPrefix = count($_GET) === 1 ? '?' : '&';

        $this->mCustomerDetailsTarget = $urlBase;

        if ($this->mEditMode === 0) {
            $this->mReturnLink = str_replace($urlParameterPrefix . 'RegisterCustomer', '', $urlBase);
        } else {
            $this->mReturnLink = str_replace($urlParameterPrefix . 'UpdateAccountDetails', '', $urlBase);
        }

        if (isset($_GET['Checkout']) && defined('USE_SSL') && constant('USE_SSL') !== 'no') {
            $this->mReturnLinkProtocol = 'https';
        }

        if (isset($_POST['sended'])) {
            $this->haveData = true;
            $this->validateInput();
        }
    }

    private function getUrlBase(): string
    {
        $requestUri = getenv('REQUEST_URI') ?: '';
        return substr($requestUri, strrpos($requestUri, '/') + 1);
    }

    private function validateInput(): void
    {
        if (empty($_POST['name'])) {
            $this->mNameError = 1;
            $this->errors++;
        } else {
            $this->mName = $_POST['name'];
        }

        if ($this->mEditMode === 0 && empty($_POST['email'])) {
            $this->mEmailError = 1;
            $this->errors++;
        } else {
            $this->mEmail = $_POST['email'] ?? '';
        }

        if (empty($_POST['password'])) {
            $this->mPasswordError = 1;
            $this->errors++;
        } else {
            $this->mPassword = $_POST['password'];
        }

        if (empty($_POST['passwordConfirm'])) {
            $this->mPasswordConfirmError = 1;
            $this->errors++;
        }

        $passwordConfirm = $_POST['passwordConfirm'] ?? '';
        if ($this->mPassword !== $passwordConfirm) {
            $this->mPasswordMatchError = 1;
            $this->errors++;
        }

        if ($this->mEditMode === 1) {
            $this->mDayPhone = $_POST['dayPhone'] ?? null;
            $this->mEvePhone = $_POST['evePhone'] ?? null;
            $this->mMobPhone = $_POST['mobPhone'] ?? null;
        }
    }

    public function init(): void
    {
        if ($this->haveData && $this->errors === 0) {
            $this->processSubmission();
            return;
        }

        if ($this->mEditMode === 1 && !$this->haveData) {
            $this->loadExistingData();
        }
    }

    private function processSubmission(): void
    {
        $customerRead = Customer::getLoginInfo($this->mEmail);

        if (!empty($customerRead['customer_id']) && $this->mEditMode === 0) {
            $this->mEmailAlreadyTaken = 1;
            return;
        }

        if ($this->mEditMode === 0) {
            Customer::add($this->mName, $this->mEmail, $this->mPassword);
        } else {
            Customer::updateAccountDetails(
                $this->mName,
                $this->mEmail,
                $this->mPassword,
                $this->mDayPhone,
                $this->mEvePhone,
                $this->mMobPhone
            );
        }

        $this->redirect();
    }

    private function loadExistingData(): void
    {
        $customerData = Customer::get();

        if ($customerData) {
            $this->mName = $customerData['name'] ?? '';
            $this->mEmail = $customerData['email'] ?? '';
            $this->mDayPhone = $customerData['day_phone'] ?? null;
            $this->mEvePhone = $customerData['eve_phone'] ?? null;
            $this->mMobPhone = $customerData['mob_phone'] ?? null;
        }
    }

    private function redirect(): void
    {
        $host = getenv('HATSHOP_HTTP_SERVER_HOST') ?: 'localhost';
        $redirectLink = 'https://' . $host;

        if (defined('HTTP_SERVER_PORT') && constant('HTTP_SERVER_PORT') !== '80') {
            $redirectLink .= ':' . constant('HTTP_SERVER_PORT');
        }

        $redirectLink .= $this->mReturnLink;

        header('Location:' . $redirectLink);
        exit;
    }
}
