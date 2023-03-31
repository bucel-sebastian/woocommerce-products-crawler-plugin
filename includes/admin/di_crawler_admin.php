<?php

if ( !defined('ABSPATH') ) {
    exit;
}

if( !class_exists('Di_crawler_admin') ) {
    class Di_crawler_admin {
        public function __construct() {
            add_action('admin_menu',array($this,'di_crawler_admin_add_menu_page'));
        }

        public function di_crawler_admin_add_menu_page() {

            add_menu_page('di Products Crawler','di Products Crawler','manage_options','di-crawler-admin-menu',array($this,'di_crawler_admin_homepage'),'dashicons-star-filled',8);

            add_submenu_page('di-crawler-admin-menu','Furnizori','Furnizori','manage_options','di-crawler-admin-furnizori',array($this,'di_crawler_admin_furnizori'));
            add_submenu_page('di-crawler-admin-menu','Categorii','Categorii','manage_options','di-crawler-admin-categori',array($this,'di_crawler_admin_categori'));
            add_submenu_page('di-crawler-admin-menu','Produse','Produse','manage_options','di-crawler-admin-produse',array($this,'di_crawler_admin_produse'));
            add_submenu_page('di-crawler-admin-menu','Comenzi','Comenzi','manage_options','di-crawler-admin-comenzi',array($this,'di_crawler_admin_comenzi'));

        }

        public static function di_crawler_admin_homepage() {
            include_once DI_CRAWLER_DIR . '/includes/admin/pages/di-crawler-admin-homepage.php';
        }

        public static function di_crawler_admin_furnizori() {
            include_once DI_CRAWLER_DIR . '/includes/admin/pages/di-crawler-admin-furnizori.php';
        }

        public static function di_crawler_admin_produse() {
            include_once DI_CRAWLER_DIR . '/includes/admin/pages/di-crawler-admin-produse.php';
        }

        public static function di_crawler_admin_categori() {
            include_once DI_CRAWLER_DIR . '/includes/admin/pages/di-crawler-admin-categori.php';
        }

        public static function di_crawler_admin_comenzi() {
            include_once DI_CRAWLER_DIR . '/includes/admin/pages/di-crawler-admin-comenzi.php';
        }

    }

    new Di_crawler_admin();
}



?>
