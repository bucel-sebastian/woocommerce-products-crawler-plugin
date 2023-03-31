<?php

/**
 * Plugin Name: di_agency products crawler
 * Plugin URI: 
 * Description:
 * Version: 1.0
 * Author: di_agency
 * Author URI: https://diagency.eu/
 */

if ( !defined('ABSPATH')) {
    exit;
}

if( !defined('DI_CRAWLER_DIR') ) {
    define('DI_CRAWLER_DIR', plugin_dir_path(__FILE__));
}

if( !defined('DI_CRAWLER_URL') ) {
    define('DI_CRAWLER_URL', plugin_dir_path(__FILE__));
}

function test_api($data) {
    echo "bine ai venit!";
}

if( !class_exists('Di_Crawler') ) {

    class Di_Crawler {

        public function __construct() {
            $this->di_crawler_init();
        }

        public function di_crawler_init() {
            global $wpdb;
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';

            require_once DI_CRAWLER_DIR . '/includes/di_crawler_functions.php';
            if( is_admin() ) {
                require_once DI_CRAWLER_DIR . '/includes/admin/di_crawler_admin.php';
            }
            else {

            }


            $dbTableFurnizori = $wpdb->prefix . 'di_crawler_furnizori';
            $dbTableFurnizoriSql = 'CREATE TABLE `' . $dbTableFurnizori . '` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `nume_furnizor` varchar(255) NOT NULL,
                    `url_furnizor` varchar(255) NOT NULL,
                    `api_token` varchar(255),
                    `api_status` varchar(10) NOT NULL,
                    `api_status_date` datetime NOT NULL,
                    `add_date` datetime NOT NULL,
                    `add_author` varchar(10) NOT NULL,
                    `last_fetch` datetime,
                    PRIMARY KEY (`id`)
                )';
            maybe_create_table($dbTableFurnizori,$dbTableFurnizoriSql);
            
            $dbTableCategoryAssing = $wpdb->prefix . 'di_crawler_categories_assign';
            $dbTableCategoryAssingSql = 'CREATE TABLE `' . $dbTableCategoryAssing .'`(
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `furnizor_id` int(11),
                `furnizor_category_id` varchar(20) NOT NULL,
                `furnizor_category_name` varchar(255) NOT NULL,
                `category_id` varchar(20) NOT NULL,
                `rule_type` varchar(10),
                `rule` varchar(255),
                PRIMARY KEY (`id`)
            )';
            maybe_create_table($dbTableCategoryAssing,$dbTableCategoryAssingSql);

            $dbTableFetchedProductsData = $wpdb->prefix . 'di_crawler_fetched_products';
            $dbTableFetchedProductsDataSql = 'CREATE TABLE `' . $dbTableFetchedProductsData . '` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `product_id` varchar(255) NOT NULL,
                    `furnizor_id` varchar(255) NOT NULL,com
                    `fetched_date` datetime NOT NULL,
                    `furnizor_product_id` varchar(255),
                    `is_full_fetchable` int(11) NOT NULL,
                    `last_update_date` datetime,
                    `product_data` text,
                    PRIMARY KEY (`id`)
                )';
            maybe_create_table($dbTableFetchedProductsData,$dbTableFetchedProductsDataSql);

            $dbTableOrders = $wpdb->prefix . 'di_crawler_orders';
            $dbTableOrdersSql = 'CREATE TABLE `' . $dbTableOrders . '` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `parent_order_id` varchar(255) NOT NULL,
                    `furnizor_id` varchar(255) NOT NULL,
                    `furnizor_order_id` varchar(255) NOT NULL,
                    `order_product_id` text NOT NULL,
                    `order_status` text NOT NULL,
                    PRIMARY KEY (`id`)
                )';
                maybe_create_table($dbTableOrders,$dbTableOrdersSql);
            $this->activate_crawler_fetch_cron();
        }

        

        public function activate_crawler_fetch_cron() {
            if( !wp_next_scheduled('di_crawler_fetch_selected_products') ){
                wp_schedule_event(time(),'daily','di_crawler_fetch_selected_products');
            }
        }

        public function di_crawler_generate_api() {
            register_rest_route('test/v1','/produs/feed/',array(
                'methods' => 'GET',
                'callback' => 'test_api'
            ));
        }
    }


    new Di_Crawler();
}



