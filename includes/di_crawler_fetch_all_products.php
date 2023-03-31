<?php

    global $wpdb;


    $array_date_furnizori = array();

    $lista_furnizori = $wpdb->get_results("
        SELECT * FROM `" . $wpdb->prefix . "di_crawler_furnizori` WHERE `api_status` = '1'
    ");

    foreach ($lista_furnizori as $furnizor) {
        
        $lista_categorii = $wpdb->get_results("
            SELECT * FROM `" . $wpdb->prefix . "di_crawler_categories_assign` WHERE `furnizor_id` = '" . $furnizor->id . "' AND `category_id` != ''
        ");

        array_push($array_date_furnizori,array("furnizor" => $furnizor, "categorii" => $lista_categorii));
        
    }

    // echo 'array date - '.var_dump($array_date_furnizori);

    foreach ($array_date_furnizori as $date_furnizor) {
        $furnizor = $date_furnizor['furnizor'];
        $categorii_data = $date_furnizor['categorii'];
        $categorii = array();
        foreach ($categorii_data as $categorie) {
            array_push($categorii,$categorie->category_id);
        }

        // echo var_dump($furnizor);

        $fetch_produse = wp_remote_get($furnizor->url_furnizor ."/wp-json/" . $furnizor->api_token . "/feed/products/");
        $fetch_produse = wp_remote_retrieve_body($fetch_produse);
        $fetch_produse = json_decode($fetch_produse);

        $produse_de_adaugat_db = array();

        foreach ($fetch_produse as $product_data) {
        
            if(sizeof(array_intersect(array_map('strval',$product_data->product_category_ids),array_map('strval',$categorii))) > 0){
                array_push($produse_de_adaugat_db,$product_data);
            }    
        }
        ?><br><br><?php
        
        // echo var_dump($produse_de_adaugat_db);
        foreach ($produse_de_adaugat_db as $product_data) {
            $check_if_exists = $wpdb->get_var("SELECT * FROM `" . $wpdb->prefix . "di_crawler_fetched_products` WHERE `furnizor_id` = '" . $furnizor->id . "' AND `furnizor_product_id` = '" . $product_data->product_id . "'");
            if($check_if_exists === NULL){
            
                $wpdb->insert($wpdb->prefix . 'di_crawler_fetched_products', array(
                    
                    'furnizor_id' => $furnizor->id,
                    'fetched_date' => date('Y-m-d H:i:s'),
                    'furnizor_product_id' => $product_data->product_id,
                    'is_full_fetchable' => '0',
                    'last_update_date' => ($product_data->product_date_modified)->date,
                    'product_data' => json_encode(array(
                        'product_name' => $product_data->product_name,
                            'product_price' => $product_data->product_price,
                            'product_regular_price' => $product_data->product_regular_price,
                            'product_image_url' => $product_data->product_image_url,
                            'product_slug' => $product_data->product_slug,
                            'product_sku' => $product_data->product_sku,
                            'product_stock_status' => $product_data->product_stock_status,
                            'product_stock_quantity' => $product_data->product_stock_quantity,
                            'product_url' => $product_data->product_permalink
                    ))
                ), array(
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s'
                ));
            }
            else{
                $existing_product_data = $wpdb->get_results("SELECT * FROM `" . $wpdb->prefix . "di_crawler_fetched_products` WHERE `furnizor_id` = '" . $furnizor->id . "' AND `furnizor_product_id` = '" . $product_data->product_id . "'");

                if(date($existing_product_data[0]->last_update_date) !== date(($product_data->product_date_modified)->date)){
                    $wpdb->update($wpdb->prefix . "di_crawler_fetched_products", array(
                        'last_update_date' => ($product_data->product_date_modified)->date,
                        'product_data' => json_encode(array(
                            'product_name' => $product_data->product_name,
                            'product_price' => $product_data->product_price,
                            'product_regular_price' => $product_data->product_regular_price,
                            'product_image_url' => $product_data->product_image_url,
                            'product_slug' => $product_data->product_slug,
                            'product_sku' => $product_data->product_sku,
                            'product_stock_status' => $product_data->product_stock_status,
                            'product_stock_quantity' => $product_data->product_stock_quantity,
                            'product_url' => $product_data->product_permalink
                        ))
                    ), array(
                        'furnizor_id' => $furnizor->id,
                        'furnizor_product_id' => $product_data->product_id
                    ), array(
                        "%s",
                        "%s"
                    ));
                }
            }
        }
    }






?>