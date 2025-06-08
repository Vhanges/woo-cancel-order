<?php 
namespace Sevhen\WooCancelOrder;

if (!defined('ABSPATH')) exit;

if (!class_exists('CancelStatuses')) {

    class CancelStatuses {

        private $statuses = [
            'request-cancel' => 'Request for Cancel',
            'approved-cancel' => 'Cancel Approved',
            'cancel-rejected' => 'Cancel Rejected'
        ];

        public function __construct() {
            add_action('init', [$this, 'register_cancel_status']);
            add_filter('wc_order_statuses', [$this, 'add_cancel_status']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_status_styles']); 
        }

        /** 
         * Register multiple custom WooCommerce order statuses.
         */
        public function register_cancel_status() {
            foreach ($this->statuses as $status_key => $status_label) {
                register_post_status("wc-$status_key", [
                    'label'                     => $status_label,
                    'public'                    => true,
                    'show_in_admin_status_list'  => true,
                    'label_count'                => _n_noop("$status_label (%s)", "$status_label (%s)")
                ]);
            }
        }

        /** 
         * Add custom statuses to WooCommerce's order status list.
         */
        public function add_cancel_status($cancel_statuses) {
            foreach ($this->statuses as $status_key => $status_label) {
                $cancel_statuses["wc-$status_key"] = $status_label;
            }
            return $cancel_statuses;
        }

        /** 
         * Enqueue admin styles for order status colors (WooCommerce Orders Page only).
         */
        public function enqueue_status_styles() {
            $screen = get_current_screen();

            if (isset($screen) && $screen->id === 'woocommerce_page_wc-orders') {
                wp_enqueue_style('woo-cancel-order-status-style', plugin_dir_url(dirname(__FILE__)) . 'assets/css/index.css');
            }
        }
    }

}
