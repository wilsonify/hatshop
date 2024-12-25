<?php
// Plugin functions inside plugin files must be named: smarty_type_name
function smarty_modifier_prepare_link($string, $link_type = 'https')
{
    // Always use HTTPS
    $link_type = 'https';

    // Build the base URL with https
    $link = 'https://' . getenv('SERVER_NAME');

    // If HTTP_SERVER_PORT is defined and different than default (443 for HTTPS)
    if (defined('HTTP_SERVER_PORT') && HTTP_SERVER_PORT != '443') {
        // Append server port
        $link .= ':' . HTTP_SERVER_PORT;
    }

    // Append the virtual location and the provided string
    $link .= VIRTUAL_LOCATION . $string;

    // Escape the URL to prevent XSS
    return htmlspecialchars($link, ENT_QUOTES);
}
?>
