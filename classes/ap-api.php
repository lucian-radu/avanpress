<?php
require_once (__DIR__.'/api/Avangate.php');

if ( ! class_exists( 'AP_Api' ) ) {

    /**
     * Handles api calls
     */
    class AP_Api extends AP_Module {
        protected static $readable_properties  = array('api');
        protected static $writeable_properties = array();
        protected $modules;
        public $api;

        protected $settings = array();
        protected $errors = array();

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
                $this->errors[] = $e->getMessage();
            }

        }

        public function getErrors(){
            return $this->errors;
        }

        protected function buildConnection(){
            $apSetting = AP_Settings::get_instance();

            // Readable
            $apSetting->init();

            $settings = array(
                'host' => $apSetting->settings['basic']['field-hostname'].$apSetting->settings['basic']['field-location'],
                'merchantCode' => $apSetting->settings['basic']['field-merchant-code'],
                'secretKey' => $apSetting->settings['basic']['field-merchant-key'],
            );
            if($apSetting->settings['basic']['field-proxy-host'] && $apSetting->settings['basic']['field-proxy-port']){
                $settings['proxyHost'] = $apSetting->settings['basic']['field-proxy-host'];
                $settings['proxyPort'] = $apSetting->settings['basic']['field-proxy-port'];
            }

            return $settings;
        }

        /**
         * Register callbacks for actions and filters
         *
         * @mvc Controller
         */
        public function register_hook_callbacks() {
            add_action( 'init',                  array( $this, 'init' ) );
            add_action( 'wp_ajax_import_products', array($this, 'ajax_import_products') );
            add_action( 'wp_ajax_check_connection', array($this, 'ajax_check_connection') );
        }

        public function ajax_import_products()
        {
            echo json_encode($this->api->importProducts());
            die();
        }

        public function ajax_check_connection()
        {
            echo json_encode(["connectionStatus" => $this->api->checkConnection()]);
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
    }
}