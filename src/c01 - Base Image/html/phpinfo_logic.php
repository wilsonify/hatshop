<?php
// Capture and display the PHP info output
ob_start();
phpinfo();
$phpinfoOutput = ob_get_clean();
