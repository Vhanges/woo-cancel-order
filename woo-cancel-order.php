<?php
/*
Plugin Name: Woo Cancel Order
Plugin URI: https://yourwebsite.com/woo-cancel-order
Description: Allows customers and seller have flexibility in cancelling woocommerce orders.
Version: 1.0.0
Author: Sevhen
Author URI: https://vhanges.github.io
License: GPL2
Text Domain: woo-cancel-order
*/

if(! defined('ABSPATH')) exit;


require_once plugin_dir_path(__FILE__). 'vendor/autoload.php';

use Sevhen\WooCancelOrder\CancelStatuses;
use Sevhen\WooCancelOrder\PluginTables;

if (!class_exists ('Woo_Cancel_Order')){

    class Woo_Cancel_Order
    {
         
        public function __construct()
        {
            //Initialize plugin database tables
            register_activation_hook(__FILE__, [$this, 'initializeTables']);

            //Register Cancel Statuses
            new CancelStatuses();
        
        }

        public function initializeTables(){

           $table = new PluginTables();
           $table->createTable(); 

        }

    }

    new Woo_Cancel_Order();

}