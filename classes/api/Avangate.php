<?php
class Avangate
{
    protected $soapClient;
    protected $sessionId;

    public function __construct($connectionDetails)
    {
        if (empty($connectionDetails)) {
            throw new Exception('No connection details!');
        }

        $now = date('Y-m-d H:i:s');

        $client = new SoapClient($connectionDetails['host'] . '?wsdl', array(
            'location' => $connectionDetails['host'],
            'proxy_host' => isset($connectionDetails['proxyHost']) ? $connectionDetails['proxyHost'] : '',
            'proxy_port' => isset($connectionDetails['proxyPort']) ? $connectionDetails['proxyPort'] : '',
            'stream_context' => stream_context_create(array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false
                )
            ))
        ));

        if (!$client) {
            throw new Exception('Could not create SOAP client!');
        }

        $this->soapClient = $client;

        $string = strlen($connectionDetails['merchantCode']) . $connectionDetails['merchantCode'] . strlen($now) . $now;
        $hash = hmac($connectionDetails['secretKey'], $string);

        try {
            $this->sessionId = $this->soapClient->login($connectionDetails['merchantCode'], $now, $hash);
        } catch (SoapFault $e) {
            throw new Exception('Could not login!');
        }
    }

    public function getProducts()
    {
        $products = array();

        $rawProducts = $this->soapClient->searchProducts($this->sessionId);

        foreach ($rawProducts as $rawProduct) {

            $product = array(
                'ProductCode' => $rawProduct->ProductCode,
                'ProductName' => $rawProduct->ProductName,
                'ProductVersion' => $rawProduct->ProductVersion,
                'ShortDescription' => $rawProduct->ShortDescription,
                'LongDescription' => $rawProduct->LongDescription,
            );

            $prices = array();
            foreach ($rawProduct->PricingConfigurations as $priceDetails) {
                $price = array(
                    'Type' => $priceDetails->PriceType,
                    'Values' => $priceDetails->Prices->Regular
                );

                $values = array();
                foreach ($priceDetails->Prices->Regular as $regularPrice) {
                    $values['Currency'] = $regularPrice->Currency;
                    $values['Amount'] = $regularPrice->Amount;
                }
                $price['Values'] = $values;

                $prices = $price;
            }
            $product['Prices'] = $prices;


            $products[] = $product;
        }

        return $products;
    }

}


/* eof */