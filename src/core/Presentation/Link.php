<?php

namespace Hatshop\Core\Presentation;

use Hatshop\Core\Config;

/**
 * Link utility class for URL preparation.
 */
class Link
{
    /**
     * Prepare a link by adding the base URL and ensuring proper format.
     *
     * @param string $string The relative path
     * @return string The prepared URL
     */
    public static function prepareLink(string $string): string
    {
        $baseLink = self::generateBaseLink();
        $link = self::appendPathToLink($baseLink, $string);
        $link = self::appendIndexIfNeeded($link);
        return self::escapeUrl($link);
    }

    /**
     * Generate the base URL with HTTPS and correct domain.
     */
    private static function generateBaseLink(): string
    {
        $host = Config::get('http_server_host') ?: getenv('HATSHOP_HTTP_SERVER_HOST');
        return 'https://' . $host;
    }

    /**
     * Join paths ensuring proper slashes.
     */
    private static function joinPaths(string ...$paths): string
    {
        return implode(DIRECTORY_SEPARATOR, array_map([self::class, 'trimPath'], $paths));
    }

    /**
     * Trim leading/trailing slashes from a path.
     */
    private static function trimPath(string $path): string
    {
        return trim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * Append path to the base link.
     */
    private static function appendPathToLink(string $baseLink, string $string): string
    {
        return self::joinPaths($baseLink, $string);
    }

    /**
     * Check if 'index.php' or 'admin.php' are in the link.
     */
    private static function needsIndexPage(string $link): bool
    {
        return strpos($link, 'index.php') === false && strpos($link, 'admin.php') === false;
    }

    /**
     * Add 'index.php' to the link if necessary.
     */
    private static function appendIndexIfNeeded(string $link): string
    {
        if (self::needsIndexPage($link)) {
            $urlParts = parse_url($link);
            $path = isset($urlParts['path']) ? rtrim($urlParts['path'], '/') . '/index.php' : 'index.php';
            $link = $urlParts['scheme'] . '://' . $urlParts['host'] . $path;
            if (isset($urlParts['query'])) {
                $link .= '?' . $urlParts['query'];
            }
        }
        return $link;
    }

    /**
     * Escape URL to prevent XSS.
     */
    private static function escapeUrl(string $link): string
    {
        return htmlspecialchars($link, ENT_QUOTES);
    }
}
