<?php

namespace Hatshop\Core\Pipeline;

use Hatshop\Core\Config;
use Hatshop\Core\Customer;
use Hatshop\Core\FeatureFlags;
use Hatshop\Core\Orders;
use Hatshop\Core\SecureCard;

/**
 * Order processor - main class for order pipeline processing (Chapter 13-14).
 *
 * Coordinates order processing through multiple pipeline sections:
 * - Status 0: PsInitialNotification - Send confirmation to customer
 * - Status 1: PsCheckFunds - Verify credit card funds
 * - Status 2: PsCheckStock - Check stock with supplier
 * - Status 3: PsStockOk - Await supplier stock confirmation
 * - Status 4: PsTakePayment - Charge credit card
 * - Status 5: PsShipGoods - Request shipment from supplier
 * - Status 6: PsShipOk - Await shipping confirmation
 * - Status 7: PsFinalNotification - Send dispatch notification
 * - Status 8: Order completed
 */
class OrderProcessor
{
    /** @var string Mail header "From: " prefix */
    private const MAIL_FROM_PREFIX = 'From: ';

    /** @var array Order information */
    public array $orderInfo;

    /** @var array Order details (line items) */
    public array $orderDetailsInfo;

    /** @var array Customer information */
    public array $customerInfo;

    /** @var bool Whether to continue processing to next section */
    public bool $continueNow = false;

    /** @var IPipelineSection|null Current pipeline section */
    private ?IPipelineSection $currentPipelineSection = null;

    /** @var int Current order processing stage */
    private int $orderProcessStage = 0;

    /**
     * Create a new order processor.
     *
     * @param int $orderId The order ID to process
     */
    public function __construct(int $orderId)
    {
        // Get order
        $this->orderInfo = Orders::getOrderInfo($orderId);

        if (empty($this->orderInfo['shipping_id'])) {
            $this->orderInfo['shipping_id'] = -1;
        }

        if (empty($this->orderInfo['tax_id'])) {
            $this->orderInfo['tax_id'] = -1;
        }

        // Get order details
        $this->orderDetailsInfo = Orders::getOrderDetails($orderId);

        // Get customer associated with the processed order
        $this->customerInfo = Customer::get($this->orderInfo['customer_id']);

        // Decrypt credit card if available
        if (!empty($this->customerInfo['credit_card'])) {
            $creditCard = new SecureCard();
            $creditCard->loadEncryptedDataAndDecrypt($this->customerInfo['credit_card']);
            $this->customerInfo['credit_card'] = $creditCard;
        }
    }

    /**
     * Process the order through pipeline sections.
     *
     * Called from checkout or admin to process an order through
     * the appropriate pipeline sections based on order status.
     *
     * @throws OrderPipelineException If processing fails
     */
    public function process(): void
    {
        // Configure processor
        $this->continueNow = true;

        // Log start of execution
        $this->createAudit('Order Processor started.', 10000);

        // Process pipeline sections
        try {
            while ($this->continueNow) {
                $this->continueNow = false;
                $this->getCurrentPipelineSection();

                if ($this->currentPipelineSection !== null) {
                    $this->currentPipelineSection->process($this);
                }
            }
        } catch (OrderPipelineException $e) {
            $this->mailAdmin(
                'Order Processing error occurred.',
                'Exception: "' . $e->getMessage() . '" on ' .
                $e->getFile() . ' line ' . $e->getLine(),
                $this->orderProcessStage
            );

            $this->createAudit('Order Processing error occurred.', 10002);

            throw OrderPipelineException::processingError(
                'Error occurred, order aborted. Details mailed to administrator.'
            );
        }

        $this->createAudit('Order Processor finished.', 10001);
    }

    /**
     * Add an audit message for this order.
     *
     * @param string $message The audit message
     * @param int $messageNumber The message code
     */
    public function createAudit(string $message, int $messageNumber): void
    {
        Orders::createAudit($this->orderInfo['order_id'], $message, $messageNumber);
    }

    /**
     * Send email to administrator.
     *
     * @param string $subject Email subject
     * @param string $message Email body
     * @param int $sourceStage Source pipeline stage
     * @throws OrderPipelineException If mail fails
     */
    public function mailAdmin(string $subject, string $message, int $sourceStage): void
    {
        $to = Config::get('admin_email');
        $headers = self::MAIL_FROM_PREFIX . Config::get('order_processor_email') . "\r\n";
        $body = 'Message: ' . $message . "\n" .
                'Source: ' . $sourceStage . "\n" .
                'Order ID: ' . $this->orderInfo['order_id'];

        $result = @mail($to, $subject, $body, $headers);

        if ($result === false) {
            throw OrderPipelineException::mailAdminFailed($body);
        }
    }

    /**
     * Send email to customer.
     *
     * @param string $subject Email subject
     * @param string $body Email body
     * @throws OrderPipelineException If mail fails
     */
    public function mailCustomer(string $subject, string $body): void
    {
        $to = $this->customerInfo['email'];
        $headers = self::MAIL_FROM_PREFIX . Config::get('customer_service_email') . "\r\n";
        $result = @mail($to, $subject, $body, $headers);

        if ($result === false) {
            throw OrderPipelineException::mailCustomerFailed();
        }
    }

    /**
     * Send email to supplier.
     *
     * @param string $subject Email subject
     * @param string $body Email body
     * @throws OrderPipelineException If mail fails
     */
    public function mailSupplier(string $subject, string $body): void
    {
        $to = Config::get('supplier_email');
        $headers = self::MAIL_FROM_PREFIX . Config::get('order_processor_email') . "\r\n";
        $result = @mail($to, $subject, $body, $headers);

        if ($result === false) {
            throw OrderPipelineException::mailSupplierFailed();
        }
    }

