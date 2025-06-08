<?php 
namespace Sevhen\WooCancelOrder;

if(! defined('ABSPATH')) exit;

if(! class_exists('PluginTables')) {


    class PluginTables
    {

        /**
         * Initialize plugins schema 
         * 
         * @return bool
         */
        public function createTable()
        {

            global $wpdb;


            $charset_collate = $wpdb->get_charset_collate();

            $cancel_table = $wpdb->prefix. "cancel_order";


            // Verify if tables already exist

            if( $wpdb->get_var("SHOW TABLES LIKE '$cancel_table'" == $cancel_table) ) return;

             $sql_cancel_table = "
                CREATE TABLE $cancel_table (
                    cancel_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    wc_order_id BIGINT UNSIGNED NOT NULL,
                    reason TEXT NOT NULL,
                    status VARCHAR(100) NOT NULL,
                    PRIMARY KEY (cancel_id),
                    FOREIGN KEY (wc_order_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE
                ) $charset_collate;
            ";
 
            $wpdb->query($sql_cancel_table);

            if( $wpdb->get_var("SHOW TABLES LIKE '$cancel_table'" == $cancel_table) ) 
            
            if (!empty($wpdb->last_error)) {
                error_log(Utility::$plugin_name . $wpdb->last_error);
            }

            return error_log(Utility::$plugin_name. "Plugin's table is successfully created");
        }

    }

}
