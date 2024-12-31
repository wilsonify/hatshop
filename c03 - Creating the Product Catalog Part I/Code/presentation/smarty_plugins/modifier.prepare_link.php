<?php
function smarty_modifier_prepare_link($string)
{
    // Base URL with HTTPS and domain
    $baseUrl = 'https://' . getenv('SERVER_NAME');

    // Split the input into path and query string
    $parts = explode('?', $string, 2);
    $path = $parts[0];
    $query = isset($parts[1]) ? '?' . $parts[1] : '';

    // Ensure the path starts with a slash
    if (!str_starts_with($path, '/')) {
        $path = '/' . $path;
    }

    // Check if the path already contains 'index.php' or 'admin.php'
    if (!str_contains($path, 'index.php') && !str_contains($path, 'admin.php')) {
        $path = rtrim($path, '/') . '/index.php';
    }

    // Construct the final URL
    $fullUrl = $baseUrl . $path . $query;

    // Escape the full URL to prevent XSS
    return htmlspecialchars($fullUrl, ENT_QUOTES);
}
