<?php

require_once DI_CRAWLER_DIR . '/includes/di_crawler_fetch_products.php';
require_once DI_CRAWLER_DIR . '/includes/di_crawler_fetch_furnizori.php';
require_once DI_CRAWLER_DIR . '/includes/di_crawler_fetch_selected_products.php';
require_once DI_CRAWLER_DIR . '/includes/di_crawler_order.php';



function di_crawler_fetch_data(){
    
    $furnizori = fetch_furnizori();

}

add_action('rest_api_init','di_crawler_generate_api');

function di_crawler_generate_api(){
    register_rest_route('di-api','/lista-furnizori/',array(
        'methods' => 'GET',
        'callback' => 'di_cralwer_api_lista_furnizori',
        'permission_callback' => '__return_true'
    ));
    register_rest_route('di-api','/set-product-fetch-status/',array(
        'methods' => 'POST',
        'callback' => 'di_cralwer_change_product_fetch_status','permission_callback' => '__return_true'
    ));
}

function di_cralwer_api_lista_furnizori() {
    global $wpdb;
    $furnizori = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."di_crawler_furnizori`");

    return $furnizori;
}

function di_cralwer_change_product_fetch_status(){

    global $wpdb;

    $id_produs = $_POST['id_produs'];
    $id_furnizor = $_POST['id_furnizor'];

    $actual_status = $wpdb->get_var("SELECT `is_full_fetchable` FROM `". $wpdb->prefix ."di_crawler_fetched_products` WHERE `furnizor_id`='".$id_furnizor."' AND `furnizor_product_id`='".$id_produs."' ");
    if(strval($actual_status) === "1"){
        $wpdb->update($wpdb->prefix . 'di_crawler_fetched_products',array(
            'is_full_fetchable'=>0
        ),
        array(
            'furnizor_id'=>$id_furnizor,
            'furnizor_product_id'=>$id_produs
        ),
        array(
            "%s"
        ));

        echo json_encode(array("status"=>"success","fetchStatus"=>0));
    }
    else{
        $wpdb->update($wpdb->prefix . 'di_crawler_fetched_products',array(
            'is_full_fetchable'=>1
        ),
        array(
            'furnizor_id'=>$id_furnizor,
            'furnizor_product_id'=>$id_produs
        ),
        array(
            "%s"
        ));
        echo json_encode(array("status"=>"success","fetchStatus"=>1));

    }
}





?>