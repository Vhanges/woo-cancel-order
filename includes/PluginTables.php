<?php 
namespace Sevhen\WooCancelOrder;

if(! defined('ABSPATH')) exit;

if(! class_exists('PluginTables')) {


    class PluginTables {

        public function createTable()
        {
            error_log("HEY");
        }

    }

}
