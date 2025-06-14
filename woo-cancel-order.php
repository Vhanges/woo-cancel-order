<?php

/*
Plugin Name: Woo Cancel Order
Plugin URI: https://yourwebsite.com/woo-cancel-order
Description: Allows customers and sellers flexibility in canceling WooCommerce orders.
Version: 1.0.0
Author: Sevhen
Author URI: https://vhanges.github.io
License: GPL2
Text Domain: woo-cancel-order
*/

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

use Sevhen\WooCancelOrder\CancelOrder;
use Sevhen\WooCancelOrder\CancelStatuses;
use Sevhen\WooCancelOrder\WooElements;
use Sevhen\WooCancelOrder\PluginTables;
use Sevhen\WooCancelOrder\RestEndpoints;

if (!class_exists('Woo_Cancel_Order')) {
    class Woo_Cancel_Order {
        
        public function __construct() {
            
            // Initialize plugin database tables
            register_activation_hook(__FILE__, callback: [$this, 'initialize_tables']);

            // Enqueue Admin Scripts
            add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);

            // Enqueue Customer Scripts
            add_action('wp_enqueue_scripts', [$this, 'customer_enqueue_scripts']);

            // Register Cancel Order Components
            new CancelStatuses();
            new CancelOrder();
            new WooElements();
            new RestEndpoints();
        }

        // Initialize plugin database tables on activation
        public function initialize_tables() {
            $table = new PluginTables();
            $table->createTable();
        }

        // Enqueue admin scripts for backend operations
        public function admin_enqueue_scripts() {
            wp_enqueue_script(
                'admin-cancel-ajax-script',
                plugin_dir_url(__FILE__) . 'assets/js/admin.js',
                ['jquery'], '1.0', true
            );

            wp_localize_script('admin-cancel-ajax-script', 'cancel_ajax_object', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('plugin_nonce')
            ]);
        }
        
        // Enqueue customer scripts for frontend interactions
        public function customer_enqueue_scripts() {
            wp_enqueue_script(
                'customer-cancel-ajax-script',
                plugin_dir_url(__FILE__) . 'assets/js/customer.js',
                ['jquery'], '1.0', true
            );

            wp_enqueue_style(
                'cancel-form-css',
                plugins_url('assets/css/cancel-form.css', __FILE__)
            );

            wp_localize_script('customer-cancel-ajax-script', 'cancel_ajax_object', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('plugin_nonce')
            ]);
        }
    }

    new Woo_Cancel_Order();
}
