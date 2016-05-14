<?php

require_once (__DIR__.'/WooProduct.php');

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
        $hash = $this->hmac($connectionDetails['secretKey'], $string);

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
                '_sku' => $rawProduct->ProductCode,
                'post_title' => $rawProduct->ProductName,
                //'ProductVersion' => $rawProduct->ProductVersion,
                'post_excerpt' => $rawProduct->ShortDescription,
                'post_content' => $rawProduct->LongDescription,
                '_price' => $rawProduct->PricingConfigurations[0]->Prices->Regular[0]->Amount,
                '_image' => $rawProduct->ProductImages[0]->URL,
            );

            /*
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
            */

            $products[] = $product;
        }

        return $products;
    }

    private function hmac($key, $data)
    {
        $b = 64; // byte length for md5
        if (strlen($key) > $b) {
            $key = pack("H*",md5($key));
        }
        $key  = str_pad($key, $b, chr(0x00));
        $ipad = str_pad('', $b, chr(0x36));
        $opad = str_pad('', $b, chr(0x5c));
        $k_ipad = $key ^ $ipad ;
        $k_opad = $key ^ $opad;

        return md5($k_opad  . pack("H*",md5($k_ipad . $data)));
    }

    public function importProducts(){
        //get products
        $products = $this->getProducts();
        if (is_array($products)){
            foreach($products as $product){
                $WooProduct = new WooProduct($product);
                $WooProduct -> save($product);
                //die(print_r($WooProduct,1));
            }
    }

    }

}


/* eof */