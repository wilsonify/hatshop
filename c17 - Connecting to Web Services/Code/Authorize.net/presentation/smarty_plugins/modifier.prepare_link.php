<?php
// Plugin functions inside plugin files must be named: smarty_type_name
function smarty_modifier_prepare_link($string)
{
    // Always use HTTPS and the correct domain
    $link = 'https://' . getenv('SERVER_NAME');

    // Ensure there's a slash between the domain and the path if needed
    if ($string && $string[0] !== '/') {
        $link .= '/';
    }

    // Check for 'admin.php' or 'index.php' and handle accordingly
    if (strpos($string, 'index.php') === false && strpos($string, 'admin.php') === false) {
        $link .= 'index.php';  // Add 'index.php' only if it's not already in the path
    }

    // Append the provided string (query parameters or path)
    $link .= $string;

    // Escape the URL to prevent XSS
    return htmlspecialchars($link, ENT_QUOTES);
}
?>
