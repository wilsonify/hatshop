<?php

namespace Hatshop\Core;

use DOMDocument;

/**
 * Secure credit card storage (Chapter 11).
 *
 * Encrypts and decrypts credit card data using XML format.
 */
class SecureCard
{
    private bool $isDecrypted = false;
    private bool $isEncrypted = false;
    private string $cardHolder = '';
    private string $cardNumber = '';
    private string $issueDate = '';
    private string $expiryDate = '';
    private string $issueNumber = '';
    private string $cardType = '';
    private string $encryptedData = '';

    /**
     * Load encrypted data and decrypt it.
     *
     * @param string $encryptedData Encrypted credit card data
     */
    public function loadEncryptedDataAndDecrypt(string $encryptedData): void
    {
        $this->encryptedData = $encryptedData;
        $this->decryptData();
    }

    /**
     * Load plain data and encrypt it.
     *
     * @param string $cardHolder Card holder name
     * @param string $cardNumber Card number
     * @param string $issueDate Issue date
     * @param string $expiryDate Expiry date
     * @param string $issueNumber Issue number
     * @param string $cardType Card type
     */
    public function loadPlainDataAndEncrypt(
        string $cardHolder,
        string $cardNumber,
        string $issueDate,
        string $expiryDate,
        string $issueNumber,
        string $cardType
    ): void {
        $this->cardHolder = $cardHolder;
        $this->cardNumber = $cardNumber;
        $this->issueDate = $issueDate;
        $this->expiryDate = $expiryDate;
        $this->issueNumber = $issueNumber;
        $this->cardType = $cardType;
        $this->encryptData();
    }

    /**
     * Create XML document with credit card data.
     *
     * @return string XML string
     */
    private function createXml(): string
    {
        $xml = new DOMDocument();
        $root = $xml->createElement('CardDetails');

        $fields = [
            'CardHolder' => $this->cardHolder,
            'CardNumber' => $this->cardNumber,
            'IssueDate' => $this->issueDate,
            'ExpiryDate' => $this->expiryDate,
            'IssueNumber' => $this->issueNumber,
            'CardType' => $this->cardType,
        ];

        foreach ($fields as $name => $value) {
            $child = $xml->createElement($name);
            $child->appendChild($xml->createTextNode($value));
            $root->appendChild($child);
        }

        $xml->appendChild($root);

        return $xml->saveXML();
    }

    /**
     * Extract data from XML.
     *
     * @param string $decryptedData Decrypted XML string
     */
    private function extractXml(string $decryptedData): void
    {
        $xml = simplexml_load_string($decryptedData);
        $this->cardHolder = (string)$xml->CardHolder;
        $this->cardNumber = (string)$xml->CardNumber;
        $this->issueDate = (string)$xml->IssueDate;
        $this->expiryDate = (string)$xml->ExpiryDate;
        $this->issueNumber = (string)$xml->IssueNumber;
        $this->cardType = (string)$xml->CardType;
    }

    /**
     * Encrypt the credit card data.
     */
    private function encryptData(): void
    {
        $xmlData = $this->createXml();
        $this->encryptedData = SymmetricCrypt::encrypt($xmlData);
        $this->isEncrypted = true;
    }

    /**
     * Decrypt the credit card data.
     */
    private function decryptData(): void
    {
        $decryptedData = SymmetricCrypt::decrypt($this->encryptedData);
        $this->extractXml($decryptedData);
        $this->isDecrypted = true;
    }

    /**
     * Get encrypted data.
     *
     * @return string Encrypted data
     * @throws SecureCardException If data not encrypted
     */
    public function getEncryptedData(): string
    {
        if (!$this->isEncrypted) {
            throw new SecureCardException('Data not encrypted');
        }
        return $this->encryptedData;
    }

    /**
     * Get masked card number (XXXX-XXXX-XXXX-1234).
     *
     * @return string Masked card number
     * @throws SecureCardException If data not decrypted
     */
    public function getCardNumberX(): string
    {
        if (!$this->isDecrypted) {
            throw new SecureCardException('Data not decrypted');
        }
        return 'XXXX-XXXX-XXXX-' . substr($this->cardNumber, -4);
    }

    /**
     * Get card holder name.
     *
     * @return string Card holder
     */
    public function getCardHolder(): string
    {
        return $this->cardHolder;
    }

    /**
     * Get card number.
     *
     * @return string Card number
     */
    public function getCardNumber(): string
    {
        return $this->cardNumber;
    }

    /**
     * Get issue date.
     *
     * @return string Issue date
     */
    public function getIssueDate(): string
    {
        return $this->issueDate;
    }

    /**
     * Get expiry date.
     *
     * @return string Expiry date
     */
    public function getExpiryDate(): string
    {
        return $this->expiryDate;
    }

    /**
     * Get issue number.
     *
     * @return string Issue number
     */
    public function getIssueNumber(): string
    {
        return $this->issueNumber;
    }

    /**
     * Get card type.
     *
     * @return string Card type
     */
    public function getCardType(): string
    {
        return $this->cardType;
    }

    /**
     * Convert to array for template use.
     *
     * @return array Card details as array
     */
    public function toArray(): array
    {
        return [
            'card_holder' => $this->cardHolder,
            'card_number' => $this->cardNumber,
            'issue_date' => $this->issueDate,
            'expiry_date' => $this->expiryDate,
            'issue_number' => $this->issueNumber,
            'card_type' => $this->cardType,
            'card_number_x' => $this->isDecrypted ? $this->getCardNumberX() : '',
        ];
    }
}
