<?php
// Class for accessing ECS
class Amazon
{
  public function Amazon()
  {
  }

  // Retrieves Amazon products for sending to presentation tier
  public function GetProducts()
  {
    // Use SOAP to get data
    if (AMAZON_METHOD == 'SOAP')
      $result = $this->GetDataWithSoap();
    // Use REST to get data
    else
      $result = $this->GetDataWithRest();

    // Initializes Array object
    $results = array ();

    // Format results
    $results = $this->DataFormat($result);

    // Returns results
    return $results;
  }

  // Call ECS using REST
  private function GetDataWithRest()
  {
    $params = array ('Operation'      => 'ItemSearch',
                     'SubscriptionId' => AMAZON_ACCESS_KEY_ID,
                     'Keywords'       => AMAZON_SEARCH_KEYWORDS,
                     'SearchIndex'    => AMAZON_SEARCH_NODE,
                     'ResponseGroup'  => AMAZON_RESPONSE_GROUPS,
                     'Sort'           => 'salesrank');

    $query_string = '&';
    foreach ($params as $key => $value)
      $query_string .= $key . '=' . urlencode($value) . '&';

    $amazon_url = AMAZON_REST_BASE_URL . $query_string;

    // Get the XML response using REST
    $amazon_xml = file_get_contents($amazon_url);

    // Unserialize the XML and return
    return simplexml_load_string($amazon_xml);
  }

  // Call ECS using SOAP
  private function GetDataWithSoap()
  {
    try
    {
      $client = new SoapClient(AMAZON_WSDL);

      /* Set up an array containing input parameters to be
         passed to the remote procedure */
      $request = array ('SubscriptionId' => AMAZON_ACCESS_KEY_ID,
                        'Request' => array ('Operation' => 'ItemSearchRequest',
                                            'Keywords' => 
                                              AMAZON_SEARCH_KEYWORDS,
                                            'SearchIndex' => AMAZON_SEARCH_NODE,
                                            'ResponseGroup' => 
                                              AMAZON_RESPONSE_GROUPS,
                                            'Sort' => 'salesrank'));

      // Invoke the method
      $result = $client->ItemSearch($request);

      return $result;
    }
    catch (SoapFault $fault)
    {
      trigger_error('SOAP Fault: (faultcode: ' . $fault->faultcode . ', ' .
                    'faultstring: ' . $fault->faultstring . ')', E_USER_ERROR);
    }
  }

  /* Places an "image not available" picture for products with no image,
     and saves the results in an array with a simple structure for easier
     handling at the upper levels */
  private function DataFormat($result)
  {
    /* Variable k is the index of the $new_result array, which will
       contain the Amazon products to be displayed in HatShop */
    $k = 0;

    $new_result = array ();

    /* Analyze all products retrieved from ECS
       and save them into the $new_result array */
    for ($i = 0; $i < count($result->Items->Item); $i++)
    {
      // Make a temporary copy for product data
      $temp = $result->Items->Item[$i];

      /* Set product's image to images/not_available.jpg,
         if image url is empty */
      if (property_exists($temp, 'SmallImage') &&
          ((string) $temp->SmallImage->URL) != '')
        $new_result[$k]['image'] = (string) $temp->SmallImage->URL;
      else
        $new_result[$k]['image'] = 'images/not_available.jpg';

      // Save asin, brand, name, and price into the $new_result array
      $new_result[$k]['asin'] = (string) $temp->ASIN;
      $new_result[$k]['brand'] = (string) $temp->ItemAttributes->Brand;
      $new_result[$k]['item_name'] = (string) $temp->ItemAttributes->Title;

      if (property_exists($temp->OfferSummary, 'LowestNewPrice'))
        $new_result[$k]['price'] =
          (string) $temp->OfferSummary->LowestNewPrice->FormattedPrice;
      elseif (property_exists($temp->ItemAttributes, 'ListPrice'))
        $new_result[$k]['price'] =
          (string) $temp->ItemAttributes->ListPrice->FormattedPrice;
      else
        $new_result[$k]['price'] = '';

      $k++;
    }

    return $new_result;
  }
}

