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
            'approve' => 'cancelled',
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

        protected function get_cancel_request($order_id) {
            global $wpdb;
            $table = $wpdb->prefix . "cancel_order";

            $query = $wpdb->prepare(
                "SELECT * FROM $table WHERE wc_order_id = %d AND status = %s",
                $order_id,
                $this->status['request']
            );

            $result = $wpdb->get_row($query);

            // error_log("RESULT: " . print_r($result, true));

            if(! $result) {
                return null;
            }

            return $result;


        }


        protected function reject($order_id)
        {
            $order = \wc_get_order($order_id);

            $to = $order->get_billing_email();
            $subject = 'Your Cancellation Request Was Rejected';
            $heading = 'Cancellation Rejected';

            $message = sprintf(
                'Hi %s,<br><br>Thank you for your cancellation request for Order #%d. After careful review, we’re unable to approve your request. If you have any questions or need further assistance, please feel free to contact our support team.<br><br>Thank you for your understanding.',
                $order->get_billing_first_name(),
                $order->get_id()
            );

            $wrapped = \WC()->mailer()->wrap_message( $heading, $message );
            $headers = [ 'Content-Type: text/html; charset=UTF-8' ];

            wp_mail( $to, $subject, $wrapped, $headers );

            return true;
        }

        protected function request($order_id, $reason)
        {
            global $wpdb;

            $result = true;

            if(!isset($order_id) && !isset($reason)) return false;

            $validation_result = $this->validate_order($order_id);
            if ($validation_result == null) {
                error_log("THIS ORDER IS EXPIRED!");
                
                wp_send_json_error([
                    'message' => "We're sorry, but the cancellation period for your order has expired and we can't process this request. If you have any questions, please contact our support team."
                ]);

                return;
            }

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

            $order = \wc_get_order($order_id);
            $order->update_status($this->status['request']);
            $order->save();


            if(!$result){
                return false;
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
                
                error_log("Threshold: ". strtotime($expired_threshold) ." Due: ".strtotime($result));
                return $result ?: null;

            } catch (Exception $e) {
                error_log($e->getMessage());
                return null;
            }
        }



    }

}