    /**
     * Update the order status.
     *
     * @param int $status New status code
     */
    public function updateOrderStatus(int $status): void
    {
        Orders::updateOrderStatus($this->orderInfo['order_id'], $status);
        $this->orderInfo['status'] = $status;
    }

    /**
     * Set order authorization code and reference.
     *
     * @param string $authCode Authorization code
     * @param string $reference Reference code
     */
    public function setAuthCodeAndReference(string $authCode, string $reference): void
    {
        Orders::setOrderAuthCodeAndReference(
            $this->orderInfo['order_id'],
            $authCode,
            $reference
        );
        $this->orderInfo['auth_code'] = $authCode;
        $this->orderInfo['reference'] = $reference;
    }

    /**
     * Set the order ship date to today.
     */
    public function setDateShipped(): void
    {
        Orders::setDateShipped($this->orderInfo['order_id']);
        $this->orderInfo['shipped_on'] = date('Y-m-d');
    }

    /**
     * Get customer address as a formatted string.
     *
     * @return string Formatted address
     */
    public function getCustomerAddressAsString(): string
    {
        $newLine = "\n";
        $address = $this->customerInfo['name'] . $newLine .
                   $this->customerInfo['address_1'] . $newLine;

        if (!empty($this->customerInfo['address_2'])) {
            $address .= $this->customerInfo['address_2'] . $newLine;
        }

        $address .= $this->customerInfo['city'] . $newLine .
                    $this->customerInfo['region'] . $newLine .
                    $this->customerInfo['postal_code'] . $newLine .
                    $this->customerInfo['country'];

        return $address;
    }

    /**
     * Get order details as a formatted string.
     *
     * @param bool $withCustomerDetails Include customer address and credit card
     * @return string Formatted order details
     */
    public function getOrderAsString(bool $withCustomerDetails = true): string
    {
        $totalCost = 0.00;
        $orderDetails = '';
        $newLine = "\n";

        if ($withCustomerDetails) {
            $orderDetails = 'Customer address:' . $newLine .
                           $this->getCustomerAddressAsString() .
                           $newLine . $newLine;

            if ($this->customerInfo['credit_card'] instanceof SecureCard) {
                $orderDetails .= 'Customer credit card:' . $newLine .
                                $this->customerInfo['credit_card']->getCardNumberX() .
                                $newLine . $newLine;
            }
        }

        foreach ($this->orderDetailsInfo as $orderDetail) {
            $orderDetails .= $orderDetail['quantity'] . ' ' .
                            $orderDetail['product_name'] . ' $' .
                            $orderDetail['unit_cost'] . ' each, total cost $' .
                            number_format($orderDetail['subtotal'], 2, '.', '') . $newLine;

            $totalCost += $orderDetail['subtotal'];
        }

        // Add shipping cost
        if ($this->orderInfo['shipping_id'] != -1) {
            $orderDetails .= 'Shipping: ' . $this->orderInfo['shipping_type'] . $newLine;
            $totalCost += $this->orderInfo['shipping_cost'];
        }

        // Add tax
        if ($this->orderInfo['tax_id'] != -1 &&
            ($this->orderInfo['tax_percentage'] ?? 0) != 0.00) {
            $taxAmount = round(
                (float)$totalCost * (float)$this->orderInfo['tax_percentage'],
                2
            ) / 100.00;

            $orderDetails .= 'Tax: ' . $this->orderInfo['tax_type'] . ', $' .
                            number_format($taxAmount, 2, '.', '') . $newLine;

            $totalCost += $taxAmount;
        }

        $orderDetails .= $newLine . 'Total order cost: $' .
                        number_format($totalCost, 2, '.', '');

        return $orderDetails;
    }

    /**
     * Get the current pipeline section based on order status.
     *
     * @throws Exception If order status is invalid
     */
    private function getCurrentPipelineSection(): void
    {
        $useDummyPipeline = !FeatureFlags::isEnabled(FeatureFlags::FEATURE_ORDER_PIPELINE);

        if ($useDummyPipeline) {
            $this->orderProcessStage = 100;
            $this->currentPipelineSection = new PsDummy();
            return;
        }

        switch ($this->orderInfo['status']) {
            case 0:
                $this->orderProcessStage = $this->orderInfo['status'];
                $this->currentPipelineSection = new PsInitialNotification();
                break;
            case 1:
                $this->orderProcessStage = $this->orderInfo['status'];
                $this->currentPipelineSection = new PsCheckFunds();
                break;
            case 2:
                $this->orderProcessStage = $this->orderInfo['status'];
                $this->currentPipelineSection = new PsCheckStock();
                break;
            case 3:
                $this->orderProcessStage = $this->orderInfo['status'];
                $this->currentPipelineSection = new PsStockOk();
                break;
            case 4:
                $this->orderProcessStage = $this->orderInfo['status'];
                $this->currentPipelineSection = new PsTakePayment();
                break;
            case 5:
                $this->orderProcessStage = $this->orderInfo['status'];
                $this->currentPipelineSection = new PsShipGoods();
                break;
            case 6:
                $this->orderProcessStage = $this->orderInfo['status'];
                $this->currentPipelineSection = new PsShipOk();
                break;
            case 7:
                $this->orderProcessStage = $this->orderInfo['status'];
                $this->currentPipelineSection = new PsFinalNotification();
                break;
            case 8:
                $this->orderProcessStage = 100;
                throw OrderPipelineException::orderCompleted();
            default:
                $this->orderProcessStage = 100;
                throw OrderPipelineException::unknownSection();
        }
    }
}
