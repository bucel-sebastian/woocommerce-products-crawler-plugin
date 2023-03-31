<?php

global $wpdb;

$lista_furnizori = $wpdb->get_results("
        SELECT * FROM `" . $wpdb->prefix . "di_crawler_furnizori` WHERE `api_status` = '1'
    ");

if(isset($_GET['action']) && $_GET['action']==='force-fetch'){
    

    $array_date_furnizori = array();

    

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
}

$num_of_rows = $wpdb->get_var("SELECT COUNT(*) FROM `" . $wpdb->prefix . "di_crawler_fetched_products`");
$num_of_active_rows = $wpdb->get_var("SELECT COUNT(*) FROM `" . $wpdb->prefix . "di_crawler_fetched_products` WHERE `is_full_fetchable`='1'");
$num_of_pages = ceil($num_of_rows / 10);



if(isset($_GET['pagenumber'])){
    $page=intval($_GET['pagenumber']);
    $first_row = ($page-1)*10;
    $last_row = 10;

    $lista_produse_in_db = $wpdb->get_results("SELECT * FROM `" . $wpdb->prefix . "di_crawler_fetched_products` LIMIT " . $first_row . ", " . $last_row);
    
}
else{
    $first_row = 0;
    $last_row = 10;
    $lista_produse_in_db = $wpdb->get_results("SELECT * FROM `" . $wpdb->prefix . "di_crawler_fetched_products` LIMIT " . $first_row . ", " . $last_row);
}


?>


<style>

.fetch-status-btn{
    padding: 5px 8px;
    border: none;
    color: white;
    font-weight: 600;
    border-radius:3px;
    cursor:pointer;
}
.fetch-status-btn:hover{
    filter: brightness(0.85);
}

.fetch-active{

    background: green;

}

.fetch-inactive{

    background: red;

}

.fetch-loading{
    background: orange;
}


</style>

<div class="wrap">
        
    <div>
        <h1>di Crawler de produse</h1>
        <h3>Produse</h3>
        

        <div>
            <form action="admin.php?page=di-crawler-admin-produse">
                <div style='display:flex;flex-direction:row;margin-bottom:10px;'>
                    <div>
                        <label>Furnizor</label>
                        <select id='selector-furnizor' name='selector-furnizor'>
                            <option value=''>Toti</option>
                            <?php  
                            foreach ($lista_furnizori as $furnizor) {
                                echo "<option value='".$furnizor->id."'>".$furnizor->nume_furnizor."</option>";
                            }?>
                        </select>
                    </div>
                    <div>
                        <label>Denumire</label>
                        <input name="input-titlu" type="text" id="input-titlu" value="Denumire" class="regular-text">
                    </div>
                                
                    <button type='submit' class='button button-primary'>Cauta</button>
                </div>
            </form>
        </div>

        
        <div style='display:flex;flex-direction:row;align-content:center;align-items:center;margin-bottom:10px;'>
            <a class='button button-primary' href='admin.php?page=di-crawler-admin-produse&action=force-fetch'>Force fetch</a>

            <p style='margin-top:0;margin-bottom:0;margin-left:15px;'>Produse active <?php echo $num_of_active_rows;?> din <?php echo $num_of_rows;?></p>

        </div>
                
           
        
        
        
    </div>
    
    

    <table class="wp-list-table widefat fixed striped table-view-list posts" cellspacing="0" >
        <thead>
            <tr>
                <th>
                    Nr. Crt.
                </th>
                <th>
                    ID intern
                </th>
                <th>
                    Furnizor
                </th>
                <th>
                    ID Furnizor
                </th>
                <th scope="col" id="thumb" class="manage-column column-thumb">
                    <span class="wc-image tips">Imagine</span>
                </th>
                <th>
                    Titlu
                </th>
                <th>
                    Categorie
                </th>
                <th>
                    SKU
                </th>
                <th>
                    Pret
                </th>
                <th>
                    Stoc
                </th>
                <th>
                    Actiuni
                </th>
            </tr>
        </thead>
        <tbody>
            <?php
                $product_index = $first_row + 1;
                foreach ($lista_produse_in_db as $produs) {
                    
                    // echo var_dump($produs);
                    $nume_furnizor = $wpdb->get_var("SELECT `nume_furnizor` FROM `" . $wpdb->prefix . "di_crawler_furnizori` WHERE `id` = '" . $produs->furnizor_id ."'");
                    
                    $date_produs = json_decode($produs->product_data);
                    ?>
                        <tr class="has-post-thumbnail hentry type-product iedit ">
                            <td>
                                <?php echo $product_index;?>
                            </td>
                            <td>
                                <?php echo $produs->product_id;?>
                            </td>
                            <td>
                                <?php echo $nume_furnizor;?>
                            </td>
                            <td>        
                                <?php echo "<a href='".$date_produs->product_url."'>".$produs->furnizor_product_id."</a>";?>
                            </td>
                            <td class="thumb column-thumb" data-colname="Image">
                                <a><?php echo "<img style='max-width:60px;max-height:60px;' src='".$date_produs->product_image_url."' class='attachment-thumbnail size-thumbnail'>"?></a>
                            </td>
                            <td>
                                <?php echo $date_produs->product_name;?>
                            </td>
                            <td>

                            </td>
                            <td>
                                <?php echo $date_produs->product_sku;?>

                            </td>
                            <td>
                                <?php echo $date_produs->product_price;?>

                            </td>
                            <td>
                                <?php echo $date_produs->product_stock_status;?> 
                                    <?php if($date_produs->product_stock_quantity !== null){
                                            echo "(".$date_produs->product_stock_quantity.")";
                                        }?>
                                

                            </td>
                            <td>
                                <?php
                                
                                    if($produs->is_full_fetchable === '1'){
                                        ?><button class='fetch-status-btn fetch-active' onclick='changeProductFetchStatus("<?php echo $produs->furnizor_product_id; ?>","<?php echo $produs->furnizor_id; ?>",event)'>Activ</button><?php
                                    }
                                    else{
                                        ?><button class='fetch-status-btn fetch-inactive' onclick='changeProductFetchStatus("<?php echo $produs->furnizor_product_id; ?>","<?php echo $produs->furnizor_id; ?>",event)'>Inactiv</button><?php
                                    }
                                
                                ?>
                            </td>


                        </tr>


                    <?php
                    $product_index++;
                    // die();
                }
            
            ?>
        </tbody>
    </table>

    <?php
        for($page_index=1;$page_index<=$num_of_pages;$page_index++){
            ?><a href='admin.php?page=di-crawler-admin-produse&pagenumber=<?php echo $page_index; ?> ' class='button button-secondary'><?php echo $page_index;?></a>
            <?php
        }
    ?>


</div>


<script>

function changeProductFetchStatus(idProdus, idFurnizor,e){
    e.target.classList.remove('fetch-inactive');
    e.target.classList.remove('fetch-active');
    e.target.classList.add('fetch-loading');

    e.target.innerHTML = "Se incarca";
    let status = 0;

    const data = new FormData();
    data.append("id_produs",idProdus);
    data.append("id_furnizor",idFurnizor);

    if(status === 0){
        status = 1;
        fetch("https://retailromania.ro/wp-json/di-api/set-product-fetch-status/",{
            method:"POST",
            body:data
        }).then(response=>response.json()).then(response=>{
            if(response.status === "success"){
                if(response.fetchStatus === 1){
                    e.target.innerHTML = "Activ";
                    e.target.classList.remove('fetch-loading');
                    e.target.classList.add('fetch-active');
                }
                else{
                    e.target.innerHTML = "Inactiv";
                    e.target.classList.add('fetch-inactive');
                    e.target.classList.remove('fetch-loading');
    
                }
            }
            status = 0;
        })

    }

}



</script>
<?php

global $wpdb;

if(isset($_GET['action']) && $_GET['action']==='force-fetch'){
    

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

}

$lista_produse_in_db = $wpdb->get_results("SELECT * FROM `" . $wpdb->prefix . "di_crawler_fetched_products`");


?>






</script>
