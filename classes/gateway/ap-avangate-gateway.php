<?php

/* Authorize.net AIM Payment Gateway Class */
class Avangate_Gateway extends WC_Payment_Gateway {

    // Setup our Gateway's id, description and other values
    function __construct() {

        // The global ID for this Payment method
        $this->id = "avangate_gateway";

        // The Title shown on the top of the Payment Gateways Page next to all the other Payment Gateways
        $this->method_title = __( "Avangate", 'avanpress' );

        // The description for this Payment Gateway, shown on the actual Payment options page on the backend
        $this->method_description = __( "Avangate Payment Gateway Plug-in for WooCommerce", 'avanpress' );

        // The title to be used for the vertical tabs that can be ordered top to bottom
        $this->title = __( "Avangate", 'avanpress' );

        // If you want to show an image next to the gateway's name on the frontend, enter a URL to an image.
        $this->icon = null;

        // Bool. Can be set to true if you want payment fields to show on the checkout
        // if doing a direct integration, which we are doing in this case
        $this->has_fields = true;

        // Supports the default credit card form
        $this->supports = array( 'default_credit_card_form' );

        // This basically defines your settings which are then loaded with init_settings()
        $this->init_form_fields();

        // After init_settings() is called, you can get the settings and load them into variables, e.g:
        // $this->title = $this->get_option( 'title' );
        $this->init_settings();

        // Turn these settings into variables we can use
        foreach ( $this->settings as $setting_key => $value ) {
            $this->$setting_key = $value;
        }

        // Lets check for SSL
        add_action( 'admin_notices', array( $this,	'do_ssl_check' ) );

        // Save settings
        if ( is_admin() ) {
            // Versions over 2.0
            // Save our administration options. Since we are not going to be doing anything special
            // we have not defined 'process_admin_options' in this class so the method in the parent
            // class will be used instead
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        }
    } // End __construct()

    // Build the administration fields for this specific Gateway
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'		=> __( 'Enable / Disable', 'avanpress' ),
                'label'		=> __( 'Enable this payment gateway', 'avanpress' ),
                'type'		=> 'checkbox',
                'default'	=> 'no',
            ),
            'title' => array(
                'title'		=> __( 'Title', 'avanpress' ),
                'type'		=> 'text',
                'desc_tip'	=> __( 'Payment title the customer will see during the checkout process.', 'avanpress' ),
                'default'	=> __( 'Avangate Direct', 'avanpress' ),
            ),
            'description' => array(
                'title'		=> __( 'Description', 'avanpress' ),
                'type'		=> 'textarea',
                'desc_tip'	=> __( 'Payment description the customer will see during the checkout process.', 'avanpress' ),
                'default'	=> __( 'Pay securely using your credit card.', 'avanpress' ),
                'css'		=> 'max-width:350px;'
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
                'title'		=> __( 'Avangate Test Mode', 'avanpress' ),
                'label'		=> __( 'Enable Test Mode', 'avanpress' ),
                'type'		=> 'checkbox',
                'description' => __( 'Place the payment gateway in test mode.', 'avanpress' ),
                'default'	=> 'no',
            )
        );
    }

    // Submit payment and handle response
    public function process_payment( $order_id ) {
        global $woocommerce;

        // Get this Order's information so that we know
        // who to charge and how much
        $order = new WC_Order( $order_id );

        // Are we testing right now or is it a real transaction
        $environment = ( $this->environment == "yes" ) ? 'TRUE' : 'FALSE';


        $apiClient = AP_Api::get_instance();
        $settings = AP_Settings::get_instance();
       // $order->card
//var_dump($order->get_product_from_item()); die;
        try {
            $response = $apiClient->api->placeOrder($order);
            $post_url = $response->PaymentDetails->PaymentMethod->RedirectURL;
        }catch(\Exception $e) {
            echo $e->getMessage();
        }
        var_dump($response);
//        $refno = $response->RefNo;
       // $external = $response->ExternalReference;
        die;
//        $paymentDetails = array(
//            'session_id' => $apiClient->api->getSessionId(),
//            'card_number' => '4111111111111111',
//            'card_type' => 'visa',
//            'ccid' => '123',
//            'date_month' => '12',
//            'date_year' => '2019',
//            'holder_name' => 'John',
//        );
//        var_dump($post_url);
//
//        try{
//            // Send this payload to Authorize.net for processing
//            $response = wp_remote_post( $post_url, array(
//                'method'    => 'POST',
//                'body'      => http_build_query( $paymentDetails ),
//                'timeout'   => 90,
//                'sslverify' => false,
//            ) );
//            var_dump($response);
//        }catch(\Exception $e){
//            echo $e->getMessage();
//        }



        if ( is_wp_error( $response ) )
            throw new Exception( __( 'We are currently experiencing problems trying to connect to this payment gateway. Sorry for the inconvenience.', 'avanpress' ) );

        if ( empty( $response['body'] ) )
            throw new Exception( __( 'Authorize.net\'s Response was empty.', 'avanpress' ) );

        // Retrieve the body's resopnse if no errors found
        $response_body = wp_remote_retrieve_body( $response );

        // Parse the response into something we can read
        foreach ( preg_split( "/\r?\n/", $response_body ) as $line ) {
            $resp = explode( "|", $line );
        }

        // Get the values we need
        $r['response_code']             = $resp[0];
        $r['response_sub_code']         = $resp[1];
        $r['response_reason_code']      = $resp[2];
        $r['response_reason_text']      = $resp[3];

        // Test the code to know if the transaction went through or not.
        // 1 or 4 means the transaction was a success
        if ( ( $r['response_code'] == 1 ) || ( $r['response_code'] == 4 ) ) {
            // Payment has been successful
            $customer_order->add_order_note( __( 'Authorize.net payment completed.', 'avanpress' ) );

            // Mark order as Paid
            $customer_order->payment_complete();

            // Empty the cart (Very important step)
            $woocommerce->cart->empty_cart();

            // Redirect to thank you page
            return array(
                'result'   => 'success',
                'redirect' => $this->get_return_url( $customer_order ),
            );
        } else {
            // Transaction was not succesful
            // Add notice to the cart
            wc_add_notice( $r['response_reason_text'], 'error' );
            // Add note to the order for your reference
            $customer_order->add_order_note( 'Error: '. $r['response_reason_text'] );
        }

    }

    // Validate fields
    public function validate_fields() {
        return true;
    }

    // Check if we are forcing SSL on checkout pages
    // Custom function not required by the Gateway
    public function do_ssl_check() {
        if( $this->enabled == "yes" ) {
            if( get_option( 'woocommerce_force_ssl_checkout' ) == "no" ) {
                echo "<div class=\"error\"><p>". sprintf( __( "<strong>%s</strong> is enabled and WooCommerce is not forcing the SSL certificate on your checkout page. Please ensure that you have a valid SSL certificate and that you are <a href=\"%s\">forcing the checkout pages to be secured.</a>" ), $this->method_title, admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ) ."</p></div>";
            }
        }
    }

} // End of SPYR_AuthorizeNet_AIM