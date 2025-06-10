<?php 
namespace Sevhen\WooCancelOrder;

if(! defined('ABSPATH')) exit;

if(! class_exists('Utility')) {


    class Utility {

        public static $plugin_name = "[Woo Cancel Order] ";

        private $wpdb;


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

        public function get_cancel_request($order_id)
        {
            $query = $this->wpdb->prepare("SELECT * FROM $this->table WHERE wc_order_id = $order_id");
            return $this->wpdb->get_row($query);
        }

        public function  reject($order_id)
        {
            $order = \wc_get_order($order_id);
            $order->update_status($this->status['reject']);
            $order->save();

            return true;
        }

        public function request($order_id, $reason)
        {
            $result = true;

            $data = [
                'wc_order_id' => $order_id,
                'reason' => $reason,
                'status' => $this->status['request']
            ];

            $format = [
                '%s',
                '%s'
            ];

            $result = $this->wpdb->insert($this->table, $data, $format);

            if(!$result){
                return $result = false;
            }

            return $result;

        }

        public function approve($order_id)
        {
            $order = \wc_get_order($order_id);

            if ($order->get_status() !== $this->status['approve']) {
                $order->update_status($this->status['approve']);
                $order->save();
                return true;
            } else {
                error_log("Order ID {$order_id} is already approved. No update needed.");
                return false;
            }
        }

    }

}
