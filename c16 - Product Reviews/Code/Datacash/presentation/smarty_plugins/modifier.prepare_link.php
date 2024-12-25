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

    // Prevent adding index.php if it's already in the string
    if (strpos($string, 'index.php') === false) {
        $link .= 'index.php';
    }

    // Append the provided string (query parameters or path)
    $link .= $string;

    // Escape the URL to prevent XSS
    return htmlspecialchars($link, ENT_QUOTES);
}
?>
