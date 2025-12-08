<?php

namespace Hatshop\Core;

/**
 * Symmetric encryption/decryption utility (Chapter 11).
 *
 * Uses AES-128-CBC for encrypting sensitive data like credit cards.
 */
class SymmetricCrypt
{
    /** @var string Encryption key - should be overridden via config */
    private static string $secretKey = 'From Dusk Till Dawn';

    /** @var string Initialization vector in hexadecimal */
    private static string $hexaIv = 'c7098adc8d6128b5d4b4f7b2fe7f7f05';

    /** @var string Cipher algorithm */
    private static string $cipherAlgorithm = 'aes-128-cbc';

    /**
     * Encrypt a plain text string.
     *
     * @param string $plainString Plain text to encrypt
     * @return string Encrypted string in hexadecimal format
     */
    public static function encrypt(string $plainString): string
    {
        $binaryIv = pack('H*', self::$hexaIv);

        $encryptedString = openssl_encrypt(
            $plainString,
            self::$cipherAlgorithm,
            self::$secretKey,
            0,
            $binaryIv
        );

        return bin2hex($encryptedString);
    }

    /**
     * Decrypt a hexadecimal encrypted string.
     *
     * @param string $encryptedString Encrypted string in hexadecimal
     * @return string Decrypted plain text
     */
    public static function decrypt(string $encryptedString): string
    {
        $binaryEncrypted = hex2bin($encryptedString);
        $binaryIv = pack('H*', self::$hexaIv);

        return openssl_decrypt(
            $binaryEncrypted,
            self::$cipherAlgorithm,
            self::$secretKey,
            0,
            $binaryIv
        );
    }

    /**
     * Set custom encryption key (useful for production).
     *
     * @param string $key New encryption key
     */
    public static function setKey(string $key): void
    {
        self::$secretKey = $key;
    }
}
