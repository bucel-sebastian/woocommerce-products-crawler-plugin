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


add_filter( 'manage_edit-shop_order_columns', 'add_partner_order_column', 20);
function add_partner_order_column( $columns ){

    $new_columns = array();

    foreach($columns as $key => $value){
        $new_columns[ $key ] = $value;
        if( 'order_number' === $key ){
            $new_columns['partner_column'] = __( 'Partener', 'woocommerce');
        }
    }

    return $new_columns;
}

add_action( 'manage_shop_order_posts_custom_column', 'partner_shop_order_column_content' );
function partner_shop_order_column_content( $column ) {
    global $post;
    global $wpdb;

    if ( 'partner_column' === $column ) {
        $partner_id = $wpdb->get_var("SELECT `furnizor_id` FROM `".$wpdb->prefix."di_crawler_orders` WHERE `parent_order_id`='".$post->ID."'");
        $partner_name = $wpdb->get_var("SELECT `nume_furnizor` FROM `".$wpdb->prefix."di_crawler_furnizori` WHERE `id`='".$partner_id."'"); 
        echo $partner_name === null ? "-" : $partner_name ;
        // echo "merge?";
    }
}



function di_send_order_canceled_api( $order_id ){

    $order = wc_get_order( $order_id );

    



}



add_action( 'woocommerce_order_status_cancelled','di_send_order_canceled_api');


function di_remove_downloads( $items ){
    unset($items['downloads']);
    return $items;
}

add_filter( 'woocommerce_account_menu_items', 'di_remove_downloads');



function di_add_cancel_button( $order ){
    // $wp_cancel_order = new WC_Cancel_Order();
    // // $wp_cancel_order->add_cancel_link($order);
    // // $key = get_post_meta($order->get_id(),'_wc_cancel_key',true);
	// // 		echo '<p><a href="'.get_home_url(get_current_blog_id(),'/guest-cancel-req/?key='.$key).'">'.__('Cancel Order','wc-cancel-order').'</a></p>';

    // if(!$wp_cancel_order->is_declined_in_past($order) && is_a($order,'WC_Order') && $order->has_status($wp_cancel_order->get_status($wp_cancel_order->settings['req-status']))){
    //     // $actions['wc-cancel-order'] = array(
    //     //     'url'=>wp_nonce_url(admin_url('admin-ajax.php?action=wc_cancel_request&order_id='.$order->get_id().'&order_num='.$order->get_order_number()),'wc-cancel-request'),
    //     //     'name'=> __('Cancel Request','wc-cancel-order'),
    //     //     'action'=>'cancel-request',
    //     // );

    //     $cancel_action = "<a href='".wp_nonce_url(admin_url('admin-ajax.php?action=wc_cancel_request&order_id='.$order->get_id().'&order_num='.$order->get_order_number()),'wc-cancel-request')."'>Anuleaza comanda</a>";
    // }
    // echo $cancel_action;
    // // $actions = apply_filters('wc_cancel_order_btn',$actions,$order);

    echo "<p>*Factura este trimisa fizic de către partenerul nostru.</p>";
}


add_action( 'woocommerce_order_details_after_order_table', 'di_add_cancel_button');



add_filter( 'woocommerce_cart_totals_order_total_html', 'custom_cart_total_html', 10, 1 );

function custom_cart_total_html( $value) {
    global $woocommerce;

    $value =$woocommerce->cart->get_cart_total() . " (TVA inclus)";
    return $value ;
}

add_action( 'woocommerce_before_edit_address_form', 'custom_add_new_address_button' );

function custom_add_new_address_button() {
    global $wp;

    // Verificați dacă utilizatorul este conectat
    if ( is_user_logged_in() ) {

        // Obțineți URL-ul actual al paginii
        $current_url = home_url( add_query_arg( array(), $wp->request ) );

        // Obțineți URL-ul de adăugare a adresei
        $add_address_url = wc_get_endpoint_url( 'edit-address', 'billing' );

        // Adăugați butonul "Adaugă adresă nouă"
        echo '<a class="button" href="' . esc_url( $current_url . $add_address_url ) . '">' . esc_html__( 'Adaugă adresă nouă', 'woocommerce' ) . '</a>';
    }
}

function load_dashicons_front_end() {
    wp_enqueue_style( 'dashicons' );
  }
  add_action( 'wp_enqueue_scripts', 'load_dashicons_front_end' );