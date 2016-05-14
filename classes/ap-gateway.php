<?php

if (!class_exists('AP_Gateway')) {

    /**
     * Handles cron jobs and intervals
     *
     * Note: Because WP-Cron only fires hooks when HTTP requests are made, make sure that an external monitoring service pings the site regularly to ensure hooks are fired frequently
     */
    class AP_Gateway extends AP_Module
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
        public function activate($network_wide)
        {
        }

        /**
         * Rolls back activation procedures when de-activating the plugin
         *
         * @mvc Controller
         */
        public function deactivate()
        {
        }

        /**
         * Register callbacks for actions and filters
         *
         * @mvc Controller
         */
        public function register_hook_callbacks()
        {
            add_action( 'plugins_loaded', array($this, 'init'), 0 );
        }

        /**
         * Initializes variables
         *
         * @mvc Controller
         */
        public function init()
        {
            // If the parent WC_Payment_Gateway class doesn't exist
            // it means WooCommerce is not installed on the site
            // so do nothing
            if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;

            // If we made it this far, then include our Gateway Class
            require_once(__DIR__ . '/gateway/ap-avangate-gateway.php');

            // Now that we have successfully included our class,
            // Lets add it too WooCommerce
            add_filter( 'woocommerce_payment_gateways', array($this, 'add_gateway') );

            add_filter( 'plugin_action_links_' . AP_NAME, array($this, 'action_links') );


        }

        public function action_links( $links ) {
            $plugin_links = array(
                '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '">' . __( 'Settings', 'spyr-authorizenet-aim' ) . '</a>',
            );

            // Merge our new link with the default ones
            return array_merge( $plugin_links, $links );
        }

        public function add_gateway( $methods ) {
            $methods[] = 'Avangate_Gateway';
            return $methods;
        }

        /**
         * Checks if the plugin was recently updated and upgrades if necessary
         *
         * @mvc Controller
         *
         * @param string $db_version
         */
        public function upgrade($db_version = 0)
        {
        }

        /**
         * Checks that the object is in a correct state
         *
         * @mvc Model
         *
         * @param string $property An individual property to check, or 'all' to check all of them
         * @return bool
         */
        protected function is_valid($property = 'all')
        {

        }

    }
}
