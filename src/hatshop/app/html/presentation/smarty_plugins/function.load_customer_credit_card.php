<?php

/**
 * Smarty plugin function for customer credit card management.
 *
 * Chapter 12: Storing Customer Orders - Credit Card Management
 *
 * @param array                    $params Smarty parameters
 * @param Smarty\Smarty            $smarty Smarty instance
 * @return void
 */
function smarty_function_load_customer_credit_card(array $params, $smarty): void
{
    $customerCreditCard = new CustomerCreditCard();
    $customerCreditCard->init();
    $smarty->assign($params['assign'], $customerCreditCard);
}

/**
 * Customer credit card management class for handling credit card form validation and submission.
 */
class CustomerCreditCard
{
    // Form target and navigation
    public string $mCustomerCreditCardTarget = '';
    public string $mReturnLink = '';
    public string $mReturnLinkProtocol = 'http';

    // Error flags
    public int $mCardHolderError = 0;
    public int $mCardNumberError = 0;
    public int $mExpDateError = 0;
    public int $mCardTypeError = 0;

    /**
     * Credit card data array with keys: card_holder, card_number, expiry_date,
     * issue_date, issue_number, card_type, card_number_x.
     *
     * @var array<string, string>
     */
    public array $mPlainCreditCard = [];

    /**
     * Available card types for dropdown.
     *
     * @var array<string, string>
     */
    public array $mCardTypes = [];

    // Private state
    private int $errorCount = 0;
    private bool $hasData = false;

    /**
     * Constructor - processes form submission data.
     */
    public function __construct()
    {
        // Initialize empty credit card data
        $this->mPlainCreditCard = [
            'card_holder' => '',
            'card_number' => '',
            'issue_date' => '',
            'expiry_date' => '',
            'issue_number' => '',
            'card_type' => '',
            'card_number_x' => '',
        ];

        $urlBase = $this->extractUrlBase();
        $urlParameterPrefix = count($_GET) === 1 ? '?' : '&';

        $this->mCustomerCreditCardTarget = $urlBase;
        $this->mReturnLink = str_replace($urlParameterPrefix . 'UpdateCreditCardDetails', '', $urlBase);

        if (isset($_GET['Checkout']) && defined('USE_SSL') && constant('USE_SSL') !== 'no') {
            $this->mReturnLinkProtocol = 'https';
        }

        // Available card types
        $this->mCardTypes = [
            '' => 'Select Card Type',
            'Mastercard' => 'Mastercard',
            'Visa' => 'Visa',
            'Switch' => 'Switch',
            'Solo' => 'Solo',
            'American Express' => 'American Express',
        ];

        if (!empty($_POST['sended'])) {
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
        // Card holder is required
        if (empty($_POST['cardHolder'])) {
            $this->mCardHolderError = 1;
            $this->errorCount++;
        } else {
            $this->mPlainCreditCard['card_holder'] = $_POST['cardHolder'];
        }

        // Card number is required
        if (empty($_POST['cardNumber'])) {
            $this->mCardNumberError = 1;
            $this->errorCount++;
        } else {
            $this->mPlainCreditCard['card_number'] = $_POST['cardNumber'];
        }

        // Expiry date is required
        if (empty($_POST['expDate'])) {
            $this->mExpDateError = 1;
            $this->errorCount++;
        } else {
            $this->mPlainCreditCard['expiry_date'] = $_POST['expDate'];
        }

        // Issue date is optional
        if (isset($_POST['issueDate'])) {
            $this->mPlainCreditCard['issue_date'] = $_POST['issueDate'];
        }

        // Issue number is optional
        if (isset($_POST['issueNumber'])) {
            $this->mPlainCreditCard['issue_number'] = $_POST['issueNumber'];
        }

        // Card type is required
        $this->mPlainCreditCard['card_type'] = $_POST['cardType'] ?? '';
        if (empty($this->mPlainCreditCard['card_type'])) {
            $this->mCardTypeError = 1;
            $this->errorCount++;
        }
    }

    /**
     * Initializes the credit card form with existing data or processes submission.
     */
    public function init(): void
    {
        if (!$this->hasData) {
            // No form submission - load existing credit card data
            $this->mPlainCreditCard = \Hatshop\Core\Customer::getPlainCreditCard();
        } elseif ($this->errorCount === 0) {
            // Valid submission - update credit card
            \Hatshop\Core\Customer::updateCreditCardDetails($this->mPlainCreditCard);

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
        $httpPort = defined('HTTP_SERVER_PORT') ? constant('HTTP_SERVER_PORT') : '80';
        if ($httpPort !== '80') {
            $redirectLink .= ':' . $httpPort;
        }

        $redirectLink .= $this->mReturnLink;

        header('Location:' . $redirectLink);
        exit;
    }
}
