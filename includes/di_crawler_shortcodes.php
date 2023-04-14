<?php

    add_shortcode( 'di_product_partner', 'di_product_partner' );

    function di_product_partner(){
        global $product;
        global $wpdb;

        $this_product_id = $product->get_id();
        $this_product_partner_id = $wpdb->get_var("SELECT `furnizor_id` FROM `" . $wpdb->prefix . "di_crawler_fetched_products` WHERE `product_id`='".$this_product_id."'");
        if($this_product_partner_id !== null){
            $this_product_partner_name = $wpdb->get_var("SELECT `nume_furnizor` FROM `".$wpdb->prefix."di_crawler_furnizori` WHERE `id`='".$this_product_partner_id."'");
            $this_product_partner_slug = "/partner/" . $wpdb->get_var("SELECT `slug_furnizor` FROM `".$wpdb->prefix."di_crawler_furnizori` WHERE `id`='".$this_product_partner_id."'");
        } else {
            $this_product_partner_name = "Retailromania";
            $this_product_partner_slug = "#";
        }

        ob_start();

        ?>
            <p>Vândut și livrat de <a href="<?php echo $this_product_partner_slug; ?>"><?php echo $this_product_partner_name; ?></a></p>
        <?php

        return ob_get_clean();
    }

    add_shortcode( 'di_product_add_to_cart_button', 'di_product_add_to_cart_button' );

    function di_product_add_to_cart_button(){

       
        global $product;
        global $wpdb;

        ob_start();


        ?>

<style>
    .di_variation_selector:has(input[type=radio]:checked ) {
        background: #2a87c4;
        color: #fff;
    }
    .di_variation_selector {
        margin: 2px 5px;
        padding: 2px 24px;
        border: 1px solid #1a1a1a55;
        border-radius: 50px;
        text-align: center;
        cursor: pointer;
    }

    .di_add_to_cart_button {
        padding: 7px 25px 7px 55px;
        font-size: 22px;
        position: relative;
        border: none;
        border-radius: 5px;
        display: flex;
        flex-direction: row;
        align-items: center;
        align-content: center;
        color: #fafafa;
        background-image: linear-gradient(60deg,#e2549d 0%,#7254a2 50%,#2a87c4 100%);
        cursor: pointer;
        overflow: hidden;
    }

    .di_add_to_cart_button span {
        position: absolute;
        left: 0;
        padding: 0 20px 0 10px;
        background: transparent;
        width: max-content;
        height: 100%;
        display: flex;
        align-items: center;
    }

</style>

        <?php

        if( $product->is_type( 'variable' ) ){
            
            $variations = $product->get_available_variations();
            // echo var_dump($variations);
            ?>

            <form class="variations_form" method="post">

            <?php
            // echo var_dump($product->get_attributes());
                foreach ( $product->get_attributes() as $attribute ) {
                    
                    if( $attribute->get_variation() ){
                        ?>
                        <div class='variation'>
                            <h4 class="variation-label">Alege <?php echo esc_html( $attribute->get_name() ); ?></h4>
                            <div class="variations-options">
                                <?php
                                    foreach ($attribute->get_terms() as $term) {

                                        // echo var_dump($term);
                                        ?>
                                            <label class="di_variation_selector" for="variation_radio_<?php echo $attribute->get_name(); ?><?php echo $term->term_id; ?>"> 
                                                <input type="radio" id="variation_radio_<?php echo $attribute->get_name(); ?><?php echo $term->term_id; ?>" name="attribute_<?php echo $attribute->get_name(); ?>" value="<?php echo $term->name; ?>" data-attribute_name="attribute_<?php echo $attribute->get_name(); ?>" hidden>
                                                <?php echo $term->name; ?>
                                            </label>
                                        <?php

                                        
                                    }
                                ?>
                            </div>
                        </div>
                        <?php
                    }
                }
            ?>

            <button type="submit" class="di_add_to_cart_button" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>"><span class="dashicons dashicons-cart"></span>Adaugă în coș</button>

            </form>
        
            <?php

        } else {
            ?>

            <form class="variations_form" method="post">

            
            <button type="submit" class="di_add_to_cart_button" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>"><span class="dashicons dashicons-cart"></span>Adaugă în coș</button>

            </form>
        
            <?php
        }

        return ob_get_clean();
    }