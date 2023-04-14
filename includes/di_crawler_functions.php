<?php

// require_once DI_CRAWLER_DIR . '/includes/di_crawler_fetch_all_products.php';
require_once DI_CRAWLER_DIR . '/includes/di_crawler_fetch_selected_products.php';
require_once DI_CRAWLER_DIR . '/includes/di_crawler_order.php';
require_once DI_CRAWLER_DIR . '/includes/di_crawler_shortcodes.php';



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





add_action( 'woocommerce_before_cart', 'di_before_cart' );

function di_before_cart(){

}



add_shortcode( 'di_cart_module', 'di_cart_table' );

function di_cart_table() {

    // global $woocommerce;
    // global $wpdb;
    // $output = "<form class='woocommerce-cart-form' action='".esc_url( wc_get_cart_url() )."' method='post'>";

    
    // if($woocommerce->cart->get_cart_contents_count()===0){
    //     $output = "";
    //     return $output;
    // }
    // $cart_items = $woocommerce->cart->get_cart();

    
    // $lista_furnizori = array("0");
    // $lista_produse_furnizor = array();
    // $lista_produse_furnizor["0"]=array();

    // foreach ($cart_items as $item) {

    //     $product = wc_get_product($item['data']->get_id());

    //     $item_furnizor_id = $wpdb->get_var("SELECT `furnizor_id` FROM `".$wpdb->prefix."di_crawler_fetched_products` WHERE `product_id`='".$product->get_id()."'");
    //     if($item_furnizor_id !== null){
    //         if(!in_array($item_furnizor_id,$lista_furnizori)){
    //             array_push($lista_furnizori,$item_furnizor_id);
    //             $lista_produse_furnizor[$item_furnizor_id] = array();
    //         }
    //         if(!isset($lista_produse_furnizor[$item_furnizor_id])){
    //             $lista_produse_furnizor[$item_furnizor_id] = array();
    //         }
    //     }
    //     else{
    //         $item_furnizor_id = "0";
    //     }
        
    //     array_push($lista_produse_furnizor[$item_furnizor_id],$item);

    //     // echo var_dump($lista_produse_furnizor);
    // }
    // foreach ($lista_produse_furnizor as $furnizor => $produse) {
        // echo $furnizor;
        // echo var_dump($produse);
    //     $nume_furnizor = $wpdb->get_var("SELECT `nume_furnizor` FROM `".$wpdb->prefix."di_crawler_furnizori` WHERE `id`='".$furnizor."'");
    //     $shipping_class = $wpdb->get_var("SELECT `shipping_class` FROM `".$wpdb->prefix."di_crawler_furnizori` WHERE `id`='".$furnizor."'");

    //     if($shipping_class === null){
    //         $shipping_fee = 20;
    //         $shipping_free = 300;
    //     }
    //     else{
    //         $shipping_class = json_decode($shipping_class);
    //         $shipping_fee = $shipping_class->rate;
    //         $shipping_free = $shipping_class->free;
    //     }

    //     $products_total = 0 ;

    //     if($nume_furnizor === null){
    //         $nume_furnizor = "Retailromania";
    //     }
    //     $product_table = "";
    //     foreach ($produse as $key => $produs) {
    //         $product_data = wc_get_product($produs['data']->get_id());

    //         if ($product_data->is_sold_individually()){
    //             $product_quantity = "
    //             <button class='di_cart_quantity_button' onclick='removeQuantity(\"".$produs['key']."\")' disabled>-</button>



    //             <input type='number' step='1' min='0' name='cart[".$produs['key']."][qty]' class='di_cart_quantity_span' id='product_quantity_".$produs['key']."' value='" . $produs['quantity'] . "' disabled/>



    //             <button class='di_cart_quantity_button' onclick='addQuantity(\"".$produs['key']."\")' disabled>+</button>
    //             ";
    //         } else {
    //             $product_quantity = "<button class='di_cart_quantity_button' onclick='removeQuantity(\"".$produs['key']."\")'>-</button>".woocommerce_quantity_input(
    //                 array(
    //                     'input_id' => 'product_quantity_'.$produs['key'],
    //                     'input_name' => "cart[{$key}][qty]",
    //                     'input_value' => $produs['quantity'],
    //                     'max_value' => $product_data->get_max_purchase_quantity(),
    //                     'min_value' => '0',
    //                     'product_name' => $product_data->get_name(),
    //                     'classes' => apply_filters( 'woocommerce_quantity_input_classes', array( 'input-text', 'qty', 'text', 'di_cart_quantity_span' ), $product_data),
    //                     'readonly' => true,
    //                 ),
    //                 $product_data,
    //                 false
    //             )."<button class='di_cart_quantity_button' onclick='addQuantity(\"".$produs['key']."\")'>+</button>";
    //         }

    //         $product_table.= "<tr class='di_cart_product_row'>
    //                     <td style='width:25%'>
    //                         <img class='di_cart_product_image' src='".wp_get_attachment_url( $product_data->get_image_id() )."'>
    //                     </td>
    //                     <td style='width:65%'>
    //                         <a href='".get_permalink($product_data->get_id())."'>
    //                             <h4>
    //                                 ".$product_data->get_title()."
    //                             </h4>
    //                         </a>
    //                         <p>Preț unitar - ".$product_data->get_price()." lei</p>
    //                     </td>
    //                     <td> 
    //                         <div style='display: flex;
    //                         justify-content: center;
    //                         flex-direction: column;
    //                         align-items: flex-end;'>

    //                             <h2 class='di_cart_product_price product-subtotal' data-title='".esc_attr_e( 'Subtotal', 'woocommerce' )."'>
    //                                 ".apply_filters( 'woocommerce_cart_item_subtotal', $woocommerce->cart->get_product_subtotal($product_data,$produs['quantity']), $produs, $key )."
    //                             </h2>

    //                             <div class='di_cart_quantity_container'>
    //                                 ".$product_quantity."
    //                             </div>






    //                             <a href=".wc_get_cart_remove_url($produs['key'])." class='di_cart_delete' data-product_sku='".$product_data->get_sku()."' product_id='".$product_data->get_id()."'>
    //                                 Șterge
    //                             </a>
                            
    //                         </div>
    //                     </td>
    //                 </tr>";

    //         $products_total += 0;
    //     }

    //     if($products_total > $shipping_free){
    //         $shipping_fee = 0;
    //     }
    //     $subtotal = $products_total + $shipping_fee;

    //     $output .= "<div class='di_cart_partner'>
    //         <h3>Produse livrate de ". $nume_furnizor ."</h3>
    //         <div>
    //             <table style='border:none;'>
    //                 <tbody>
    //                     ".$product_table."
    //                 </tbody>
    //             </table>
    //             <div style='display:flex;justify-content:space-between;border-top:1px solid #1a1a1a55; padding-top: 5px;'>
    //                 <div>
    //                     Taxa de livrare - " . $shipping_fee . " Lei
    //                 </div>
    //                 <div>
    //                     <h3>Subtotal - " . $subtotal . " Lei</h3>
    //                 </div>
    //             </div>
    //         </div>
    //     </div>
        
    //     <script>
        
    //         function removeQuantity(productKey){
    //             let actualQuantity = parseInt(document.getElementById('product_quantity_'+productKey).value);
    //             if(actualQuantity - 1 >= 1){
    //                 actualQuantity--;
    //             }
    //             document.getElementById('product_quantity_'+productKey).value = actualQuantity;
    //         }

    //         function addQuantity(productKey){
    //             let actualQuantity = parseInt(document.getElementById('product_quantity_'+productKey).value);
    //             actualQuantity++;
                
    //             document.getElementById('product_quantity_'+productKey).value = actualQuantity;
    //         }
        
    //     </script>
        
    //     ";
    // }
    // $output .= "
    // <button type='submit' class='button wp-element-button' name='update_cart' value='Update cart'>Update cart</button>
    // </form>";

    ob_start();
    include_once('cart.php');
    return ob_get_clean();
}


?>