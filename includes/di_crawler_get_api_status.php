<?php
function di_crawler_get_api_status(){
    global $wpdb;

    $furnizori = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."di_crawler_furnizori`");

    foreach ($furnizori as $furnizor) {
        $response = wp_remote_get($furnizor->url_furnizor . '/wp-json/' . $furnizor->api_token . '/status/');
        $response = wp_remote_retrieve_body($response);
        $response = json_decode($response);

        if(isset($response->code)){
            if($response->code === "rest_no_route"){
                $wpdb->update($wpdb->prefix . 'di_crawler_furnizori',array('api_status'=>'0','api_status_date'=>date("Y-m-d H:i:s")),array('id'=>$furnizor->id),array("%s","%s"));
            }
        }
        else{
            $wpdb->update($wpdb->prefix . 'di_crawler_furnizori',array('api_status'=>'1','api_status_date'=>date("Y-m-d H:i:s")),array('id'=>$furnizor->id),array("%s","%s"));
            
        }
    }
}