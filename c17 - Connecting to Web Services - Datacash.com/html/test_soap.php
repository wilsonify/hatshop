<?php
try
{
  // Initialize SOAP client object
  $client = new SoapClient(
    'https://webservices.amazon.com/AWSECommerceService/AWSECommerceService.wsdl');

  /* DON'T FORGET to replace the string '[Your Access Key ID]' with your 
     subscription ID in the following line */
  $request = array ('Service' => 'AWSECommerceService',
                    'AWSAccessKeyId' => '[Your Access Key ID]',
                    'Request' => array ('Operation' => 'ItemSearchRequest',
                                        'Keywords' => 'super+hats',
                                        'SearchIndex' => 'Apparel',
                                        'ResponseGroup' => array ('Request',
                                                                  'Medium')));

  $result = $client->ItemSearch($request);

  echo '<pre>';
  print_r($result);
  echo '</pre>';
}
catch (SoapFault $fault)
{
  trigger_error('SOAP Fault: (faultcode: ' . $fault->faultcode . ', ' .
                'faultstring: ' . $fault->faultstring . ')', E_USER_ERROR);
}

