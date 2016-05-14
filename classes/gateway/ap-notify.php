<?php

if (!class_exists('AP_Notify')) {

    /**
     * Handles cron jobs and intervals
     *
     * Note: Because WP-Cron only fires hooks when HTTP requests are made, make sure that an external monitoring service pings the site regularly to ensure hooks are fired frequently
     */
    class AP_Notify extends AP_Module
    {
        protected static $readable_properties = array();
        protected static $writeable_properties = array();

        /*
		 * methods
		 */

        /**
         * Constructor
         *
         * @mvc Controller
         */
        protected function __construct()
        {
            $this->register_hook_callbacks();
        }
        

        /**
         * Prepares sites to use the plugin during single or network-wide activation
         *
         * @mvc Controller
         *
         * @param bool $network_wide
         */
        public function activate($network_wide) { }

        
        /**
         * Rolls back activation procedures when de-activating the plugin
         *
         * @mvc Controller
         */
        public function deactivate() { }

        
        /**
         * Register callbacks for actions and filters
         *
         * @mvc Controller
         */
        public function register_hook_callbacks()
        {
            add_action( 'init', array($this, 'init'), 0 );
        }

        
        /**
         * Initializes variables
         */
        public function init()
        {
            add_rewrite_endpoint('avangate-ipn', EP_ROOT | EP_PAGES);
            add_action('pre_get_posts', [$this, 'avangate_proxy_ipn']);
		}

        
        /**
         * Make ipn action
         * @param WP_Http $query
         */
        public function avangate_proxy_ipn($query)
        {
            $options = get_option('ap_settings');
            if ($query->get('avangate-ipn') === 'register') {
                // Avangate ipn
                $pass = $options['basic']['field-merchant-key'];
                $result = '';
                $return = '';
                $signature = $_POST['HASH'];
                $body = '';

                // read info received
                ob_start();
                while (list($key, $val) = each($_POST)) {
                    if ($key != 'HASH') {
                        if (is_array($val)) {
                            $result .= $this->arrayExpand($val);
                        }
                        else {
                            $size = strlen(stripslashes($val));
                            $result	.= $size.stripslashes($val);
                        }
                    }
                }
                $body = ob_get_contents();
                ob_end_flush();

                $date_return = date('YmdGis');

                $return = strlen($_POST['IPN_PID'][0]).$_POST['IPN_PID'][0].strlen($_POST['IPN_PNAME'][0]).$_POST['IPN_PNAME'][0];
                $return .= strlen($_POST['IPN_DATE']).$_POST['IPN_DATE'].strlen($date_return).$date_return;

                $hash =  $this->hmac($pass, $result); /* HASH for data received */

                $body .= $result."\r\n\r\nHash: ".$hash."\r\n\r\nSignature: {$signature}\r\n\r\nReturnSTR: ".$return;

                if ($hash == $signature) {
                    
                    echo 'Verified OK!';
                    /* ePayment response */
                    $result_hash =  $this->hmac($pass, $return);
                    echo '<EPAYMENT>'.$date_return.'|'.$result_hash.'</EPAYMENT>';
                    /* Begin automated procedures (START YOUR CODE)*/
                    if (self::DEBUG_MODE) {
                        @mail(get_option('admin_email'), 'Good IPN', $body);
                    }
                }
                else {
                    /* warning email */
                    if (self::DEBUG_MODE) {
                        @mail(get_option('admin_email'), 'BAD IPN Signature', $body);
                    }
                }
                exit;
            }
        }
        
        
        /**
         * Array expand helper
         * @param type $array
         * @return string
         */
        protected function arrayExpand($array)
        {
            $retval = '';
            for($i = 0; $i < sizeof($array); $i++) {
                $size = strlen(stripslashes($array[$i]));
                $retval	.= $size.stripslashes($array[$i]);
            }

            return $retval;
        }
        
        
        /**
         * Hmac helper
         * @param string $key
         * @param string $data
         * @return string
         */
        protected function hmac($key, $data)
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
        
        
        /**
         * Checks if the plugin was recently updated and upgrades if necessary
         * @mvc Controller
         * @param string $db_version
         */
        public function upgrade($db_version = 0) { }

        
        /**
         * Checks that the object is in a correct state
         * @mvc Model
         * @param string $property An individual property to check, or 'all' to check all of them
         * @return bool
         */
        protected function is_valid($property = 'all') { }

    }
}
