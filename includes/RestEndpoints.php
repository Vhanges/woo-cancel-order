<?php 
namespace Sevhen\WooCancelOrder;

if (!defined('ABSPATH')) exit;

if (!class_exists('RestEndpoints')) {
    class RestEndpoints {

        private $namespace = 'cancel-order/v1';

        public function __construct() {
            add_action('rest_api_init', [$this, 'register_routes']);
        }

        public function register_routes() {
            register_rest_route($this->namespace, '/cancel-initiation', [
                'methods'  => 'GET',
                'callback' => [$this, 'initiate_cancel'],
                'permission_callback' => '__return_true'
            ]);
        }

        public function initiate_cancel() {
            return "HEY";
        }
    }
}
