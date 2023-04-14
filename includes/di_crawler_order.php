<?php


global $wpdb;

add_action( 'woocommerce_new_order', 'di_crawler_new_order', 1, 2 );

function di_crawler_new_order( $order_id, $order ){
    
    global $wpdb;

    $items = $order->get_items();

    $order_data = array();
    $order_data["billing_first_name"]=$order->get_billing_first_name();
    $order_data["billing_last_name"]=$order->get_billing_last_name();
    $order_data["billing_company"]=$order->get_billing_company();
    $order_data["billing_address_1"]=$order->get_billing_address_1();
    $order_data["billing_address_2"]=$order->get_billing_address_2();
    $order_data["billing_city"]=$order->get_billing_city();
    $order_data["billing_state"]=$order->get_billing_state();
    $order_data["billing_postcode"]=$order->get_billing_postcode();
    $order_data["billing_country"]=$order->get_billing_country();
    $order_data["billing_email"]=$order->get_billing_email();
    $order_data["billing_phone"]=$order->get_billing_phone();

    $order_data["shipping_first_name"]=$order->get_shipping_first_name();
    $order_data["shipping_last_name"]=$order->get_shipping_last_name();
    $order_data["shipping_company"]=$order->get_shipping_company();
    $order_data["shipping_address_1"]=$order->get_shipping_address_1();
    $order_data["shipping_address_2"]=$order->get_shipping_address_2();
    $order_data["shipping_city"]=$order->get_shipping_city();
    $order_data["shipping_state"]=$order->get_shipping_state();
    $order_data["shipping_postcode"]=$order->get_shipping_postcode();
    $order_data["shipping_country"]=$order->get_shipping_country();
    $order_data["address"]=$order->get_address();

   
    $order_products = array();

    foreach ($items as $item) {
        
        $product_id = $item->get_product_id();
        $product_variation_id = $item->get_variation_id();
        $product_quantity = $item->get_quantity();

        array_push($order_products,array( "id" => $product_id,"quantity"=>$product_quantity));
    
    }

    
    $order_produse_furnizori = array();

    foreach ($order_products as $order_product) {
        
        $order_product_id = $order_product['id'];
        
        $order_product_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."di_crawler_fetched_products` WHERE `product_id`='".$order_product_id."'");
        $order_product_furnizor = $order_product_data[0]->furnizor_id;

        if(!isset($order_produse_furnizori[$order_product_furnizor])){
            $order_produse_furnizori[$order_product_furnizor]=array();
        }
        
        array_push($order_produse_furnizori[$order_product_furnizor],array(
            "product_data"=>$order_product_data[0],
            "quantity"=>$order_product['quantity']
        ));
    

    }
   
    foreach ($order_produse_furnizori as $id_furnizor => $produse) {
        $date_furnizor = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."di_crawler_furnizori` WHERE `id` = '".$id_furnizor."'");
        $date_furnizor = $date_furnizor[0];

        $body = array(
                "products"=>$produse,
                "order_data"=>$order_data
        );


        $order_request = wp_remote_post($date_furnizor->url_furnizor . '/wp-json/' . $date_furnizor->api_token . '/di-crawler-new-order/',
             array(
                'method' => 'POST',
                'body' => $body
            ));

             if ( ! is_wp_error( $order_request ) ) {
                $body = json_decode( wp_remote_retrieve_body( $order_request ), true );
                $wpdb->insert($wpdb->prefix.'di_crawler_orders',array(
                    'parent_order_id'=>$order_id,
                    'furnizor_id'=>$id_furnizor,
                    'furnizor_order_id'=>$body->furnizor_order_id,
                    'order_product_id'=>json_encode($order_products)
                    
                ),
                array(
                    "%s",
                ));
            } else {
                $error_message = $order_request->get_error_message();
                throw new Exception( $error_message );
            }
             
        $order_request = json_decode($order_request);

        $suborder_args = array(
            'parent'=>$order_id,
        );
        $suborder = new wc_create_order($suborder_args);

        foreach ($produse as $produs) {
            $product_data = $produs['product_data'];

            $suborder->add_product( wc_get_product($product_data['product_id']), $produs->quantity);
        }
        $furnizor_shipping = $date_furnizor->shipping_class;
        $shipping_rate = $furnizor_shipping['rate'];
        $shipping_free = $furnizor_shipping['free'];


        if($suborder->calculate_totals()<$shipping_free){
            $fee = new WC_Order_Item_Fee();
            $fee->set_name( 'Taxa de transport' );
            $fee->set_amount( $shipping_rate );
            $fee->set_total( $shipping_rate );
            
            $suborder->add_item( $fee );
        }

        $suborder->calculate_totals();
        $suborder->save();
        
    }
    

}