<?php

class WooProduct
{

    public $new = true;

    public $header = array();

    public $tags = array();

    public $categories = array();

    public $images = array();

    public $raw_data = array();

    public $shipping_class = '';

    public $featured_image = '';

    public $product_gallery = '';

    public $product_type = 'simple';

    /* since 3.0.6
        no more use of the global $woocsv_import
    */
    public $log = array();

    //body
    public $body = array(
        'ID' => '',
        'post_type' => 'product',
        'post_status' => 'publish',
        'post_title' => '',
        'post_name' => '',
        'post_date' => '',
        'post_date_gmt' => '',
        'post_content' => '',
        'post_excerpt' => '',
        'post_parent' => 0,
        'post_password' => '',
        'comment_status' => 'open',
        'ping_status' => 'open',
        'menu_order' => 0,
        'post_author' => '',
    );


    public $meta = array(
        '_sku' => '',
        '_downloadable' => 'no',
        '_virtual' => 'yes',
        '_price' => '',
        '_visibility' => 'visible',
        '_stock' => '',
        '_stock_status' => 'instock',
        '_backorders' => 'no',
        '_manage_stock' => 'no',
        '_sale_price' => '',
        '_regular_price' => '',
        '_weight' => '',
        '_length' => '',
        '_width' => '',
        '_height' => '',
        '_tax_status' => 'taxable',
        '_tax_class' => '',
        '_upsell_ids' => array(),
        '_crosssell_ids' => array(),
        '_sale_price_dates_from' => '',
        '_sale_price_dates_to' => '',
        '_min_variation_price' => '',
        '_max_variation_price' => '',
        '_min_variation_regular_price' => '',
        '_max_variation_regular_price' => '',
        '_min_variation_sale_price' => '',
        '_max_variation_sale_price' => '',
        '_featured' => 'no',
        '_file_path' => '',
        '_download_limit' => '',
        '_download_expiry' => '',
        '_product_url' => '',
        '_button_text' => '',
//		'total_sales'=>0,
    );

    public function __construct($product = null)
    {
        $this->fill($product);
    }



    public function is_valid_url($url)
    {
        // alternative way to check for a valid url
        if  (filter_var($url, FILTER_VALIDATE_URL) === FALSE) return false; else return true;

    }




    public function fill($product = null){

        if (is_array($product)){
            //fill post data
            foreach($product as $k=>$v ){
                if (in_array($k,array_keys($this->body))){
                    $this->body[$k] = $v;
                }
            }

            //fill product meta
            foreach($product as $k=>$v ){
                if (in_array($k,array_keys($this->meta))){
                    $this->meta[$k] = $v;
                }
            }

            if (!empty($product['_image'])){
                $this->featured_image = $product['_image'];
                $this->featured_image = $product['_image'];
            }

        }
    }


    /**
     * Save image for current product
     */
    public function save_featured_image() {

        if ($this->is_valid_url($this->featured_image)) {
            $imageID = $this->save_image_with_url($this->featured_image);
        }

        // @ since XXX check if the url is valid
        if ( !is_wp_error( $imageID ) ) {
            set_post_thumbnail( $this->body['ID'], $imageID );
        }
    }


    //@since 3.0.5 use WP functions to upload and handle images with url's
    function save_image_with_url($url) {


        $tmp = download_url( $url , 10 );

        if( is_wp_error( $tmp ) ){
            //something went wrong during download
            @unlink($file_array['tmp_name']);
            return false;
        }


        $post_id = $this->body['ID'];
        $desc = '';
        $file_array = array();
        $id = false;


        @preg_match('/[^\?]+\.(jpg|jpe|jpeg|gif|png|pdf)/i', $url, $matches);
        if (!$matches) {
            $file_array['tmp_name'] = $tmp;
            $file_array['name'] = sanitize_file_name($url);
            $desc = '';
        } else {
            $file_array['name'] = basename($matches[0]);
            $file_array['tmp_name'] = $tmp;
            $desc = $file_array['name'];
        }

        // do the validation and storage stuff
        $id = media_handle_sideload( $file_array, $post_id, $desc );

        // If error storing permanently, unlink
        if ( is_wp_error($id) ) {
            @unlink($file_array['tmp_name']);
            return $id;
        }

        return $id;
    }



    /**
     * @return bool
     * Save the product data
     */
    public function save(){

        //save main data
        $postId = wp_insert_post($this->body, true);

        if (is_wp_error($postId)) {
            return false;
        } else {
            $this->body['ID'] = $postId;
        }

        //save meta
        //save the meta
        foreach ($this->meta as $key=>$value) {
            update_post_meta($postId, $key, $value);
        }

        //save image
        if (!empty($this->featured_image)) {
            $this->save_featured_image();
        }

        return $postId;

    }



}