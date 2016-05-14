<?php
class Avangate
{
    protected $soapClient;
    protected $sessionId;

    public function getSessionId()
    {
        return $this->sessionId;
    }

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
                'ProductId' => $rawProduct->ProductCode,
                'ProductCode' => $rawProduct->ProductCode,
                'ProductName' => $rawProduct->ProductName,
                'ProductVersion' => $rawProduct->ProductVersion,
                'ShortDescription' => $rawProduct->ShortDescription,
                'LongDescription' => $rawProduct->LongDescription,
                'ImageUrl' => $rawProduct->ProductImages[0]->URL
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

    public function placeOrder($orderDetails)
    {
        if (empty($orderDetails)) {
            throw new Exception('Empty order!');
        }

        // set billing details
        $billingObj = new stdClass();
        $billingObj->Address1 = $orderDetails->billing_address_1;
        $billingObj->Address2 = $orderDetails->billing_address_2;
        $billingObj->City = $orderDetails->billing_city;
        $billingObj->State = $orderDetails->billing_state;
        $billingObj->CountryCode = $orderDetails->billing_country;
        $billingObj->FirstName = $orderDetails->billing_first_name;
        $billingObj->LastName = $orderDetails->billing_last_name;
        $billingObj->Zip = $orderDetails->billing_postcode;
        //$billingObj->Company = $orderDetails['billing_address']['company'];
        $billingObj->Phone = $orderDetails->billing_phone;
        $billingObj->Email = $orderDetails->billing_email;

        // set delivery details
        $deliveryObj = new stdClass();
        $deliveryObj->Address1 = $orderDetails->shipping_address_1;
        $deliveryObj->Address2 =  $orderDetails->shipping_address_2;
        $deliveryObj->City =  $orderDetails->shipping_city;
        $deliveryObj->State =  $orderDetails->shipping_state;
        $deliveryObj->CountryCode =  $orderDetails->shipping_country;
        $deliveryObj->FirstName =  $orderDetails->shipping_first_name;
        $deliveryObj->LastName = $orderDetails->shipping_last_name;
        $deliveryObj->Zip = $orderDetails->shipping_postcode;
        $deliveryObj->Company = $orderDetails->shipping_company;
        $deliveryObj->Phone = $orderDetails->shipping_phone;
        $deliveryObj->Email = $orderDetails->shipping_email;

        $paymentMethod = new stdClass();
        $paymentMethod->RecurringEnabled = 1;

        $paymentDetails = new stdClass();
        $paymentDetails->Type = 'CCNOPCI'; //TEST
        $paymentDetails->Currency = $orderDetails->order_currency;
        $paymentDetails->CustomerIP = $orderDetails->customer_ip_address;
        $paymentDetails->PaymentMethod = $paymentMethod;

        $orderObj = new stdClass();
        $orderObj->Currency = $orderDetails->order_currency;
        $orderObj->Country = $orderDetails->billing_country;
        $orderObj->Language = 'en';
        $orderObj->CustomerIP = $orderDetails->customer_ip_address;
        $orderObj->ExternalReference = $orderDetails->get_order_number();
        $orderObj->Source = 'AvanPress';
        $orderObj->BillingDetails = $billingObj;
        //$orderObj->DeliveryDetails = $deliveryObj;

        //$orderObj->PaymentDetails = $paymentDetails;
        $orderObj->PaymentDetails = new stdClass();
        $orderObj->PaymentDetails->Type = 'TEST';
        $orderObj->PaymentDetails->Currency = $orderDetails->order_currency;
        $orderObj->PaymentDetails->PaymentMethod = new stdClass ();
        $orderObj->PaymentDetails->CustomerIP =  $orderDetails->customer_ip_address;
        $orderObj->PaymentDetails->PaymentMethod->RecurringEnabled = true;
        $orderObj->PaymentDetails->PaymentMethod->CardNumber = "4111111111111111";
        $orderObj->PaymentDetails->PaymentMethod->CardType = 'visa';
        $orderObj->PaymentDetails->PaymentMethod->ExpirationYear = '2019';
        $orderObj->PaymentDetails->PaymentMethod->ExpirationMonth = '12';
        $orderObj->PaymentDetails->PaymentMethod->HolderName = 'John';
        $orderObj->PaymentDetails->PaymentMethod->CCID = '123';

        $orderObj->Items = array();
        foreach ($orderDetails->get_items() as $idx => $productInfo) {
            $product = $orderDetails->get_product_from_item($productInfo);
            $productObj = new stdClass();
            $productObj->Code = $product->get_sku();
            $productObj->Quantity = $productInfo['qty'];

            $orderObj->Items[$idx] = $productObj;
        }

        //print_r($orderObj); exit;

        $newOrderDetails = $this->soapClient->placeOrder($this->sessionId, $orderObj);

        return $newOrderDetails;
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



}


/* eof */