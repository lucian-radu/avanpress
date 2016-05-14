<?php
require_once (__DIR__.'/api/Avangate.php');

if ( ! class_exists( 'AP_Api' ) ) {

    /**
     * Handles cron jobs and intervals
     *
     * Note: Because WP-Cron only fires hooks when HTTP requests are made, make sure that an external monitoring service pings the site regularly to ensure hooks are fired frequently
     */
    class AP_Api extends AP_Module {
        protected static $readable_properties  = array('api');
        protected static $writeable_properties = array();
        protected $modules;
        public $api;

        protected $settings = array();

        /*
         * Magic methods
         */

        /**
         * Constructor
         *
         * @mvc Controller
         */
        protected function __construct() {
            $this->register_hook_callbacks();
            $this->settings = $this->buildConnection();

            try {
                $this->api = new Avangate($this->settings);
            } catch (Exception $e) {
                echo $e->getMessage();
            }

        }

        protected function buildConnection(){

            $apSetting = AP_Settings::get_instance();

            // Readable
            $apSetting->init();

            $settings = array(
                'host' => $apSetting->settings['basic']['field-hostname'].$apSetting->settings['basic']['field-location'],
                'proxyHost' => 'proxy.avangate.local',
                'proxyPort' => '8080',
                'merchantCode' => $apSetting->settings['basic']['field-merchant-code'],
                'secretKey' => $apSetting->settings['basic']['field-merchant-key'],
            );
            return $settings;
        }

        /*
         * Instance methods
         */

        /**
         * Register callbacks for actions and filters
         *
         * @mvc Controller
         */
        public function register_hook_callbacks() {
            //add_action( 'ap_cron_timed_jobs',  __CLASS__ . '::fire_job_at_time' );
            //add_action( 'ap_cron_example_job', __CLASS__ . '::example_job' );

            add_action( 'init',                  array( $this, 'init' ) );
            add_action( 'wp_ajax_import_products', array($this, 'ajax_import_products') );

            //add_filter( 'cron_schedules',        __CLASS__ . '::add_custom_cron_intervals' );
        }

        public function ajax_import_products()
        {
            echo json_encode($this->api->getProducts());
            die();
        }



        /**
         * Initializes variables
         *
         * @mvc Controller
         */
        public function init() {
        }

        /**
         * Executes the logic of upgrading from specific older versions of the plugin to the current version
         *
         * @mvc Model
         *
         * @param string $db_version
         */
        public function upgrade( $db_version = 0 ) {
            /*
            if( version_compare( $db_version, 'x.y.z', '<' ) )
            {
                // Do stuff
            }
            */
        }

        /**
         * Prepares sites to use the plugin during single or network-wide activation
         *
         * @mvc Controller
         *
         * @param bool $network_wide
         */
        public function activate( $network_wide ) {

        }

        /**
         * Rolls back activation procedures when de-activating the plugin
         *
         * @mvc Controller
         */
        public function deactivate(){

        }


        /**
         * Checks that the object is in a correct state
         *
         * @mvc Model
         *
         * @param string $property An individual property to check, or 'all' to check all of them
         * @return bool
         */
        protected function is_valid( $property = 'all' ) {
            return true;
        }
    } // end AP_Cron
}
