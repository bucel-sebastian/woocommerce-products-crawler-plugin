<?php
global $wpdb;

// require_once(ABSPATH . 'wp-admin/includes/media.php');
// require_once(ABSPATH . 'wp-admin/includes/file.php');
// require_once(ABSPATH . 'wp-admin/includes/image.php');


// $array_date_furnizori = array();

// $lista_furnizori = $wpdb->get_results("
//     SELECT * FROM `" . $wpdb->prefix . "di_crawler_furnizori` WHERE `api_status` = '1'
// ");

// foreach ($lista_furnizori as $furnizor) {

//     $lista_categorii = $wpdb->get_results("
//         SELECT * FROM `" . $wpdb->prefix . "di_crawler_categories_assign` WHERE `furnizor_id` = '" . $furnizor->id . "' AND `category_id` != ''
//     ");

    
   
//     $lista_produse_selectate = $wpdb->get_results("
//         SELECT * FROM `".$wpdb->prefix."di_crawler_fetched_products` WHERE `furnizor_id` = '".$furnizor->id."' AND `is_full_fetchable` = '1'
//     ");

//     array_push($array_date_furnizori,array("furnizor" => $furnizor, "categorii" => $lista_categorii, "produse" => $lista_produse_selectate));
// }

// foreach ($array_date_furnizori as $date_furnizor) {

//     $furnizor = $date_furnizor['furnizor'];
//     $categorii_data = $date_furnizor['categorii'];
//     $produse_selectate = $date_furnizor['produse'];

//     $produse_selecate_ids = array();

//     foreach ($produse_selectate as $produs_selectat) {
//         array_push($produse_selecate_ids,$produs_selectat->furnizor_product_id);
//     }
   

//     $fetch_produse = wp_remote_get($furnizor->url_furnizor ."/wp-json/" . $furnizor->api_token . "/feed/products/");
//     $fetch_produse = wp_remote_retrieve_body($fetch_produse);
//     $fetch_produse = json_decode($fetch_produse);

//     $produse_de_adaugat_in_magazin = array();
//     foreach ($fetch_produse as $produs) {
//         $product_exists = false;

//         if(in_array(strval($produs->product_id),$produse_selecate_ids)){
//             foreach ($produse_selectate as $produs_selectat) {
//                 if(strval($produs->product_id) == $produs_selectat->furnizor_product_id){
//                     if($produs_selectat->product_id != ''){
//                         $product_exists = true; 
//                         array_push($produse_de_adaugat_in_magazin,array("produs"=>$produs,"exista"=>$product_exists,"product_id"=>$produs_selectat->product_id));
//                     }
//                     else{
//                         array_push($produse_de_adaugat_in_magazin,array("produs"=>$produs,"exista"=>$product_exists));
//                     }
//                 }
//             }
            
//         }
//     }



//     foreach ($produse_de_adaugat_in_magazin as $produs_de_adaugat_in_magazin) {
        
//         // echo var_dump($produs_de_adaugat_in_magazin);
//         $date_produs = $produs_de_adaugat_in_magazin['produs'];
//         $produsul_exista = $produs_de_adaugat_in_magazin['exista'];

//         if($produsul_exista === true){
//             $intern_product_id = $produs_de_adaugat_in_magazin['product_id'];
            

//             $edit_product = wc_get_product($intern_product_id);


//             $edit_product->set_name($date_produs->product_name);
//             $edit_product->set_description($date_produs->product_description);
//             $edit_product->set_short_description($date_produs->product_short_description);

//             $product_category_ids = array();
//             foreach ($date_produs->product_category_ids as $categorie) {
//                 $categorie_interna = $wpdb->get_var("SELECT `category_id` FROM `" . $wpdb->prefix . "di_crawler_categories_assign` WHERE `furnizor_id`='" . $furnizor->id . "' AND `furnizor_category_id`='" . $categorie . "'");
//                 array_push($product_category_ids,$categorie_interna);
//             }
//             $edit_product->set_category_ids( $product_category_ids );

