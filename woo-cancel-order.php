<?php
/*
Plugin Name: Woo Cancel Order
Plugin URI: https://yourwebsite.com/woo-cancel-order
Description: Allows customers and seller have flexibility in cancelling woocommerce orders.
Version: 1.0.0
Author: Sevhen
Author URI: https://vhanges.github.io
License: GPL2
Text Domain: woo-cancel-order
*/

if(! defined('ABSPATH')) exit;


require_once plugin_dir_path(__FILE__). 'vendor/autoload.php';

use Sevhen\WooCancelOrder\CancelOrder;
use Sevhen\WooCancelOrder\CancelStatuses;
use Sevhen\WooCancelOrder\WooElements;
use Sevhen\WooCancelOrder\PluginTables;

if (!class_exists ('Woo_Cancel_Order')){

    class Woo_Cancel_Order
    {
         
        public function __construct()
        {
            //Initialize plugin database tables
            register_activation_hook(__FILE__, [$this, 'initialize_tables']);

            // Enqueue Scripts
            add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
            //Register Cancel Statuses
            new CancelStatuses();
            new CancelOrder();
            new WooElements();
        
        }

        // Once triggered, migrations of the plugins table will be initiated
        public function initialize_tables(){

           $table = new PluginTables();
           $table->createTable(); 

        }

        // Enqueue important scripts for internal and external plugin operations
        public function enqueue_scripts() {


            wp_enqueue_script('cancel-ajax-script', plugin_dir_url(__FILE__) . 'assets/js/index.js', array('jquery'), 1.0, true);

            wp_localize_script('cancel-ajax-script', 'cancel_ajax_object', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('plugin_nonce')
            ]);

        }

    }

    new Woo_Cancel_Order();

}