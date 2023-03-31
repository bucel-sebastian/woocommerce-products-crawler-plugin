<?php

    
    if(isset($_POST['furnizor-selector']) && $_POST['furnizor-selector'] !== ''){
        global $wpdb;

        $array_selectoare = array();
        foreach ($_POST as $key => $value) {
            // echo $key . ' - ' .$value . '\n';
            $parametru = explode("_",$key);

            if(isset($parametru[1]) && $parametru[1] === "selector"){
                array_push($array_selectoare,array("index"=>$parametru[2],"value"=>$value));
            }
        }

        foreach ($array_selectoare as $item) {
            
            $wpdb->update($wpdb->prefix . 'di_crawler_categories_assign',array("category_id"=>$item['value']),array("furnizor_id"=>$_POST['furnizor-selector'],"id"=>$item['index']),array("%s"));
            
        }

        $furnizor_selectat = $_POST['furnizor-selector'];
        // echo var_dump($array_selectoare);

    }


    global $wpdb;

    $furnizori = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."di_crawler_furnizori` WHERE `api_status`='1'");
    $selectorFurnizori = "";
    $categorii_furnizori = array();
    foreach ($furnizori as $furnizor) {
        $selectorFurnizori .= "<option value='".$furnizor->id."'>".$furnizor->nume_furnizor."</option>";


        $categorii_furnizor_tmp = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."di_crawler_categories_assign` WHERE `furnizor_id`='".$furnizor->id."'");

        array_push($categorii_furnizori,array("furnizor"=>$furnizor->id,"categorii"=>$categorii_furnizor_tmp));

    }

    if(isset($_GET['action'])){
        if($_GET['action']==='fetch'){
            foreach ($furnizori as $furnizor) {
                ?><div>
                <?php echo var_dump($furnizor); ?>
                </div><br><br>
               
                <?php
                $response = wp_remote_get($furnizor->url_furnizor . '/wp-json/' . $furnizor->api_token . '/feed/categories/');
                $response = wp_remote_retrieve_body($response);
                $response = json_decode($response);
                if(!isset($response->code)){
                    foreach ($response as $category) {
                        $check_if_exist = $wpdb->get_var("SELECT * FROM `".$wpdb->prefix."di_crawler_categories_assign` WHERE `furnizor_id`='".$furnizor->id."' AND `furnizor_category_id`='".$category->id."'");
                        if(!$check_if_exist){
                            $wpdb->insert($wpdb->prefix . 'di_crawler_categories_assign',array(
                                "furnizor_id"=>$furnizor->id,
                                "furnizor_category_id"=>$category->id,
                                "furnizor_category_name"=>$category->name
                            ),array("%s","%s","%s"));
                        }
                        else{
                            $wpdb->update($wpdb->prefix . 'di_crawler_categories_assign',array("furnizor_category_name"=>$category->name),array("furnizor_id"=>$furnizor->id,"furnizor_category_id"=>$category->id),array("%s"));
                        }
                    }
                }
            }
            $selectorFurnizori = "";
            $categorii_furnizori = array();
            foreach ($furnizori as $furnizor) {
                $selectorFurnizori .= "<option value='".$furnizor->id."'>".$furnizor->nume_furnizor."</option>";
        
        
                $categorii_furnizor_tmp = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."di_crawler_categories_assign` WHERE `furnizor_id`='".$furnizor->id."'");
        
                array_push($categorii_furnizori,array("furnizor"=>$furnizor->id,"categorii"=>$categorii_furnizor_tmp));
        
            }
        }
    }

$taxonomy     = 'product_cat';
  $orderby      = 'parent';  
  $show_count   = 0;      // 1 for yes, 0 for no
  $pad_counts   = 0;      // 1 for yes, 0 for no
  $hierarchical = 1;      // 1 for yes, 0 for no  
  $title        = '';  
  $empty        = 0;

  $args = array(
         'taxonomy'     => $taxonomy,
         'orderby'      => $orderby,
         'show_count'   => $show_count,
         'pad_counts'   => $pad_counts,
         'hierarchical' => $hierarchical,
         'title_li'     => $title,
         'hide_empty'   => $empty,
         'parent'     => 0
  );
 $all_categories = get_categories( $args );

