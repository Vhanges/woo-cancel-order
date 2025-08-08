<?php 
namespace Sevhen\WooCancelOrder;

if (!defined('ABSPATH')) exit;

if (!class_exists('CancelStatuses')) {

    class CancelStatuses
    {



        private $statuses = [
            'request-cancel' => 'Request for Cancel'
        ];

        // 'approved-cancel' => 'Cancel Approved',
        // 'cancel-rejected' => 'Cancel Rejected'

        public function __construct() {
            add_action('init', [$this, 'register_cancel_status']);
            add_filter('wc_order_statuses', [$this, 'add_cancel_status']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_status_styles']); 

            //Send an email when order status changed to the following status
            add_action( 'woocommerce_order_status_cancelled', [$this, 'cancelled']);
            add_action( 'woocommerce_order_status_request-cancel', [$this, 'request_cancel']);
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

        public function request_cancel( $order_id ) {
            $order = wc_get_order( $order_id );
            $admin_email = get_option( 'admin_email' );

            $subject = 'Cancellation Request Received';
            $heading = 'Customer Requested Cancellation';

            $edit_link = admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' );
            $edit_anchor = '<p><a href="' . esc_url( $edit_link ) . '" target="_blank" style="color:#0071a1;">📝 Click here to review and edit the order</a></p>';


            $mailer = \WC()->mailer();
            $email  = $mailer->emails['WC_Email_New_Order'];

            ob_start();

            do_action( 'woocommerce_email_header', $heading, $email );

            echo wpautop( sprintf(
                'Order #%d has been marked as "Request for Cancellation" by the customer.',
                $order->get_id()
            ) );
            echo $edit_anchor;

            do_action( 'woocommerce_email_footer', $email );

            $styled_html = ob_get_clean();

            $headers = [ 'Content-Type: text/html; charset=UTF-8' ];

            wp_mail( $admin_email, $subject, $styled_html, $headers );
        }

        public function cancelled( $order_id ) {
            \WC()->mailer()->emails['WC_Email_Cancelled_Order']->trigger( $order_id );
        }



    }

}
