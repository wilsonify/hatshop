<?php
/**
 * Smarty modifier to prepare asset URLs with the correct path prefix.
 * Usage in templates: {$path|asset_url}
 * Example: {"hatshop.css"|asset_url} -> /dev/hatshop.css
 */

/**
 * Prepend the path prefix to static asset URLs (CSS, JS, images).
 *
 * @param string $path The relative path to the asset
 * @return string The full path with prefix
 */
function smarty_modifier_asset_url($path)
{
    $prefix = getenv('HATSHOP_PATH_PREFIX');
    if ($prefix === false) {
        $prefix = '';
    }
    $prefix = trim($prefix, '/');
    $cleanPath = trim($path, '/');
    
    if (!empty($prefix)) {
        return '/' . $prefix . '/' . $cleanPath;
    }
    return '/' . $cleanPath;
}
