<?php

class SymmetricCrypt
{
    // Encryption/decryption key
    private static $_msSecretKey = 'From Dusk Till Dawn';

    // The initialization vector (should be 16 bytes for AES-128)
    private static $_msHexaIv = 'c7098adc8d6128b5d4b4f7b2fe7f7f05';

    // Use AES encryption with CBC mode
    private static $_msCipherAlgorithm = 'aes-128-cbc';

    /* Function encrypts plain-text string received as parameter
       and returns the result in hexadecimal format */
    public static function Encrypt($plainString)
    {
        // Pack the IV from hexadecimal to binary
        $binary_iv = pack('H*', self::$_msHexaIv);

        // Encrypt the string using OpenSSL
        $encrypted_string = openssl_encrypt(
            $plainString,
            self::$_msCipherAlgorithm,
            self::$_msSecretKey,
            0,
            $binary_iv
        );

        // Return the encrypted string in hexadecimal format
        return bin2hex($encrypted_string);
    }

    /* Function decrypts hexadecimal string received as parameter
       and returns the decrypted result */
    public static function Decrypt($encryptedString)
    {
        // Convert the hexadecimal encrypted string to binary
        $binary_encrypted_string = hex2bin($encryptedString);

        // Pack the IV from hexadecimal to binary
        $binary_iv = pack('H*', self::$_msHexaIv);

        // Decrypt the string using OpenSSL
        $decrypted_string = openssl_decrypt(
            $binary_encrypted_string,
            self::$_msCipherAlgorithm,
            self::$_msSecretKey,
            0,
            $binary_iv
        );

        return $decrypted_string;
    }
}
