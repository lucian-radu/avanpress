<?php

require_once(__DIR__ . '/WooProduct.php');

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

        try {
            $options = [
                'location' => $connectionDetails['host'],
                'stream_context' => stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false
                    ]
                ])
            ];

            if(isset($connectionDetails['proxyHost']) && isset($connectionDetails['proxyPort'])){
                $options['proxy_host'] =  $connectionDetails['proxyHost'];
                $options['proxy_port'] =  $connectionDetails['proxyPort'];
            }

            $client = new SoapClient($connectionDetails['host'] . '?wsdl', $options);
        } catch ( SoapFault $e ) { // Do NOT try and catch "Exception" here
            //throw new Exception('sorry... our service is down');
        }

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

    public function getSessionId()
    {
        return $this->sessionId;
    }

    public function checkConnection()
    {
        if (empty($this->sessionId)) {
            return false;
        } else {
            return true;
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
                //'_image' => $rawProduct->ProductImages[0]->URL,
            );

            foreach ($rawProduct->ProductImages as $image) {
                if ($image->Default) {
                    $product['_image'] = $image->URL;
                    break;
                }
            }


            $products[] = $product;
        }

        return $products;
    }

    public function placeOrder($orderDetails, $environment)
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
        $deliveryObj->Address2 = $orderDetails->shipping_address_2;
        $deliveryObj->City = $orderDetails->shipping_city;
        $deliveryObj->State = $orderDetails->shipping_state;
        $deliveryObj->CountryCode = $orderDetails->shipping_country;
        $deliveryObj->FirstName = $orderDetails->shipping_first_name;
        $deliveryObj->LastName = $orderDetails->shipping_last_name;
        $deliveryObj->Zip = $orderDetails->shipping_postcode;
        $deliveryObj->Company = $orderDetails->shipping_company;
        $deliveryObj->Phone = $orderDetails->shipping_phone;
        $deliveryObj->Email = $orderDetails->shipping_email;

        $orderObj = new stdClass();
        $orderObj->Currency = $orderDetails->order_currency;
        $orderObj->Country = $orderDetails->billing_country;
        $orderObj->Language = 'en';
        $orderObj->CustomerIP = $orderDetails->customer_ip_address;
        $orderObj->ExternalReference = $orderDetails->get_order_number();
        $orderObj->Source = 'AvanPress';
        $orderObj->BillingDetails = $billingObj;
        //$orderObj->DeliveryDetails = $deliveryObj;

        if (!$environment) {
            $paymentMethod = new stdClass();
            $paymentMethod->RecurringEnabled = 1;

            $paymentDetails = new stdClass();
            $paymentDetails->Type = 'CCNOPCI';
            $paymentDetails->Currency = $orderDetails->order_currency;
            $paymentDetails->CustomerIP = $orderDetails->customer_ip_address;
            $paymentDetails->PaymentMethod = $paymentMethod;
            $orderObj->PaymentDetails = $paymentDetails;

        } else {
            $exp = $_POST['avangate_gateway-card-expiry'];
            $exp = explode('/', $exp);
            $orderObj->PaymentDetails = new stdClass();
            $orderObj->PaymentDetails->Type = 'TEST';
            $orderObj->PaymentDetails->Currency = $orderDetails->order_currency;
            $orderObj->PaymentDetails->PaymentMethod = new stdClass ();
            $orderObj->PaymentDetails->CustomerIP = $orderDetails->customer_ip_address;
            $orderObj->PaymentDetails->PaymentMethod->RecurringEnabled = true;
            $orderObj->PaymentDetails->PaymentMethod->CardNumber = str_replace(" ", '', $_POST['avangate_gateway-card-number']);
            $orderObj->PaymentDetails->PaymentMethod->CardType = strtolower($this->cardType($_POST['avangate_gateway-card-number']));
            $orderObj->PaymentDetails->PaymentMethod->ExpirationYear = '20' . trim($exp[1]);
            $orderObj->PaymentDetails->PaymentMethod->ExpirationMonth = trim($exp[0]);
            $orderObj->PaymentDetails->PaymentMethod->HolderName = $orderDetails->billing_first_name . " " . $orderDetails->billing_last_name;
            $orderObj->PaymentDetails->PaymentMethod->CCID = $_POST['avangate_gateway-card-cvc'];

        }

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

    public function importProducts()
    {
        $result = true;
        //get products
        $products = $this->getProducts();
        if (is_array($products)) {
            foreach ($products as $product) {
                $WooProduct = new WooProduct($product);
                $product_id = $WooProduct->save($product);
                $result = $result && $product_id;
            }
        }
       return $result;
    }

    public function cardType($number)
    {
        $number = preg_replace('/[^\d]/', '', $number);
        if (preg_match('/^3[47][0-9]{13}$/', $number)) {
            return 'American Express';
        } elseif (preg_match('/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/', $number)) {
            return 'Diners Club';
        } elseif (preg_match('/^6(?:011|5[0-9][0-9])[0-9]{12}$/', $number)) {
            return 'Discover';
        } elseif (preg_match('/^(?:2131|1800|35\d{3})\d{11}$/', $number)) {
            return 'JCB';
        } elseif (preg_match('/^5[1-5][0-9]{14}$/', $number)) {
            return 'MasterCard';
        } elseif (preg_match('/^4[0-9]{12}(?:[0-9]{3})?$/', $number)) {
            return 'Visa';
        } else {
            return 'Unknown';
        }
    }
}