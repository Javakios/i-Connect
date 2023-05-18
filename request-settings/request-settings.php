<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('Request_Settings')) {
    class Request_Settings {
        public function __construct() {
            add_action('admin_menu', array($this, 'add_submenu'));
            add_action('admin_init', array($this, 'request_init'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
            add_action('wp_ajax_create_new_request_type', array($this, 'create_new_request_type'));
        }
        
        public function create_new_request_type() {
            check_ajax_referer('create_new_request_type', 'security');


            $request_name = sanitize_text_field($_POST['request_name'] ?? '');
            $service = sanitize_text_field($_POST['service'] ?? '');
            $sqlName = sanitize_text_field($_POST['sqlName'] ?? '');
            $request_type = sanitize_text_field($_POST['request_type'] ?? '');

            if ($request_name && $service && $sqlName && $request_type) {
                $options = get_option('request_settings', array());
                $options[$request_name] = array(
                    'service' => $service,
                    'sqlName' => $sqlName,
                    'request_type'=>$request_type
                );
                update_option('request_settings', $options);
                echo 'Success';
            } else {
                echo 'Failure';
            }

            wp_die();
        }

        public function add_submenu() {
            add_options_page('Request Settings', 'Request Settings', 'manage_options', 'request-settings', array($this, 'request_page'));
        }

        public function request_page() {
            ?>
            <div class="wrap">
                <h1>i-Connect Request Settings</h1>
                <?php $this->display_form(); ?>
                <form action="options.php" method="post">
                    <?php
                    settings_fields('request-settings');
                    do_settings_sections('request-settings');
                    submit_button('Save Settings');
                    ?>
                </form>
            </div>
            <?php
        }

        public function enqueue_scripts() {
            wp_enqueue_script('request-settings', plugin_dir_url(__FILE__) . 'request-settings.js', array('jquery'), false, true);
            wp_localize_script('request-settings', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
        }
        
        public function get_all_request_types() {
            return get_option('request_settings', []);
        }

        public function display_form() {
            ?>
            <form id="new-request-form">
                <input type="text" id="request_name" name="request_name" placeholder="Request Name">
                <input type="text" id="service" name="service" placeholder="Service">
                <input type="text" id="sqlName" name="sqlName" placeholder="SQL Name">
                <select name="request_type" id="request_type">
                    <option value="PRODUCTS">Update Products</option>
                    <option value="STOCK">Update Stock</option>
                    <option value="CATEGORIES">Update Categories</option>
                    <option value="USERS">Update Users</option>
                    <option value="SUBCATEGORIE">Update Subcategories</option>
                </select>
                <?php wp_nonce_field('create_new_request_type', '_wpnonce', true, true); // Add nonce field ?>
                <button type="submit">Create New Request Type</button>
            </form>
            <?php
        }
        

        public function request_init() {
            register_setting('request-settings', 'request_settings');
            add_settings_section('requests_settings', 'Request Settings', array($this, 'request_section_callback'), 'request-settings');
        }

        public function request_section_callback() {
            $options = get_option('request_settings');
            if (is_array($options)) {
                foreach ($options as $request_name => $request_type) {
                    echo '<h3>' . esc_html($request_name) . '</h3>';
                    if (isset($request_type['service']) && isset($request_type['sqlName'])) {
                        echo '<input type="text" name="request_settings[' . esc_attr($request_name) . '][service]" value="' . esc_attr($request_type['service']) . '">';
                        echo '<input type="text" name="request_settings[' . esc_attr($request_name) . '][sqlName]" value="' . esc_attr($request_type['sqlName']) . '">';
                    }
                }
            }
        }
    }
}

?>
