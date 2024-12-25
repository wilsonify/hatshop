<?php

function joinPaths(...$paths) {
    return join(DIRECTORY_SEPARATOR, array_map(function($path) {
        return trim($path, DIRECTORY_SEPARATOR);  // Remove leading/trailing slashes
    }, $paths));
}

// Plugin functions inside plugin files must be named: smarty_type_name
function smarty_modifier_prepare_link($string)
{
    // Always use HTTPS and the correct domain
    $link = 'https://' . getenv('SERVER_NAME');

    // Use joinPaths to handle appending the path, ensuring proper slashes
    $link = joinPaths($link, $string);

    // Check for 'admin.php' or 'index.php' and handle accordingly
    if (strpos($link, 'index.php') === false && strpos($link, 'admin.php') === false) {
        $link = joinPaths($link, 'index.php');  // Add 'index.php' only if it's not already in the path
    }

    // Escape the URL to prevent XSS
    return htmlspecialchars($link, ENT_QUOTES);
}
?>
