<?php 
namespace Sevhen\WooCancelOrder;

if (!defined('ABSPATH')) exit;

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

class Metabox {
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'register_meta_box']);
    }

    public function register_meta_box() {
        $screen = class_exists('\\Automattic\\WooCommerce\\Internal\\DataStores\\Orders\\CustomOrdersTableController') &&
                  wc_get_container()->get(CustomOrdersTableController::class)->custom_orders_table_usage_is_enabled()
                  ? wc_get_page_screen_id('shop-order')
                  : 'shop_order';

        add_meta_box(
            'cancel-meta-box',
            __('Request for Cancel', 'woo-cancel-order'),
            [$this, 'meta_box_content'],
            $screen,
            'advanced',
            'core'
        );
    }

    public function meta_box_content($post) {
        $order = is_a($post, 'WP_Post') ? wc_get_order($post->ID) : $post;
        // esc_html($order->get_id())
        echo '<div class="custom-order-meta-box" style="padding:30px; border:1px solid #e5e5e5; background:#fafafa;">';
            echo '<h1 style="font-size:20px; margin-bottom:10px;"><strong>This customer is requesting for cancellation</strong></h1>';
            echo '<h3 style="margin-bottom:5px;"><strong>Reason: </strong></h3>';
            echo '<p style="margin-bottom:20px;">I think I might be smarter than Iron Man so I will cancel this order. Thanks</p>';
            echo '<div style="display:flex; gap:10px; justify-content: end; border-top: solid 1px #EDEDED; padding: 20px 0 0 0;">';
            echo '<button type="button" class="button button-primary" style="background:#d63638; border-color:#d63638;">Reject</button>';
            echo '<button type="button" class="button button-primary" style="background:#00a32a; border-color:#00a32a;">Approve</button>';
            echo '</div>';
        echo '</div>';
    }
}
