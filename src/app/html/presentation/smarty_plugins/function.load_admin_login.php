<?php

use Hatshop\Core\Config;

/**
 * Smarty plugin function for admin login.
 */
function smarty_function_load_admin_login($params, $smarty)
{
    $admin_login = new AdminLogin();
    $smarty->assign($params['assign'], $admin_login);
}

/**
 * Class that handles administrator authentication.
 */
class AdminLogin
{
    public $mUsername = '';
    public $mLoginMessage = '';

    public function __construct()
    {
        if (isset($_POST['submit'])) {
            $adminUsername = Config::get('admin_username', 'hatshopadmin');
            $adminPassword = Config::get('admin_password', 'hatshopadmin');

            if ($_POST['username'] === $adminUsername
                && $_POST['password'] === $adminPassword) {
                $_SESSION['admin_logged'] = true;
                header('Location: admin.php');
                exit;
            } else {
                $this->mLoginMessage = 'Login failed. Please try again:';
            }
        }

        if (isset($_POST['username'])) {
            $this->mUsername = $_POST['username'];
        }
    }
}
