<?php 
namespace Sevhen\WooCancelOrder;

if (!defined('ABSPATH')) exit;

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

class Metabox {
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'register_meta_box']);
    }

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
     * Retrieves correct WooCommerce screen ID based on HPOS support.
     */
    private function get_screen_id() {
        return class_exists('\\Automattic\\WooCommerce\\Internal\\DataStores\\Orders\\CustomOrdersTableController') &&
               wc_get_container()->get(CustomOrdersTableController::class)->custom_orders_table_usage_is_enabled()
               ? wc_get_page_screen_id('shop-order')
               : 'shop_order';
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
            echo '<button type="button" class="button button-primary woo-cancel-order-action" data-action="woo_cancel_order_reject"  data-order_id="'.$order_id.'"style="background:#d63638; border-color:#d63638;">Reject</button>';
            echo '<button type="button" class="button button-primary woo-cancel-order-action" data-action="woo_cancel_order_approve" data-order_id="'.$order_id.'"style="background:#00a32a; border-color:#00a32a;">Approve</button>';
            echo '</div>';
        echo '</div>';
    }

    /**
     * Retrieves the WooCommerce order object safely.
     */
    private function get_order_object($post) {
        return is_a($post, 'WP_Post') ? wc_get_order($post->ID) : $post;
    }


}
