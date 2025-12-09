<?php
/**
 * Smarty modifier to prepare links with the correct host, protocol, and path prefix.
 */

use Hatshop\Core\Config;

// Function to join multiple paths, ensuring proper slashes
function joinPaths(...$paths)
{
    return join('/', array_map('trimPath', array_filter($paths)));
}

// Function to trim leading/trailing slashes from a path
function trimPath($path)
{
    return trim($path, '/');
}

// Function to get the path prefix from configuration
function getPathPrefix()
{
    $prefix = getenv('HATSHOP_PATH_PREFIX');
    if ($prefix === false) {
        $prefix = '';
    }
    return trim($prefix, '/');
}

// Function to generate the base link with HTTPS and the correct domain
function generateBaseLink()
{
    $host = getenv('HATSHOP_HTTP_SERVER_HOST') ?: 'localhost';
    $prefix = getPathPrefix();
    $basePath = 'https://' . $host;
    if (!empty($prefix)) {
        $basePath .= '/' . $prefix;
    }
    return $basePath;
}

// Function to append path to the base link
function appendPathToLink($baseLink, $string)
{
    return $baseLink . '/' . trimPath($string);
}

// Function to check if 'index.php' or 'admin.php' are in the link
function needsIndexPage($link)
{
    return strpos($link, 'index.php') === false && strpos($link, 'admin.php') === false;
}

// Function to add 'index.php' to the link if necessary
function appendIndexIfNeeded($link)
{
    // Check if 'index.php' is already in the path to avoid appending it twice
    if (needsIndexPage($link)) {
        $urlParts = parse_url($link);
        $path = isset($urlParts['path']) ? rtrim($urlParts['path'], '/') . '/index.php' : 'index.php';
        $link = $urlParts['scheme'] . '://' . $urlParts['host'] . $path;
        if (isset($urlParts['query'])) {
            $link .= '?' . $urlParts['query'];
        }
    }
    return $link;
}

// Function to escape the URL to prevent XSS
function escapeUrl($link)
{
    return htmlspecialchars($link, ENT_QUOTES);
}

// Main function to prepare the link
function smarty_modifier_prepare_link($string)
{
    $baseLink = generateBaseLink();
    $link = appendPathToLink($baseLink, $string);
    $link = appendIndexIfNeeded($link);
    return escapeUrl($link);
}