$categorii_interne = array();
 foreach ($all_categories as $cat ) {

    array_push($categorii_interne,array("nume"=>$cat->name,"id"=>$cat->term_id));
    $args = array(
        'taxonomy'     => $taxonomy,
         'orderby'      => $orderby,
         'show_count'   => $show_count,
         'pad_counts'   => $pad_counts,
         'hierarchical' => $hierarchical,
         'title_li'     => $title,
         'hide_empty'   => $empty,
         'parent'     => $cat->term_id
    );
    $subcategories = get_categories( $args );
    foreach ($subcategories as $subcat ) {
        array_push($categorii_interne,array("nume"=>'-'.$subcat->name,"id"=>$subcat->term_id));
        $args = array(
            'taxonomy'     => $taxonomy,
             'orderby'      => $orderby,
             'show_count'   => $show_count,
             'pad_counts'   => $pad_counts,
             'hierarchical' => $hierarchical,
             'title_li'     => $title,
             'hide_empty'   => $empty,
             'parent'     => $subcat->term_id
        );

        $subsubcategories = get_categories( $args );
        foreach ($subsubcategories as $subsubcat) {
            array_push($categorii_interne,array("nume"=>'--'.$subsubcat->name,"id"=>$subsubcat->term_id));
        }
    }
    
 }
$selector_options = "";
 foreach($categorii_interne as $cat_int){

    $selector_options .= "<option value='".$cat_int['id']."'>".$cat_int['nume']."</option>"; 
 }

 function di_crawler_category_selector($selector_id,$selector_options) {
    
    return "
        <select id='di_selector_$selector_id' name='di_selector_$selector_id'>
            <option value='' selected>Selecteaza categoria</option>
            $selector_options    
        </select>
    ";
 }

?>


<div class="wrap">
    
    <h1>di Crawler de produse</h1>
    <h3>Categorii</h3>
    
   <form method="POST" action='admin.php?page=di-crawler-admin-categori' >
        <select name="furnizor-selector" id="furnizor-selector">
			<option selected="selected" value="" disabled>Selecteaza furnizor</option>
            <?php echo $selectorFurnizori;?>
		</select>
        <button type='submit' class='button button-primary'>Salveaza</button>
    <table class="widefat fixed" cellspacing="0" >
        <thead>
            <tr>
                <th>
                    ID
                </th>
                <th>
                    Categorie Furnizor
                </th>
                <th>
                    Categorie Interna
                </th>    
                <th>
                    Reguli
                </th>
                
            </tr>
        </thead>
        <tbody id="di-tabel-categorii-furnizori">
            
        </tbody>
    </table>
    <?php // echo di_crawler_category_selector("test",$selector_options);?>
    
    </form>

    <script>
    
        let categoriiFurnizori = JSON.parse('<?php echo json_encode($categorii_furnizori); ?>');
        let furnizorSelector = document.getElementById("furnizor-selector");
        let tableBody = document.getElementById("di-tabel-categorii-furnizori");
        furnizorSelector.addEventListener("change",e=>{
           let output = "";
           tableBody.innerHTML = output;

           let selectedFurnizor = e.target.value;
           let selectedCategoriiFurnizor;
           categoriiFurnizori.forEach(categoriiFurnizor => {
                if(categoriiFurnizor.furnizor === selectedFurnizor){
                    selectedCategoriiFurnizor=categoriiFurnizor.categorii;
                }
           });
           selectedCategoriiFurnizor.forEach(categorie => {
                output += "<tr><td>"+categorie.id+"</td><td>"+categorie.furnizor_category_name+"</td><td><select id='di_selector_"+categorie.id+"' name='di_selector_"+categorie.id+"'><option value='' selected>Selecteaza categoria</option><?php echo $selector_options; ?></select></td><td>"+categorie.rule+"</td></tr>";
                // if(categorie.category_id != ""){
                //     document.getElementById("di_selector_"+categorie.id).value = categorie.category_id;
                // }
           });
           tableBody.innerHTML = output;
           selectedCategoriiFurnizor.forEach(categorie => {
                if(categorie.category_id != ""){
                    document.getElementById("di_selector_"+categorie.id).value = categorie.category_id;
                }
           });

        });

        <?php
            if(isset($furnizor_selectat)){
            ?>
                furnizorSelector.value = '<?php echo $furnizor_selectat; ?>';
                furnizorSelector.dispatchEvent(new Event('change'));
            <?php 
                }
        ?>


    </script>

</div>

