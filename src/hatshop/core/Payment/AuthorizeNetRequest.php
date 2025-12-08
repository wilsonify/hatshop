<?php

namespace Hatshop\Core\Payment;

use Hatshop\Core\Config;

/**
 * Authorize.net payment gateway request (Chapter 15).
 *
 * Handles credit card transactions via the Authorize.net API.
 */
class AuthorizeNetRequest
{
    /** @var string Authorize.net API URL */
    private string $url;

    /** @var string Current request data */
    private string $request = '';

    /**
     * Create a new Authorize.net request.
     *
     * @param string|null $url Optional URL override (uses config if null)
     */
    public function __construct(?string $url = null)
    {
        $this->url = $url ?? Config::get('authorize_net_url', 'https://secure.authorize.net/gateway/transact.dll');
    }

    /**
     * Set request parameters.
     *
     * @param array $request Request parameters
     */
    public function setRequest(array $request): void
    {
        $this->request = '';

        $requestInit = [
            'x_login' => Config::get('authorize_net_login_id'),
            'x_tran_key' => Config::get('authorize_net_transaction_key'),
            'x_version' => '3.1',
            'x_test_request' => Config::get('authorize_net_test_request', 'TRUE'),
            'x_delim_data' => 'TRUE',
            'x_delim_char' => '|',
            'x_relay_response' => 'FALSE',
        ];

        $request = array_merge($requestInit, $request);

        foreach ($request as $key => $value) {
            $this->request .= $key . '=' . urlencode((string)$value) . '&';
        }
    }

    /**
     * Send the request and get the response.
     *
     * @return string|false Response string or false on failure
     */
    public function getResponse(): string|false
    {
        // Initialize CURL session
        $ch = curl_init();

        // Prepare for HTTP POST request
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim($this->request, '& '));
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Perform CURL session
        $result = curl_exec($ch);

        // Close CURL session
        curl_close($ch);

        return $result;
    }

    /**
     * Create an authorization request (check funds).
     *
     * @param string $cardNumber Credit card number
     * @param string $expiryDate Expiry date (MMYY format)
     * @param float $amount Amount to authorize
     * @param string $description Transaction description
     * @return array Response parsed into array
     */
    public function authorize(
        string $cardNumber,
        string $expiryDate,
        float $amount,
        string $description = ''
    ): array {
        $this->setRequest([
            'x_type' => 'AUTH_ONLY',
            'x_card_num' => $cardNumber,
            'x_exp_date' => $expiryDate,
            'x_amount' => number_format($amount, 2, '.', ''),
            'x_description' => $description,
        ]);

        return $this->parseResponse($this->getResponse());
    }

    /**
     * Create a capture request (take payment).
     *
     * @param string $transactionId Previous authorization transaction ID
     * @param float $amount Amount to capture
     * @return array Response parsed into array
     */
    public function capture(string $transactionId, float $amount): array
    {
        $this->setRequest([
            'x_type' => 'PRIOR_AUTH_CAPTURE',
            'x_trans_id' => $transactionId,
            'x_amount' => number_format($amount, 2, '.', ''),
        ]);

        return $this->parseResponse($this->getResponse());
    }

    /**
     * Create an auth-capture request (authorize and capture in one step).
     *
     * @param string $cardNumber Credit card number
     * @param string $expiryDate Expiry date (MMYY format)
     * @param float $amount Amount to charge
     * @param string $description Transaction description
     * @return array Response parsed into array
     */
    public function authCapture(
        string $cardNumber,
        string $expiryDate,
        float $amount,
        string $description = ''
    ): array {
        $this->setRequest([
            'x_type' => 'AUTH_CAPTURE',
            'x_card_num' => $cardNumber,
            'x_exp_date' => $expiryDate,
            'x_amount' => number_format($amount, 2, '.', ''),
            'x_description' => $description,
        ]);

        return $this->parseResponse($this->getResponse());
    }

    /**
     * Parse the pipe-delimited response.
     *
     * @param string|false $response Raw response string
     * @return array Parsed response
     */
    private function parseResponse(string|false $response): array
    {
        if ($response === false) {
            return [
                'success' => false,
                'response_code' => 0,
                'response_reason_code' => 0,
                'response_reason_text' => 'Connection failed',
                'auth_code' => '',
                'trans_id' => '',
            ];
        }

        $parts = explode('|', $response);

        return [
            'success' => isset($parts[0]) && $parts[0] === '1',
            'response_code' => (int)($parts[0] ?? 0),
            'response_subcode' => (int)($parts[1] ?? 0),
            'response_reason_code' => (int)($parts[2] ?? 0),
            'response_reason_text' => $parts[3] ?? '',
            'auth_code' => $parts[4] ?? '',
            'avs_result_code' => $parts[5] ?? '',
            'trans_id' => $parts[6] ?? '',
            'raw_response' => $response,
        ];
    }
}
