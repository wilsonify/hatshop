<?php

namespace Hatshop\Core;

/**
 * Business tier class for customer management (Chapter 11).
 *
 * Provides methods for customer authentication, registration, and profile management.
 */
class Customer
{
    /**
     * Check if a customer is authenticated.
     *
     * @return bool True if authenticated
     */
    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['hatshop_customer_id']);
    }

    /**
     * Get login info for a customer by email.
     *
     * @param string $email Customer email
     * @return array|null Customer login info or null
     */
    public static function getLoginInfo(string $email): ?array
    {
        $sql = 'SELECT * FROM customer_get_login_info(:email);';
        $params = [':email' => $email];
        $result = DatabaseHandler::prepare($sql);

        return DatabaseHandler::getRow($result, $params);
    }

    /**
     * Validate customer credentials.
     *
     * @param string $email Customer email
     * @param string $password Customer password
     * @return int 0=valid, 1=wrong password, 2=unrecognized email
     */
    public static function isValid(string $email, string $password): int
    {
        $customer = self::getLoginInfo($email);

        if (empty($customer['customer_id'])) {
            return 2; // Unrecognized email
        }

        $hashedPassword = $customer['password'];

        if (!PasswordHasher::verify($password, $hashedPassword)) {
            return 1; // Wrong password
        }

        $_SESSION['hatshop_customer_id'] = $customer['customer_id'];
        return 0; // Valid
    }

    /**
     * Logout the current customer.
     */
    public static function logout(): void
    {
        unset($_SESSION['hatshop_customer_id']);
    }

    /**
     * Get the current customer ID.
     *
     * @return int Customer ID or 0 if not authenticated
     */
    public static function getCurrentCustomerId(): int
    {
        if (self::isAuthenticated()) {
            return (int)$_SESSION['hatshop_customer_id'];
        }
        return 0;
    }

    /**
     * Add a new customer.
     *
     * @param string $name Customer name
     * @param string $email Customer email
     * @param string $password Customer password
     * @param bool $addAndLogin Whether to log in after adding
     * @return int New customer ID
     */
    public static function add(
        string $name,
        string $email,
        string $password,
        bool $addAndLogin = true
    ): int {
        $hashedPassword = PasswordHasher::hash($password);

        $sql = 'SELECT customer_add(:name, :email, :password);';
        $params = [
            ':name' => $name,
            ':email' => $email,
            ':password' => $hashedPassword,
        ];
        $result = DatabaseHandler::prepare($sql);

        $customerId = (int)DatabaseHandler::getOne($result, $params);

        if ($addAndLogin) {
            $_SESSION['hatshop_customer_id'] = $customerId;
        }

        return $customerId;
    }

    /**
     * Get customer details.
     *
     * @param int|null $customerId Customer ID (null for current)
     * @return array|null Customer data
     */
    public static function get(?int $customerId = null): ?array
    {
        if ($customerId === null) {
            $customerId = self::getCurrentCustomerId();
        }

        $sql = 'SELECT * FROM customer_get_customer(:customer_id);';
        $params = [':customer_id' => $customerId];
        $result = DatabaseHandler::prepare($sql);

        return DatabaseHandler::getRow($result, $params);
    }

    /**
     * Update customer account details.
     *
     * @param string $name Customer name
     * @param string $email Customer email
     * @param string $password New password
     * @param string|null $dayPhone Day phone
     * @param string|null $evePhone Evening phone
     * @param string|null $mobPhone Mobile phone
     * @param int|null $customerId Customer ID (null for current)
     */
    public static function updateAccountDetails(
        string $name,
        string $email,
        string $password,
        ?string $dayPhone = null,
        ?string $evePhone = null,
        ?string $mobPhone = null,
        ?int $customerId = null
    ): void {
        if ($customerId === null) {
            $customerId = self::getCurrentCustomerId();
        }

        $hashedPassword = PasswordHasher::hash($password);

        $sql = 'SELECT customer_update_account(:customer_id, :name, :email,
                     :password, :day_phone, :eve_phone, :mob_phone);';
        $params = [
            ':customer_id' => $customerId,
            ':name' => $name,
            ':email' => $email,
            ':password' => $hashedPassword,
            ':day_phone' => $dayPhone,
            ':eve_phone' => $evePhone,
            ':mob_phone' => $mobPhone,
        ];
        $result = DatabaseHandler::prepare($sql);

        DatabaseHandler::execute($result, $params);
    }

    /**
     * Decrypt credit card data.
     *
     * @param string $encryptedCreditCard Encrypted credit card
     * @return array Decrypted credit card details
     */
    public static function decryptCreditCard(string $encryptedCreditCard): array
    {
        $secureCard = new SecureCard();
        $secureCard->loadEncryptedDataAndDecrypt($encryptedCreditCard);

        return $secureCard->toArray();
    }

    /**
     * Get plain credit card for current customer.
     *
     * @return array Credit card details (empty if none)
     */
    public static function getPlainCreditCard(): array
    {
        $customerData = self::get();

        if (!empty($customerData['credit_card'])) {
            return self::decryptCreditCard($customerData['credit_card']);
        }

        return [
            'card_holder' => '',
            'card_number' => '',
            'issue_date' => '',
            'expiry_date' => '',
            'issue_number' => '',
            'card_type' => '',
            'card_number_x' => '',
        ];
    }

    /**
     * Update credit card details.
     *
     * @param array $plainCreditCard Plain credit card data
     * @param int|null $customerId Customer ID (null for current)
     */
    public static function updateCreditCardDetails(
        array $plainCreditCard,
        ?int $customerId = null
    ): void {
        if ($customerId === null) {
            $customerId = self::getCurrentCustomerId();
        }

        $secureCard = new SecureCard();
        $secureCard->loadPlainDataAndEncrypt(
            $plainCreditCard['card_holder'],
            $plainCreditCard['card_number'],
            $plainCreditCard['issue_date'] ?? '',
            $plainCreditCard['expiry_date'],
            $plainCreditCard['issue_number'] ?? '',
            $plainCreditCard['card_type']
        );
        $encryptedCard = $secureCard->getEncryptedData();

        $sql = 'SELECT customer_update_credit_card(:customer_id, :credit_card);';
        $params = [
            ':customer_id' => $customerId,
            ':credit_card' => $encryptedCard,
        ];
        $result = DatabaseHandler::prepare($sql);

        DatabaseHandler::execute($result, $params);
    }

    /**
     * Get available shipping regions.
     *
     * @return array Shipping regions
     */
    public static function getShippingRegions(): array
    {
        $sql = 'SELECT * FROM customer_get_shipping_regions();';
        $result = DatabaseHandler::prepare($sql);

        return DatabaseHandler::getAll($result);
    }

    /**
     * Update customer address details.
     *
     * @param array<string, mixed> $addressData Address data containing:
     *   - address1: string (required)
     *   - address2: string|null (optional)
     *   - city: string (required)
     *   - region: string (required)
     *   - postalCode: string (required)
     *   - country: string (required)
     *   - shippingRegionId: int (required)
     * @param int|null $customerId Customer ID (null for current)
     */
    public static function updateAddressDetails(array $addressData, ?int $customerId = null): void
    {
        if ($customerId === null) {
            $customerId = self::getCurrentCustomerId();
        }

        $sql = 'SELECT customer_update_address(:customer_id, :address_1,
                     :address_2, :city, :region, :postal_code, :country,
                     :shipping_region_id);';
        $params = [
            ':customer_id' => $customerId,
            ':address_1' => $addressData['address1'] ?? '',
            ':address_2' => $addressData['address2'] ?? null,
            ':city' => $addressData['city'] ?? '',
            ':region' => $addressData['region'] ?? '',
            ':postal_code' => $addressData['postalCode'] ?? '',
            ':country' => $addressData['country'] ?? '',
            ':shipping_region_id' => $addressData['shippingRegionId'] ?? 0,
        ];
        $result = DatabaseHandler::prepare($sql);

        DatabaseHandler::execute($result, $params);
    }
}
