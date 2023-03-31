<?php

function di_crawler_fetch_furnizor_categories(){
    global $wpdb;

    $furnizori = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."di_crawler_furnizori`");
    $selectorFurnizori = "";
    foreach ($furnizori as $furnizor) {
        $selectorFurnizori .= "<option value='".$furnizor['id']."'>".$furnizor['nume_furnizor']."</option>";
    }

}