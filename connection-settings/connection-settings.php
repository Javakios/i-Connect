<?php 


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


if(!class_exists('Connection_Settings')){


    class Connection_Settings {
        public function __construct(){

            add_action('admin_menu',array($this,'add_submenu'));
            add_action('admin_init', array($this, 'settings_init'));

        }


        public function add_submenu(){
            add_options_page(
                'Softone Connection',
                'Softone Connection',
                'manage_options',
                'update-products-settings',
                array($this, 'settings_page')
            );
        }

        public function settings_page()
        {
            ?>
            <div class="wrap">
                <h1>i-Connect Configuration</h1>
                <form action="options.php" method="post">
                    <?php
                    settings_fields('update-products-settings');
                    do_settings_sections('update-products-settings');
                    submit_button('Save Settings');
                    ?>
                </form>
            </div>
            <?php
        }
        public function settings_init()
        {
            // Register a new setting for "update-products" page
            register_setting('update-products-settings', 'update_products_settings');

            // Register a new section in the "update-products" page
            add_settings_section(
                'update_products_section',
                'API Settings',
                array($this, 'settings_section_callback'),
                'update-products-settings'
            );

            $fields = array(
                'url' => 'URL',
                'username' => 'Username',
                'password' => 'Password',
                'appId' => 'App ID',
                'company'=>'Company',
                'branch' => 'Branch',
                'module' => 'Module',
                'refid' => 'Refid'


            );

            foreach ($fields as $id => $title) {
                // Register new fields in the "update_products_section" section, inside the "update-products" page
                add_settings_field(
                    $id,
                    $title,
                    array($this, 'settings_field_callback'),
                    'update-products-settings',
                    'update_products_section',
                    array('label_for' => $id, 'default' => '')
                );
            }
        }
        public function settings_section_callback()
        {
            echo '<p>Enter your API settings below:</p>';
        }
        public function settings_field_callback($args)
        {
            // Get the value of the setting we've registered with register_setting()
            $options = get_option('update_products_settings');
            ?>
            <input type="text" id="<?php echo esc_attr($args['label_for']); ?>" name="update_products_settings[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo esc_attr($options[$args['label_for']] ?? $args['default']); ?>">
            <?php
        }
    }


}