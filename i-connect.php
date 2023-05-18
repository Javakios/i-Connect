<?php
/*
Plugin Name: i-ConnectOne
Description: i-ConnectOne is a powerful and intuitive WordPress plugin designed to streamline and enhance your e-commerce operations. It seamlessly integrates with WooCommerce to provide robust features and capabilities such as updating products and categories directly from your external server.
Version: 1.0
Author: Isg Informatics
*/

require_once plugin_dir_path(__FILE__) . '/connection-settings/connection-settings.php';
require_once plugin_dir_path(__FILE__) . '/request-settings/request-settings.php';
$request_settings = new Request_Settings();
$update_products_settings = new Connection_Settings();

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


if (!class_exists('Update_Products')) {

    class Update_Products
    {

        private $request_settings;

        public function __construct($request_settings)
        {

            $this->request_settings = $request_settings;

            if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
                add_action('admin_menu', array($this, 'add_submenu'));

            }
        }

        public function add_submenu()
        {
            add_submenu_page(
                'edit.php?post_type=product',
                'Update Products',
                'Update Products',
                'manage_options',
                'update-products',
                array($this, 'display_callback')
            );

        }
        public function display_callback()
        {
            delete_option('request_settings');
            // Fetch data
            $request_types = $this->request_settings->get_all_request_types();
            // Start output
            echo '<div class="wrap">';

            // Page title
            echo '<h1 class="wp-heading-inline">Update Products</h1>';
            if (count($request_types) != 0) {
                $keys = array_keys($request_types);
                $filtered_keys = array_diff($keys, ['service', 'sqlName']);



                echo '<form method="post" class="wp-filter">';

                // Select Request Type
                echo '<label for="request_type" class="screen-reader-text">Select Request Type</label>';
                echo '<select name="request_type" id="request_type" class="postform">';
                foreach ($filtered_keys as $request_type) {
                    echo '<option value="' . esc_attr($request_type) . '">' . esc_html($request_type) . '</option>';
                }
                echo '</select>';

                // Submit button
                echo '<input type="submit" name="submit_request_type" id="submit_request_type" class="button" value="Submit">';

                echo '</form>';

                // Display output from selected request type
                if (isset($_POST['submit_request_type'])) {
                    $selected_request_type = $_POST['request_type'];
                    $request = $request_types[$selected_request_type]['request_type'];
                    // global $wpdb;
                    // $result = $wpdb->query( "DELETE FROM {$wpdb->prefix}posts WHERE post_type = 'product'");
                    // $result = $wpdb->query("DELETE pm FROM {$wpdb->prefix}postmeta pm LEFT JOIN {$wpdb->prefix}posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL");

                    switch ($request) {

                        case 'PRODUCTS':
                            $this->update_products($request_types[$selected_request_type]); /*update products function */
                            break;
                        case 'STOCK':
                            $this->update_stock($request_types[$selected_request_type]);
                            break;
                        case 'CATEGORIES':
                            $this->update_categories($request_types[$selected_request_type]); /* update categories function */
                            break;
                        case 'USERS':
                            echo 'update users'; /* update users function */
                            break;
                        case 'SUBCATEGORIE':
                            echo 'update subcategories'; /* update subcategories function */
                            break;
                        default:
                            echo 'Please Select A Request Type';
                    }


                }

            } else {
                echo '<h3 style="color:red;">Go To Settings And Fill The Required Fields</h3>';
            }

            echo '</div>'; // End wrap


            // $this->execute_request_type($request_types[$selected_request_type]);
        }
        private function update_stock($request_type)
        {

            // retriving addition options for req 
            $options = get_option('update_products_settings');
            $required_keys = ['appId'];


            // checking if the requried keys that i need in this request i have them
            foreach ($required_keys as $key) {
                if (!isset($options[$key]) || empty($options[$key])) {
                    die('Go To Settings and fill all the required fields');
                }
            }

            $clientID = $this->login_authenticate();
            // if i dont have it i cannot continue
            if (!isset($clientID) || empty($clientID)) {
                die('Something Went Wrong. Try Updating You Configuration Settings');
            }
            $update_stock_body = [
                'service' => $request_type['service'],
                // most common SqlData
                'clientID' => $clientID,
                // Authorization Key
                'appId' => $options['appId'],
                // Application ID (i have no idea what it is)
                'SqlName' => $request_type['sqlName'],
                // Function that i want to execute
                'param1' => date('Y'),
                // eg. 20230501
                'param2' => date('Ymd'),
                // current date
                'param3' => date('Y'),
                // current year
                'param4' => date('m') // current month
            ];
            $response = $this->make_request($update_stock_body);
            if (!isset($response['rows'])) {
                die('No Updates Retrived');
            }
            foreach ($response['rows'] as $stock) {
                $product_exists = wc_get_product_id_by_sku($stock['MTRL']);
                if ($product_exists) {
                    $product = wc_get_product($product_exists);
                    $product->set_stock_quantity($stock['Thess_Apothema']);
                    $product->set_manage_stock(true);
                    $product->save();
                }
            }
            echo 'Stock Updated';
            echo '<pre>';
            print_r($response);
            echo '</pre>';
        }
        private function update_categories($request_type)
        {
            $options = get_option('update_products_settings');
            $required_keys = ['appId'];


            // checking if the requried keys that i need in this request i have them
            foreach ($required_keys as $key) {
                if (!isset($options[$key]) || empty($options[$key])) {
                    die('Go To Settings and fill all the required fields');
                }
            }

            // getting the authorization key
            $clientID = $this->login_authenticate();
            // if i dont have it i cannot continue
            if (!isset($clientID) || empty($clientID)) {
                die('Something Went Wrong. Try Updating You Configuration Settings');
            }

            $update_categories_body = [
                'service' => $request_type['service'],
                'clientID' => $clientID,
                'appId' => $options['appId'],
                'SqlName' => $request_type['sqlName'],
                'param1' => '20230101'
            ];
            $response = $this->make_request($update_categories_body);
            foreach ($response['rows'] as $category) {
                $term = term_exists($category['NAME']);
                if ($term == 0 || $term == null) {
                    $results = wp_insert_term(
                        $category['NAME'],
                        'product_cat',
                        array(
                            'desciption' => 'Category Description',
                            'slug' => sanitize_title($category['NAME'])
                        )
                    );
                    if (is_wp_error($results)) {
                        echo $results->get_error_message();
                    } else {
                        $term_id = $results['term_id'];
                        $term_meta = get_term_meta($term_id['term_id']);
                        update_term_meta($term_id, 'external_category_code', $category['CCCSUBCATEGORIES']);
                    }
                }
            }
            echo 'Categories Updated';
            echo '<pre>',
                print_r($response);
            echo '</pre>';
        }
        private function update_products($request_type)
        {
            // retriving addition options for req 
            $options = get_option('update_products_settings');
            $required_keys = ['appId'];


            // checking if the requried keys that i need in this request i have them
            foreach ($required_keys as $key) {
                if (!isset($options[$key]) || empty($options[$key])) {
                    die('Go To Settings and fill all the required fields');
                }
            }

            // getting the authorization key
            $clientID = $this->login_authenticate();
            // if i dont have it i cannot continue
            if (!isset($clientID) || empty($clientID)) {
                die('Something Went Wrong. Try Updating You Configuration Settings');
            }

            // finally preparing the request body
            $update_products_body = [
                'service' => $request_type['service'],
                'clientID' => $clientID,
                'appId' => $options['appId'],
                'Sqlname' => $request_type['sqlName'],
                'param1' => '20230101'
            ];

            // hit the request
            $respose = $this->make_request($update_products_body);
            foreach ($respose['rows'] as $product) {
                if ($product['CCCPRWEB'] == 1 || $product['CCCPRWEB'] == '1') {
                    $existing_product_id = wc_get_product_id_by_sku($product['mtrl']);
                    if (!$existing_product_id) {
                        $new_product = new WC_Product();
                        $new_product->set_name($product['NAME']); // Sets the product name
                        $new_product->set_sku($product['MTRL']); // Sets the product SKU
                        $new_product->set_status('publish'); // Sets the product status
                        $new_product->set_stock_quantity(0); // Sets the stock quantity to 0 by default
                        $new_product->set_manage_stock(true); // Set manage stock enabled

                        // Get the category ID by the external category code
                        $external_category_code = $product['SUBCATEGORIES']; // replace 'category_code' with the actual key in your product array
                        echo $external_category_code;
                        $categories = get_terms(
                            array(
                                'taxonomy' => 'product_cat',
                                'hide_empty' => false,
                                'meta_query' => array(
                                    array(
                                        'key' => 'external_category_code',
                                        'value' => $external_category_code,
                                        'compare' => '='
                                    )
                                )
                            )
                        );
                        if (!empty($categories) && !is_wp_error($categories)) {
                            print_r($categories);
                            $category_ids = array_map(function ($category) {
                                return $category->term_id;
                            }, $categories);
                            $new_product->set_category_ids($category_ids);
                        }

                        $product_id = $new_product->save(); // Saves the product and returns the new product ID
                    }
                }
            }
            // echo 'Products Update';
            // echo '<pre>';
            // print_r($respose);
            // echo '</pre>';

        }

        private function login_authenticate()
        {

            $response = $this->login();
            if (is_string($response)) {
                die($response);
            }

            $response = $this->authenticate($response['clientID']);
            if (is_string($response)) {
                die($response);
            }

            return $response['clientID'];
        }

        private function selected_request($body, $clientID)
        {

            $options = get_option('update_products_settings');
            if (!isset($options['appId']) || empty($options['appId'])) {
                return 'Go To Settings And fill the required fields';
            }
            $post_data = [
                'service' => $body['service'],
                'clientID' => $clientID,
                'appId' => $options['appId'],
                'SqlName' => $body['sqlName'],
                'param1' => '20230501',
                'param2' => '20230517',
                'param3' => '2023',
                'param4' => '5'
            ];
            $response = $this->make_request($post_data);
            return $response;
        }

        private function login()
        {
            $options = get_option('update_products_settings');
            $required_keys = ['username', 'password', 'appId'];

            foreach ($required_keys as $key) {
                if (!isset($options[$key]) || empty($options[$key])) {
                    return 'Go To Settings and fill all the required fields';
                }
            }

            $login_data = [
                'service' => 'login',
                'username' => $options['username'],
                'password' => $options['password'],
                'appId' => $options['appId']
            ];

            $response = $this->make_request($login_data);
            return $response;
        }
        private function authenticate($clientID)
        {
            $options = get_option('update_products_settings');
            $required_keys = ['company', 'branch', 'module', 'refis'];
            foreach ($required_keys as $key) {
                if ((!isset($options[$key]) || empty($options[$key])) && !($options[$key] == 0 || $options[$key] == '0')) {
                    return 'Go To Settings and fill all the required fields';
                }
            }
            $auth_data = [
                'service' => 'authenticate',
                'clientID' => $clientID,
                'company' => $options['company'],
                'branch' => $options['branch'],
                'module' => $options['module'],
                'refid' => $options['refid']
            ];
            $response = $this->make_request($auth_data);
            return $response;
        }
        private function make_request($data)
        {
            $options = get_option('update_products_settings');
            $url = $options['url'] ?? 'https://perlaprofil.oncloud.gr/s1services';

            $headers = array(
                "Content-Type: application/json;charset=windows-1253",
                "X-APPSMITH-DATATYPE: TEXT"
            );

            $data = json_encode($data);

            $curl = curl_init();

            curl_setopt_array(
                $curl,
                array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_POSTFIELDS => $data,
                    CURLOPT_HTTPHEADER => $headers,
                )
            );

            $response = curl_exec($curl);
            curl_close($curl);

            // Convert the entire response from ISO-8859-7 to UTF-8
            $response = mb_convert_encoding($response, 'UTF-8', 'ISO-8859-7');

            $response = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('JSON decode error: ' . json_last_error_msg());
            }

            return $response;
        }
    }

    $update_products = new Update_Products($request_settings);

}
?>