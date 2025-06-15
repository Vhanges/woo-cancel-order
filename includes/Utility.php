<?php 
namespace Sevhen\WooCancelOrder;

use Exception;

if(! defined('ABSPATH')) exit;

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
use wpdb;


if(! class_exists('Utility')) {


    class Utility {

        public $plugin_name = "[Woo Cancel Order] ";

        protected $wpdb;


        private $table;

        private $status = [
            'request' => 'request-cancel',
            'approve' => 'approved-cancel',
            'reject' => 'cancel-rejected'
        ];


        public function __construct()
        {
            global $wpdb;
            $this->wpdb = $wpdb;
            $this->table = $wpdb->prefix. "cancel_order"; 
        }

        /**
         * ===========================================================
         *  Model
         * -----------------------------------------------------------
         *  Used for database interaction  
         * ===========================================================
         */

        protected function set_fetched_order($order_id) {

            global $wpdb;

            $current_time = current_time('mysql'); 
            $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes', strtotime($current_time)));

            $data = [
                'wc_order_id'  => $order_id,
                'created_at'   => $current_time,
                'expires_at'   => $expires_at
            ];

            $format = ['%d', '%s', '%s'];


            $result = $wpdb->insert($wpdb->prefix. "cancel_order", $data, $format);

            if ($result === false) {
                error_log("{$this->plugin_name} Failed to insert cancel order record for Order ID: $order_id");
            }

        }


        protected function get_cancel_request($order_id)
        {
            $query = $this->wpdb->prepare("SELECT * FROM $this->table WHERE wc_order_id = $order_id");
            return $this->wpdb->get_row($query);
        }

        protected function reject($order_id)
        {
            $order = \wc_get_order($order_id);
            $order->update_status($this->status['reject']);
            $order->save();

            return true;
        }

        protected function request($order_id, $reason):bool
        {
            global $wpdb;

            $result = true;

            if(!isset($order_id) && !isset($reason)) return false;

            $result = $wpdb->update(
                $wpdb->prefix. "cancel_order",
                [
                    'reason' => $reason,
                    'status' => $this->status['request']
                ],
                ['wc_order_id' => $order_id], 
                ['%s', '%s'],
                ['%d'] 
            );


            if(!$result){
                return $result = false;
            }

            return $result;

        }

        protected function approve($order_id)
        {
            $order = \wc_get_order($order_id);

            if ($order->get_status() !== $this->status['approve']) {
                $order->update_status($this->status['approve']);
                $order->save();
                return true;
            } else {
                error_log("$this->plugin_name Order ID {$order_id} is already approved. No update needed.");
                return false;
            }
        }

        /**
         * ===========================================================
         *  Helpers
         * -----------------------------------------------------------
         *  Utility functions for internal plugin operations
         * ===========================================================
         */

        /**
         * Retrieves the correct WooCommerce screen ID based on HPOS support.
         */
        protected function get_screen_id() 
        {

            return class_exists('\\Automattic\\WooCommerce\\Internal\\DataStores\\Orders\\CustomOrdersTableController') &&
            wc_get_container()->get(CustomOrdersTableController::class)->custom_orders_table_usage_is_enabled()
            ? wc_get_page_screen_id('shop-order')
            : 'shop_order';
        }

        /**
         * Retrieves the WooCommerce order object safely.
         */
        protected function get_order_object($post) {
            return is_a($post, 'WP_Post') ? wc_get_order($post->ID) : $post;
        }


        protected function validate_order($order_id)
        {
            try {
                global $wpdb;          
                

                $current_time = strtotime(current_time('mysql')); // Convert to timestamp
                $expired_threshold = date('Y-m-d H:i:s', $current_time);

                
                // Fetch the expiration time from the database
                $query = $wpdb->prepare(
                    "SELECT expires_at FROM {$wpdb->prefix}cancel_order WHERE wc_order_id = %d",
                    $order_id
                );
                
                $result = $wpdb->get_var($query);
                // Ensure both values are treated as timestamps
                if ($result && strtotime($expired_threshold) >= strtotime($result)) {
                    throw new Exception("$this->plugin_name The cancel request for order ID {$order_id} has expired.");
                }
                
                // error_log("Threshold: ". strtotime($expired_threshold) ." Due: ".strtotime($result));
                return $result ?: null;

            } catch (Exception $e) {
                error_log($e->getMessage());
                return null;
            }
        }



    }

}
