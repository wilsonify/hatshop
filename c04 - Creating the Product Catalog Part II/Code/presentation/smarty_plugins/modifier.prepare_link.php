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

    // Parse the URL and append 'index.php' if not already present
    $urlParts = parse_url($link);
    if (
        strpos($link, 'index.php') === false &&
        strpos($link, 'admin.php') === false
    ) {
        $path = isset($urlParts['path']) ? rtrim($urlParts['path'], '/') . '/index.php' : 'index.php';
        $link = $urlParts['scheme'] . '://' . $urlParts['host'] . $path;
        if (isset($urlParts['query'])) {
            $link .= '?' . $urlParts['query'];
        }
    }

    // Escape the URL to prevent XSS
    return htmlspecialchars($link, ENT_QUOTES);
}