//             $edit_product->set_sku($date_produs->product_sku);
//             $edit_product->set_stock_status($date_produs->product_stock_status);
//             $edit_product->set_manage_stock($date_produs->product_manage_stock);
//             $edit_product->set_stock_quantity($date_produs->product_stock_quantity);

//             $edit_product->set_regular_price($date_produs->product_regular_price);
//             $edit_product->set_regular_price($date_produs->product_regular_price);


//             $edit_product->save();
//         }
//         else{

//             if( $date_produs->product_type === "simple" ){
//                 $new_product = new WC_Product_Simple();
//             }
//             else if( $date_produs->product_type === "external" ){
//                 $new_product = new WC_Product_External();
//             }
//             else if( $date_produs->product_type === "grouped" ){
//                 $new_product = new WC_Product_Grouped();
//             }
//             else if( $date_produs->product_type === "variable" ){
//                 $new_product = new WC_Product_Variable();
//             }

//             $new_product->set_name($date_produs->product_name);
//             $new_product->set_description($date_produs->product_description);
//             $new_product->set_short_description($date_produs->product_short_description);

//             $product_category_ids = array();
//             foreach ($date_produs->product_category_ids as $categorie) {
//                 $categorie_interna = $wpdb->get_var("SELECT `category_id` FROM `" . $wpdb->prefix . "di_crawler_categories_assign` WHERE `furnizor_id`='" . $furnizor->id . "' AND `furnizor_category_id`='" . $categorie . "'");
//                 array_push($product_category_ids,$categorie_interna);
//             }
//             $new_product->set_category_ids( $product_category_ids );

//             $new_product->set_sku($date_produs->product_sku);
//             $new_product->set_stock_status($date_produs->product_stock_status);
//             $new_product->set_manage_stock($date_produs->product_manage_stock);
//             $new_product->set_stock_quantity($date_produs->product_stock_quantity);
            
//             $product_image_id = media_sideload_image($date_produs->product_image_url,"0",null,'id');
//             $new_product->set_image_id($product_image_id);

//             $product_gallery_images = array();
//             foreach ($date_produs->product_gallery_image_urls as $gallery_image) {
//                 $product_gallery_image = media_sideload_image($gallery_image,"0",null,'id');
//                 array_push($product_gallery_images,$product_gallery_image);
//             }
//             $new_product->set_gallery_image_ids($product_gallery_images);
//             $new_product->set_regular_price($date_produs->product_regular_price);
//             $new_product->set_regular_price($date_produs->product_regular_price);

//             $new_product->save();

//             if( $date_produs->product_type === "variable" ){

//             }

//             $wpdb->update($wpdb->prefix . "di_crawler_fetched_products",array("product_id"=>$new_product->get_id()),array("furnizor_id"=>$furnizor->id,"furnizor_product_id"=>$date_produs->product_id));

//         }

//     }

// }


// $order_product_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."di_crawler_fetched_products` WHERE `product_id` = '1338'");

$order_product['quantity'] = 1;
$order_product_id = '1338';
$order_produse_furnizori = array();
        
$order_product_data = $wpdb->get_results("SELECT * FROM `" . $wpdb->prefix . "di_crawler_fetched_products` WHERE `product_id`='" . $order_product_id . "'");

$order_product_furnizor = $order_product_data[0]->furnizor_id;
// echo var_dump($order_product_data[0]);


if(!isset($order_produse_furnizori[$order_product_furnizor])){
    $order_produse_furnizori[$order_product_furnizor]= array();
}

array_push($order_produse_furnizori[$order_product_furnizor],array(
    "product_data" => $order_product_data,
    "product_quantity"=>$order_product['quantity']
));
array_push($order_produse_furnizori[$order_product_furnizor],array(
    "product_data" => $order_product_data,
    "product_quantity"=>$order_product['quantity']
));



foreach($order_produse_furnizori as $id => $data) {
    echo 'furnizor_id - '.$id.'  data - '.var_dump($data);
}

// echo '<pre>'.var_export($order_produse_furnizori,true).'</pre>' ;
       
