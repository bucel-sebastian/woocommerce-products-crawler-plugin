<?php

global $woocommerce;
global $wpdb;

?>

<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">

<?php

    if( $woocommerce->cart->get_cart_contents_count() === 0 ){

        ?><p>Cosul de cumparaturi este gol</p><?php

        die();
    }

    // Get cart details
    $cart_items = $woocommerce->cart->get_cart();

    // Set partners arrays - 0 is Retailromania default
    $list_partner = array("0");
    $list_partner_products = array();
    $list_partner_products["0"]=array();

    // All cart items loop
    foreach ( $cart_items as $cart_item_key => $cart_item ) {

        $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

        $product_partner_id = $wpdb->get_var("SELECT `furnizor_id` FROM `".$wpdb->prefix."di_crawler_fetched_products` WHERE `product_id`='".$_product->get_id()."'");

        if( $product_partner_id !== null ){
            if( !in_array($product_partner_id,$list_partner) ){
                array_push($list_partner,$product_partner_id);
                $list_partner_products[$product_partner_id] = array();
            }
            if( !isset($list_partner_products[$product_partner_id]) ){
                $list_partner_products[$product_partner_id] = array();
            }
        } else {
            $product_partner_id = "0";
        }

        // Store all products based by their partner id
        array_push($list_partner_products[$product_partner_id], $cart_item);
    }

    foreach ( $list_partner_products as $partner_id => $partner_products ) {
        
        $partner_name = $wpdb->get_var("SELECT `nume_furnizor` FROM `".$wpdb->prefix."di_crawler_furnizori` WHERE `id`='".$partner_id."'");

        if( $partner_name === null ){
            $partner_name = "Retailromania";
        }


        $partner_shipping_class = $wpdb->get_var("SELECT `shipping_class` FROM `".$wpdb->prefix."di_crawler_furnizori` WHERE `id`='".$partner_id."'");

        if($partner_shipping_class === null){
            $partner_shipping_rate = 20;
            $partner_shipping_free = 300;
        } else {
            $partner_shipping_class = json_decode($partner_shipping_class);
            $partner_shipping_rate = $partner_shipping_class->rate;
            $partner_shipping_free = $partner_shipping_class->free;
        }
        

        ?>
            <div class="di_cart_partner">
                <h3>Produse livrate de <?php echo $partner_name; ?></h3>
                <div>
                    <table style="border:none;">
                        <tbody>

                        <?php

        foreach ($partner_products as $cart_item_key => $cart_item) {
            
            $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

            if( $_product->is_sold_individually() ){
                $product_quantity = "
                <button class='di_cart_quantity_button' onclick='removeQuantity(\"".$cart_item_key."\")' disabled>-</button>
                <input type='number' step='1' min='0' name='cart[".$cart_item_key."][qty]' class='di_cart_quantity_span' id='product_quantity_".$cart_item_key."' value='" . $cart_item_key . "' disabled/>
                <button class='di_cart_quantity_button' onclick='addQuantity(\"".$cart_item_key."\")' disabled>+</button>
                ";
            } else {
                $product_quantity = "
                <button class='di_cart_quantity_button' onclick='removeQuantity(\"".$cart_item_key."\")'>-</button>
                " . woocommerce_quantity_input(
                    array(
                        'input_id' => 'product_quantity_'.$cart_item_key,
                        'input_name' => "cart[{$cart_item_key}][qty]",
                        'input_value' => $cart_item['quantity'],
                        'max_value' => $_product->get_max_purchase_quantity(),
                        'min_value' => '0',
                        'product_name' => $_product->get_name(),
                        'classes' => apply_filters( 'woocommerce_quantity_input_classes', array( 'input-text', 'qty', 'text', 'di_cart_quantity_span' ), $_product),
                        'readonly' => true,
                    ),
                    $_product,
                    false
                ) . "<button class='di_cart_quantity_button' onclick='addQuantity(\"".$cart_item_key."\")'>+</button>";
            }

            ?>
                <tr class="di_cart_product_row">
                    <td style="width:25%;">
                        <img class="di_cart_product_image" src="<?php echo wp_get_attachment_url( $_product->get_image_id() ); ?>">
                    </td>
                    <td style="width:65%;">
                        <a href="<?php echo get_permalink($_product->get_id()); ?>">
                            <h4>
                                <?php echo $_product->get_title(); ?>
                            </h4>
                            <p>Preț unitar - <?php echo $_product->get_price(); ?></p>
                        </a>
                    </td>
                    <td>
                        <div style="display: flex;justify-content: center;flex-direction: column;align-items: flex-end;">
                            <h2 class="di_cart_product_price product-subtotal" data-title="<?php echo esc_attr_e( 'Subtotal', 'woocommerce'); ?>">
                                <?php
                                    echo apply_filters( 'woocommerce_cart_item_subtotal', $woocommerce->cart->get_product_subtotal($_product,$cart_item['quantity']), $cart_item, $cart_item_key );
                                ?>
                            </h2>
                            <div class="di_cart_quantity_container">
                                <?php echo $product_quantity; ?>
                            </div>
                    
                            <a href="<?php echo wc_get_cart_remove_url($cart_item_key); ?>" class="di_cart_delete" data-product_sku="<?php echo $_product->get_sku(); ?>" product_id="<?php echo $_product->get_id(); ?>">
                                Șterge 
                            </a>
                        </div>
                    </td>

                </tr>
            <?php

        }


                        ?>

                        </tbody>
                    </table>
                    <div style="display:flex;justify-content:space-between;border-top:1px solid #1a1a1a55; padding-top: 5px;">
                        <div>
                            Taxa de livrare - <?php echo $partner_shipping_rate; ?> Lei
                        </div>
                        <div>
                            <h3>Subtotal - <?php echo $partner_subtotal; ?> Lei</h3>
                        </div>
                    </div>
                </div>
            </div>
        <?php

    }

?>













</form>


<script>
        
            function removeQuantity(productKey){
                let actualQuantity = parseInt(document.getElementById('product_quantity_'+productKey).value);
                if(actualQuantity - 1 >= 1){
                    actualQuantity--;
                }
                document.getElementById('product_quantity_'+productKey).value = actualQuantity;
            }

            function addQuantity(productKey){
                let actualQuantity = parseInt(document.getElementById('product_quantity_'+productKey).value);
                actualQuantity++;
                
                document.getElementById('product_quantity_'+productKey).value = actualQuantity;
            }
        
        </script>