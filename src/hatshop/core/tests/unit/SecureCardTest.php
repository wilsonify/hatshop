<?php

namespace Hatshop\Core\Tests\Unit;

use Hatshop\Core\SecureCard;
use Hatshop\Core\SecureCardException;
use Hatshop\Core\SymmetricCrypt;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for SecureCard class.
 *
 * Note: SecureCard uses static SymmetricCrypt with fixed IV,
 * and loadPlainDataAndEncrypt returns void (stores encrypted data internally).
 */
class SecureCardTest extends TestCase
{
    private const TEST_CARD_HOLDER = 'John Doe';
    private const TEST_CARD_NUMBER = '4111111111111111';
    private const TEST_ISSUE_DATE = '01/20';
    private const TEST_EXPIRY_DATE = '12/25';
    private const TEST_ISSUE_NUMBER = '123';
    private const TEST_CARD_TYPE = 'Visa';

    private string $originalKey = 'From Dusk Till Dawn';

    protected function tearDown(): void
    {
        // Restore original encryption key
        SymmetricCrypt::setKey($this->originalKey);
    }

    private function loadTestCardData(SecureCard $card): void
    {
        $card->loadPlainDataAndEncrypt(
            self::TEST_CARD_HOLDER,
            self::TEST_CARD_NUMBER,
            self::TEST_ISSUE_DATE,
            self::TEST_EXPIRY_DATE,
            self::TEST_ISSUE_NUMBER,
            self::TEST_CARD_TYPE
        );
    }

    public function testLoadPlainDataAndEncryptCreatesEncryptedData(): void
    {
        $card = new SecureCard();
        $this->loadTestCardData($card);

        // Should be able to get encrypted data after loading plain data
        $encrypted = $card->getEncryptedData();
        $this->assertNotEmpty($encrypted);
    }

    public function testLoadEncryptedDataAndDecrypt(): void
    {
        $card1 = new SecureCard();
        $this->loadTestCardData($card1);
        $encrypted = $card1->getEncryptedData();

        // Create new instance and decrypt
        $card2 = new SecureCard();
        $card2->loadEncryptedDataAndDecrypt($encrypted);

        $this->assertEquals(self::TEST_CARD_HOLDER, $card2->getCardHolder());
        $this->assertEquals(self::TEST_CARD_NUMBER, $card2->getCardNumber());
        $this->assertEquals(self::TEST_ISSUE_DATE, $card2->getIssueDate());
        $this->assertEquals(self::TEST_EXPIRY_DATE, $card2->getExpiryDate());
        $this->assertEquals(self::TEST_ISSUE_NUMBER, $card2->getIssueNumber());
        $this->assertEquals(self::TEST_CARD_TYPE, $card2->getCardType());
    }

    public function testEncryptedDataIsNotPlaintext(): void
    {
        $card = new SecureCard();
        $this->loadTestCardData($card);
        $encrypted = $card->getEncryptedData();

        // The card number should not appear in the encrypted data
        $this->assertStringNotContainsString(self::TEST_CARD_NUMBER, $encrypted);
    }

    public function testGetEncryptedDataThrowsExceptionBeforeEncryption(): void
    {
        $card = new SecureCard();

        $this->expectException(SecureCardException::class);
        $card->getEncryptedData();
    }

    public function testGettersReturnEmptyBeforeLoad(): void
    {
        $card = new SecureCard();

        $this->assertEquals('', $card->getCardNumber());
        $this->assertEquals('', $card->getExpiryDate());
        $this->assertEquals('', $card->getIssueDate());
        $this->assertEquals('', $card->getIssueNumber());
        $this->assertEquals('', $card->getCardType());
        $this->assertEquals('', $card->getCardHolder());
    }

    public function testEncryptionIsDeterministic(): void
    {
        // With fixed IV, same input produces same output
        $card1 = new SecureCard();
        $this->loadTestCardData($card1);
        $encrypted1 = $card1->getEncryptedData();

        $card2 = new SecureCard();
        $this->loadTestCardData($card2);
        $encrypted2 = $card2->getEncryptedData();

        $this->assertEquals($encrypted1, $encrypted2);
    }

    public function testSpecialCharactersInCardHolder(): void
    {
        $card = new SecureCard();
        $specialName = "O'Brien & Co.";

        $card->loadPlainDataAndEncrypt(
            $specialName,
            self::TEST_CARD_NUMBER,
            self::TEST_ISSUE_DATE,
            self::TEST_EXPIRY_DATE,
            self::TEST_ISSUE_NUMBER,
            self::TEST_CARD_TYPE
        );

        $encrypted = $card->getEncryptedData();

        $card2 = new SecureCard();
        $card2->loadEncryptedDataAndDecrypt($encrypted);

        $this->assertEquals($specialName, $card2->getCardHolder());
    }

    public function testGetCardNumberXReturnsMaskedNumber(): void
    {
        $card = new SecureCard();
        $this->loadTestCardData($card);
        $encrypted = $card->getEncryptedData();

        $card2 = new SecureCard();
        $card2->loadEncryptedDataAndDecrypt($encrypted);

        $masked = $card2->getCardNumberX();
        $this->assertStringContainsString('XXXX', $masked);
        $this->assertStringContainsString('1111', $masked);
    }

    public function testToArrayReturnsAllFields(): void
    {
        $card = new SecureCard();
        $this->loadTestCardData($card);
        $encrypted = $card->getEncryptedData();

        $card2 = new SecureCard();
        $card2->loadEncryptedDataAndDecrypt($encrypted);

        $array = $card2->toArray();

        $this->assertArrayHasKey('card_holder', $array);
        $this->assertArrayHasKey('card_number', $array);
        $this->assertArrayHasKey('issue_date', $array);
        $this->assertArrayHasKey('expiry_date', $array);
        $this->assertArrayHasKey('issue_number', $array);
        $this->assertArrayHasKey('card_type', $array);
        $this->assertArrayHasKey('card_number_x', $array);
    }
}
