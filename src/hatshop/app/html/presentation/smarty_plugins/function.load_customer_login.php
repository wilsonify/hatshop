<?php

use Hatshop\Core\Customer;

/**
 * Smarty plugin for customer login form.
 */
function smarty_function_load_customer_login(array $params, $smarty): void
{
    $customerLogin = new CustomerLogin();
    $customerLogin->init();

    $smarty->assign($params['assign'], $customerLogin);
}

/**
 * Presentation class for customer login.
 */
class CustomerLogin
{
    public string $mLoginMessage = '';
    public string $mCustomerLoginTarget = '';
    public string $mRegisterUser = '';
    public string $mEmail = '';

    private bool $haveData = false;

    public function __construct()
    {
        if (isset($_POST['Login'])) {
            $this->haveData = true;
        }
    }

    public function init(): void
    {
        $urlBase = $this->getUrlBase();
        $urlParameterPrefix = empty($_GET) ? '?' : '&';

        $this->mCustomerLoginTarget = $urlBase;

        if (strpos($urlBase, 'RegisterCustomer') === false) {
            $this->mRegisterUser = $urlBase . $urlParameterPrefix . 'RegisterCustomer';
        } else {
            $this->mRegisterUser = $urlBase;
        }

        if ($this->haveData) {
            $this->processLogin();
        }
    }

    private function getUrlBase(): string
    {
        $requestUri = getenv('REQUEST_URI') ?: '';
        return substr($requestUri, strrpos($requestUri, '/') + 1);
    }

    private function processLogin(): void
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $loginStatus = Customer::isValid($email, $password);

        switch ($loginStatus) {
            case 2:
                $this->mLoginMessage = 'Unrecognized Email.';
                $this->mEmail = $email;
                break;
            case 1:
                $this->mLoginMessage = 'Unrecognized password.';
                $this->mEmail = $email;
                break;
            case 0:
                $this->redirectAfterLogin();
                break;
            default:
                break;
        }
    }

    private function redirectAfterLogin(): void
    {
        $host = getenv('HATSHOP_HTTP_SERVER_HOST') ?: 'localhost';
        $redirectLink = 'https://' . $host;

        $port = getenv('HATSHOP_HTTP_SERVER_PORT') ?: '443';
        if ($port !== '443' && $port !== '80') {
            $redirectLink .= ':' . $port;
        }

        $redirectLink .= $this->mCustomerLoginTarget;

        header('Location:' . $redirectLink);
        exit;
    }
}
