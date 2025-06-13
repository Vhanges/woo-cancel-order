<?php 
namespace Sevhen\WooCancelOrder;

if (!defined('ABSPATH')) exit;

use Sevhen\WooCancelOrder\Utility;

if (!class_exists('WooElements')){

    class WooElements extends Utility {
        public function __construct() {
            add_action('add_meta_boxes', [$this, 'register_meta_box']);

            
            add_filter('woocommerce_my_account_my_orders_actions', [$this, 'customer_cancel_button_action'], 10, 2);
        }

        /**
         * ===========================================================
         *  Metaboxes
         * ===========================================================
         */

        
        /**
         * Registers the custom meta box in WooCommerce admin order page.
         */
        public function register_meta_box() {
            $screen = $this->get_screen_id();
            
            add_meta_box(
                'cancel-meta-box',
                __('Request for Cancel', 'woo-cancel-order'),
                [$this, 'render_meta_box'],
                $screen,
                'advanced',
                'core'
            );
        }
        
        
        /**
         * Renders the cancel request meta box inside WooCommerce admin order page.
         */
        public function render_meta_box($post) {
            $order = $this->get_order_object($post);
            $order_id = $order ? $order->get_id() : 0;
            
            // Output the meta box HTML
            echo '<div class="custom-order-meta-box" style="padding:30px; border:1px solid #e5e5e5; background:#fafafa;">';
            echo '<h1 style="font-size:20px; margin-bottom:10px;"><strong>This customer is requesting cancellation</strong></h1>';
            echo '<h3 style="margin-bottom:5px;"><strong>Reason: </strong></h3>';
            echo '<p style="margin-bottom:20px;">I think I might be smarter than Iron Man, so I will cancel this order. Thanks.</p>';
            echo '<div style="display:flex; gap:10px; justify-content:end; border-top:solid 1px #EDEDED; padding:20px 0 0 0;">';
            echo '<button type="button" class="button button-primary cancel-admin-action" data-action="woo_cancel_order_reject"  data-order_id="'.$order_id.'"style="background:#d63638; border-color:#d63638;">Reject</button>';
            echo '<button type="button" class="button button-primary cancel-admin-action" data-action="woo_cancel_order_approve" data-order_id="'.$order_id.'"style="background:#00a32a; border-color:#00a32a;">Approve</button>';
            echo '</div>';
            echo '</div>';
        }
                
        /**
         * ===========================================================
         *  Customer Order Action Button
         * ===========================================================
         */

        /**
         * TODO List:
         * - [ ] Create a rest endpoint for action to work and load .
         * - [ ] Create a separate page to handle the cancel form template.
         * - [ ] Create another action button for users to be able to check their cancel request details.
         * - [ ] Modify logic on when to display the action button implement the expired_at column for verfication.
         * - [ ] Create an event job on plugin tables so we can delete expired and unused requests.
         */

        public function customer_cancel_button_action($actions, $order) {

            if (!($order instanceof \WC_Order)) {
            return $actions;
            }

            $order_id = $order->get_id();

            if ($this->validate_order($order_id) === null) {
            return $actions;
            }


            // Add custom button WITH a URL
            $actions['initiate_cancel_order'] = [
                'url'   => '#', 
                'name'  => __('Cancel Order', 'woo-cancel-order'),
                'class' => 'customer-initiate-cancel',
                'data-order-id' => $order_id 
            ];

        

            return $actions;
        }



    }

}