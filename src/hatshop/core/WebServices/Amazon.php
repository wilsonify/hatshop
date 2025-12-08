<?php

namespace Hatshop\Core\WebServices;

use Hatshop\Core\Config;
use SoapClient;
use SoapFault;

/**
 * Amazon Product Advertising API client (Chapter 17).
 *
 * Retrieves product recommendations from Amazon using REST or SOAP.
 */
class Amazon
{
    private const METHOD_REST = 'REST';
    private const METHOD_SOAP = 'SOAP';

    /**
     * Get products from Amazon.
     *
     * @return array Array of product data
     */
    public function getProducts(): array
    {
        $method = Config::get('amazon_method', self::METHOD_REST);

        // Use SOAP to get data
        if ($method === self::METHOD_SOAP) {
            $result = $this->getDataWithSoap();
        } else {
            // Use REST to get data
            $result = $this->getDataWithRest();
        }

        if ($result === null) {
            return [];
        }

        // Format results
        return $this->dataFormat($result);
    }

    /**
     * Call Amazon API using REST.
     *
     * @return object|null Response object or null on failure
     */
    private function getDataWithRest(): ?object
    {
        $params = [
            'Operation' => 'ItemSearch',
            'SubscriptionId' => Config::get('amazon_access_key_id'),
            'Keywords' => Config::get('amazon_search_keywords'),
            'SearchIndex' => Config::get('amazon_search_node'),
            'ResponseGroup' => Config::get('amazon_response_groups'),
            'Sort' => 'salesrank',
        ];

        $queryString = '&';
        foreach ($params as $key => $value) {
            $queryString .= $key . '=' . urlencode((string)$value) . '&';
        }

        $amazonUrl = Config::get('amazon_rest_base_url') . $queryString;

        // Get the XML response using REST
        $amazonXml = @file_get_contents($amazonUrl);

        if ($amazonXml === false) {
            return null;
        }

        // Unserialize the XML and return
        $result = @simplexml_load_string($amazonXml);

        return $result !== false ? $result : null;
    }

    /**
     * Call Amazon API using SOAP.
     *
     * @return object|null Response object or null on failure
     */
    private function getDataWithSoap(): ?object
    {
        try {
            $client = new SoapClient(Config::get('amazon_wsdl'));

            $request = [
                'SubscriptionId' => Config::get('amazon_access_key_id'),
                'Request' => [
                    'Operation' => 'ItemSearchRequest',
                    'Keywords' => Config::get('amazon_search_keywords'),
                    'SearchIndex' => Config::get('amazon_search_node'),
                    'ResponseGroup' => Config::get('amazon_response_groups'),
                    'Sort' => 'salesrank',
                ],
            ];

            return $client->ItemSearch($request);
        } catch (SoapFault $fault) {
            trigger_error(
                'SOAP Fault: (faultcode: ' . $fault->faultcode . ', ' .
                'faultstring: ' . $fault->faultstring . ')',
                E_USER_WARNING
            );
            return null;
        }
    }

    /**
     * Format Amazon response data for display.
     *
     * @param object $result Raw Amazon response
     * @return array Formatted product array
     */
    private function dataFormat(object $result): array
    {
        $newResult = [];

        if (!isset($result->Items->Item)) {
            return $newResult;
        }

        $items = is_array($result->Items->Item)
            ? $result->Items->Item
            : [$result->Items->Item];

        $k = 0;
        foreach ($items as $item) {
            // Skip items without required data
            if (!isset($item->ASIN) || !isset($item->DetailPageURL)) {
                continue;
            }

            $newResult[$k] = [
                'asin' => (string)$item->ASIN,
                'title' => isset($item->ItemAttributes->Title)
                    ? (string)$item->ItemAttributes->Title
                    : 'Unknown',
                'price' => $this->extractPrice($item),
                'image_url' => $this->extractImageUrl($item),
                'detail_url' => (string)$item->DetailPageURL,
            ];

            $k++;
        }

        return $newResult;
    }

    /**
     * Extract price from item data.
     *
     * @param object $item Amazon item
     * @return string Formatted price
     */
    private function extractPrice(object $item): string
    {
        if (isset($item->ItemAttributes->ListPrice->FormattedPrice)) {
            return (string)$item->ItemAttributes->ListPrice->FormattedPrice;
        }

        if (isset($item->OfferSummary->LowestNewPrice->FormattedPrice)) {
            return (string)$item->OfferSummary->LowestNewPrice->FormattedPrice;
        }

        return 'Price not available';
    }

    /**
     * Extract image URL from item data.
     *
     * @param object $item Amazon item
     * @return string Image URL
     */
    private function extractImageUrl(object $item): string
    {
        $noImageUrl = Config::get('amazon_no_image_url', '/images/not_available.jpg');

        if (isset($item->MediumImage->URL)) {
            return (string)$item->MediumImage->URL;
        }

        if (isset($item->SmallImage->URL)) {
            return (string)$item->SmallImage->URL;
        }

        return $noImageUrl;
    }
}
