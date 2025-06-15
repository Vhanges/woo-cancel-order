<?php 
namespace Sevhen\WooCancelOrder;

if(! defined('ABSPATH')) exit;

use Sevhen\WooCancelOrder\Utility;

if(! class_exists('CancelOrder')) {


    class CancelOrder extends Utility{

        
        public function __construct()
        {
            // Grabs the ID when checkout order is processed
            add_action("woocommerce_new_order", [$this, "get_order"], 10, 1);
            
            // Register AJAX handlers
            add_action("wp_ajax_woo_fetch_cancel_request", [$this, "ajax_fetch_cancel_request"]);
            add_action("wp_ajax_woo_cancel_order_approve", [$this, "ajax_approve_order"]);
            add_action("wp_ajax_woo_cancel_order_reject", [$this, "ajax_reject_order"]);
            add_action("wp_ajax_woo_cancel_request", [$this, "ajax_cancel_request"]);
            add_action("wp_ajax_nopriv_woo_cancel_request", [$this, "ajax_cancel_request"]);
        }

        /**
         *  Get customer order id when order is processed
         */

        public function get_order($order_id)
        {
            $this->set_fetched_order($order_id);
        }

        /**
         * Handle AJAX approval request.
         */
        public function ajax_approve_order()
        {
            if (!isset($_POST['order_id'])) {
                wp_send_json_error(["message" => "Invalid order ID"]);
            }

            $order_id = intval($_POST['order_id']);
            $result = $this->approve(order_id: $order_id);

            if ($result) {

                wp_send_json_success([
                    "message" => "Order approved successfully",
                    "redirect" => wp_get_referer() ?: admin_url('edit.php?post_type=shop_order')
                ]);
                $link = wp_get_referer();
                error_log($link);

                
            } else {
                wp_send_json_error(["message" => "Order is already approved or an error occurred"]);
            }
        }

        /**
         * Handle AJAX rejection request.
         */
        public function ajax_reject_order()
        {
            if (!isset($_POST['order_id'])) {
                wp_send_json_error(["message" => "Invalid order ID"]);
            }

            $order_id = intval($_POST['order_id']);
            $result = $this->reject($order_id);

            if ($result) {
                wp_send_json_success(["message" => "Order rejected successfully"]);
            } else {
                wp_send_json_error(["message" => "Failed to reject order"]);
            }
        }

        /**
         * Handle AJAX request to fetch cancellation data.
         */
        public function ajax_fetch_cancel_request()
        {
            if (!isset($_POST['order_id'])) {
                wp_send_json_error(["message" => "Invalid order ID"]);
            }

            $order_id = intval($_POST['order_id']);
            $data = $this->get_cancel_request($order_id);

            if ($data) {
                wp_send_json_success($data);
            } else {
                wp_send_json_error(["message" => "No cancellation request found"]);
            }
        }

        /**
         * Handle AJAX request to request for cancellation 
         */
        public function ajax_cancel_request()
        {
            if (!isset($_POST['order_id'])) {
                wp_send_json_error(["message" => "Invalid order ID"]);
            }

            $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
            $reason = isset($_POST['reason']) ? sanitize_text_field($_POST['reason']) : '';

            $result = $this->request($order_id, $reason);

            if ($result) {
                wp_send_json_success($result);
            } else {
                wp_send_json_error(["message" => "An error occured. Please try again"]);
            }
        }


    }

}
