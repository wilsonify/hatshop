<?php

namespace Hatshop\Core\Tests\Unit;

use Hatshop\Core\SymmetricCrypt;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for SymmetricCrypt class.
 *
 * Note: SymmetricCrypt uses static methods with a fixed IV,
 * so encryption is deterministic (same input = same output).
 */
class SymmetricCryptTest extends TestCase
{
    private string $originalKey;

    protected function setUp(): void
    {
        // Store original key to restore later
        $this->originalKey = 'From Dusk Till Dawn';
    }

    protected function tearDown(): void
    {
        // Restore original key
        SymmetricCrypt::setKey($this->originalKey);
    }

    public function testEncryptDecryptRoundTrip(): void
    {
        $plaintext = 'Hello, World!';

        $encrypted = SymmetricCrypt::encrypt($plaintext);
        $decrypted = SymmetricCrypt::decrypt($encrypted);

        $this->assertEquals($plaintext, $decrypted);
    }

    public function testEncryptProducesHexOutput(): void
    {
        $plaintext = 'Test data';
        $encrypted = SymmetricCrypt::encrypt($plaintext);

        // Output should be valid hexadecimal
        $this->assertMatchesRegularExpression('/^[0-9a-fA-F]+$/', $encrypted);
    }

    public function testEncryptIsDeterministic(): void
    {
        // With fixed IV, same input produces same output
        $plaintext = 'Same text';

        $encrypted1 = SymmetricCrypt::encrypt($plaintext);
        $encrypted2 = SymmetricCrypt::encrypt($plaintext);

        $this->assertEquals($encrypted1, $encrypted2);
    }

    public function testDifferentTextsProduceDifferentCiphertext(): void
    {
        $plaintext1 = 'First message';
        $plaintext2 = 'Second message';

        $encrypted1 = SymmetricCrypt::encrypt($plaintext1);
        $encrypted2 = SymmetricCrypt::encrypt($plaintext2);

        $this->assertNotEquals($encrypted1, $encrypted2);
    }

    public function testDecryptWithDifferentKeyFails(): void
    {
        $plaintext = 'Secret message';
        $encrypted = SymmetricCrypt::encrypt($plaintext);

        // Change key
        SymmetricCrypt::setKey('different_key123');
        $decrypted = SymmetricCrypt::decrypt($encrypted);

        // Decryption with wrong key should not return original
        $this->assertNotEquals($plaintext, $decrypted);
    }

    public function testEncryptEmptyString(): void
    {
        $plaintext = '';

        $encrypted = SymmetricCrypt::encrypt($plaintext);
        $decrypted = SymmetricCrypt::decrypt($encrypted);

        $this->assertEquals($plaintext, $decrypted);
    }

    public function testEncryptLongText(): void
    {
        $plaintext = str_repeat('A', 10000);

        $encrypted = SymmetricCrypt::encrypt($plaintext);
        $decrypted = SymmetricCrypt::decrypt($encrypted);

        $this->assertEquals($plaintext, $decrypted);
    }

    public function testEncryptSpecialCharacters(): void
    {
        $plaintext = "Special chars: !@#$%^&*()_+-=[]{}|;':\",./<>?\nNewlines\tTabs";

        $encrypted = SymmetricCrypt::encrypt($plaintext);
        $decrypted = SymmetricCrypt::decrypt($encrypted);

        $this->assertEquals($plaintext, $decrypted);
    }

    public function testEncryptUnicodeText(): void
    {
        $plaintext = "Unicode: 日本語 中文 한국어 émojis: 🎉🔐";

        $encrypted = SymmetricCrypt::encrypt($plaintext);
        $decrypted = SymmetricCrypt::decrypt($encrypted);

        $this->assertEquals($plaintext, $decrypted);
    }

    public function testSetKeyChangesEncryption(): void
    {
        $originalText = 'Test message';

        // Encrypt with original key
        $encrypted1 = SymmetricCrypt::encrypt($originalText);

        // Change key
        SymmetricCrypt::setKey('new_secret_key12');

        // Encrypt with new key
        $encrypted2 = SymmetricCrypt::encrypt($originalText);

        // Different keys should produce different ciphertext
        $this->assertNotEquals($encrypted1, $encrypted2);

        // Decrypting with new key should work
        $decrypted = SymmetricCrypt::decrypt($encrypted2);
        $this->assertEquals($originalText, $decrypted);
    }

    public function testDecryptInvalidHex(): void
    {
        // Invalid hex should fail gracefully
        $result = @SymmetricCrypt::decrypt('not-valid-hex!!!');

        // The result should be empty or false
        $this->assertEmpty($result);
    }

    public function testDecryptCorruptedData(): void
    {
        $plaintext = 'Test message';
        $encrypted = SymmetricCrypt::encrypt($plaintext);

        // Corrupt the encrypted data by changing characters
        $corrupted = substr($encrypted, 0, -10) . str_repeat('0', 10);
        $result = @SymmetricCrypt::decrypt($corrupted);

        // Should not return original text
        $this->assertNotEquals($plaintext, $result);
    }
}
