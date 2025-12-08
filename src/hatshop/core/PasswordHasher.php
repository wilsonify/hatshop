<?php

namespace Hatshop\Core;

/**
 * Password hashing utility (Chapter 11).
 *
 * Provides secure password hashing using SHA-1 with optional prefix.
 */
class PasswordHasher
{
    /** @var string Hash prefix for additional security */
    private static string $hashPrefix = '';

    /**
     * Hash a password.
     *
     * @param string $password Plain text password
     * @param bool $withPrefix Whether to use hash prefix
     * @return string Hashed password
     */
    public static function hash(string $password, bool $withPrefix = true): string
    {
        if ($withPrefix && self::$hashPrefix !== '') {
            return hash('sha256', self::$hashPrefix . $password);
        }

        return hash('sha256', $password);
    }

    /**
     * Verify a password against a hash.
     *
     * @param string $password Plain text password
     * @param string $hash Hash to verify against
     * @param bool $withPrefix Whether prefix was used
     * @return bool True if password matches
     */
    public static function verify(string $password, string $hash, bool $withPrefix = true): bool
    {
        return self::hash($password, $withPrefix) === $hash;
    }

    /**
     * Set the hash prefix.
     *
     * @param string $prefix Prefix to use
     */
    public static function setPrefix(string $prefix): void
    {
        self::$hashPrefix = $prefix;
    }

    /**
     * Get the current hash prefix.
     *
     * @return string Current prefix
     */
    public static function getPrefix(): string
    {
        return self::$hashPrefix;
    }
}
