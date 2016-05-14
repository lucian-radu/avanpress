<?php

/* Authorize.net AIM Payment Gateway Class */

class Avangate_Gateway extends WC_Payment_Gateway
{

    // Setup our Gateway's id, description and other values
    function __construct()
    {

        // The global ID for this Payment method
        $this->id = "avangate_gateway";

        // The Title shown on the top of the Payment Gateways Page next to all the other Payment Gateways
        $this->method_title = __("Avangate", 'avanpress');

        // The description for this Payment Gateway, shown on the actual Payment options page on the backend
        $this->method_description = __("Avangate Payment Gateway Plug-in for WooCommerce", 'avanpress');

        // The title to be used for the vertical tabs that can be ordered top to bottom
        $this->title = __("Avangate", 'avanpress');

        // If you want to show an image next to the gateway's name on the frontend, enter a URL to an image.
        $this->icon = null;

        // Bool. Can be set to true if you want payment fields to show on the checkout
        // if doing a direct integration, which we are doing in this case
        $this->has_fields = true;

        // Supports the default credit card form
        $this->supports = array('default_credit_card_form');

        // This basically defines your settings which are then loaded with init_settings()
        $this->init_form_fields();

        // After init_settings() is called, you can get the settings and load them into variables, e.g:
        // $this->title = $this->get_option( 'title' );
        $this->init_settings();

        // Turn these settings into variables we can use
        foreach ($this->settings as $setting_key => $value) {
            $this->$setting_key = $value;
        }

        // Lets check for SSL
        add_action('admin_notices', array($this, 'do_ssl_check'));

        // Save settings
        if (is_admin()) {
            // Versions over 2.0
            // Save our administration options. Since we are not going to be doing anything special
            // we have not defined 'process_admin_options' in this class so the method in the parent
            // class will be used instead
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }
    } // End __construct()

    // Build the administration fields for this specific Gateway
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable / Disable', 'avanpress'),
                'label' => __('Enable this payment gateway', 'avanpress'),
                'type' => 'checkbox',
                'default' => 'no',
            ),
            'title' => array(
                'title' => __('Title', 'avanpress'),
                'type' => 'text',
                'desc_tip' => __('Payment title the customer will see during the checkout process.', 'avanpress'),
                'default' => __('Avangate Direct', 'avanpress'),
            ),
            'description' => array(
                'title' => __('Description', 'avanpress'),
                'type' => 'textarea',
                'desc_tip' => __('Payment description the customer will see during the checkout process.', 'avanpress'),
                'default' => __('Pay securely using your credit card.', 'avanpress'),
                'css' => 'max-width:350px;'
            ),
//            'api_login' => array(
//                'title'		=> __( 'Authorize.net API Login', 'avanpress' ),
//                'type'		=> 'text',
//                'desc_tip'	=> __( 'This is the API Login provided by Authorize.net when you signed up for an account.', 'avanpress' ),
//            ),
//            'trans_key' => array(
//                'title'		=> __( 'Authorize.net Transaction Key', 'avanpress' ),
//                'type'		=> 'password',
//                'desc_tip'	=> __( 'This is the Transaction Key provided by Authorize.net when you signed up for an account.', 'avanpress' ),
//            ),
            'environment' => array(
                'title' => __('Avangate Test Mode', 'avanpress'),
                'label' => __('Enable Test Mode', 'avanpress'),
                'type' => 'checkbox',
                'description' => __('Place the payment gateway in test mode.', 'avanpress'),
                'default' => 'no',
            )
        );
    }

    // Submit payment and handle response
    public function process_payment($order_id)
    {
        global $woocommerce;
        // Get this Order's information so that we know
        // who to charge and how much
        $order = new WC_Order($order_id);

        // Are we testing right now or is it a real transaction
        $environment = ($this->environment == "yes") ? TRUE : FALSE;


        try {
            // Get Api Client
            $apiClient = AP_Api::get_instance();
            $result = $this->processAvangatePayment($apiClient, $order, $environment);
            return $result;
        } catch (\Exception $e) {
            throw new Exception(__($e->getMessage(), 'avanpress'));
        }
    }

    protected function processAvangatePayment($apiClient, $order, $environment)
    {
        try {
            $response = $apiClient->api->placeOrder($order, $environment);

            if(!$environment){
                $response = $apiClient->api->placeOrder($order, $environment);

                $post_url = $response->PaymentDetails->PaymentMethod->RedirectURL;

                $exp = explode('/', $_POST['avangate_gateway-card-expiry']);

                $paymentDetails = array(
                    'card_number' =>  str_replace(" ", '', $_POST['avangate_gateway-card-number']),
                    'card_type' => strtolower($apiClient->api->cardType($_POST['avangate_gateway-card-number'])),
                    'ccid' => $_POST['avangate_gateway-card-cvc'],
                    'date_month' => trim($exp[0]),
                    'date_year' => '20' . trim($exp[1]),
                    'holder_name' => $order->billing_first_name . " " . $order->billing_last_name,
                );
                // Send this payload to Authorize.net for processing
                $responseFinish = wp_remote_post( $post_url, array(
                    'method'    => 'POST',
                    'body'      => http_build_query( $paymentDetails ),
                    'timeout'   => 90,
                    'sslverify' => false,
                ) );

                if ( is_wp_error( $responseFinish ) ) {
                    $error_message = $responseFinish->get_error_message();
                    error_log(print_r($error_message, false));
                    throw new Exception(__("Something went wrong", 'avanpress'));
                } else {
                    if(isset($responseFinish['response']['code']) && $responseFinish['response']['code'] == 200){
                        $response = $responseFinish['response']['message'];
                    }else{
                        error_log(print_r($response['response']['message'], false));
                        throw new Exception(__("Something went wrong", 'avanpress'));
                    }
                }


            }
        } catch (\Exception $e) {
            throw new Exception(__($e->getMessage(), 'avanpress'));
        }

        if (is_wp_error($response))
            throw new Exception(__('We are currently experiencing problems trying to connect to this payment gateway. Sorry for the inconvenience.', 'avanpress'));

        if (empty($response))
            throw new Exception(__('Avangate\'s Response was empty.', 'avanpress'));

        if (isset($response->RefNo) && !empty($response->RefNo)) {
            $order->add_order_note(__('Avangate order placed with refno: ' . $response->RefNo, 'avanpress'));

            // Mark order as Paid
            $order->update_status('processing', __( 'Payment to be confirmed via IPN.', 'woocommerce' ));

            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url($order),
            );
        } else {
            wc_add_notice('Order not placed', 'error');
            // Add note to the order for your reference
            $order->add_order_note(__('Error: Avangate order not placed.', 'avanpress'));
        }
        return array(
            'result' => 'error'
        );

    }

    // Check if we are forcing SSL on checkout pages
    // Custom function not required by the Gateway

    public function validate_fields()
    {
        return true;
    }

    // Validate fields

    public function do_ssl_check()
    {
        if ($this->enabled == "yes") {
            if (get_option('woocommerce_force_ssl_checkout') == "no") {
                echo "<div class=\"error\"><p>" . sprintf(__("<strong>%s</strong> is enabled and WooCommerce is not forcing the SSL certificate on your checkout page. Please ensure that you have a valid SSL certificate and that you are <a href=\"%s\">forcing the checkout pages to be secured.</a>"), $this->method_title, admin_url('admin.php?page=wc-settings&tab=checkout')) . "</p></div>";
            }
        }
    }
} // End of SPYR_AuthorizeNet_AIM