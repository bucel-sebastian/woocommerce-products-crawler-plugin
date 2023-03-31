<?php

global $wpdb;
di_crawler_get_api_status();

if(isset($_POST['nume_furnizor']) && isset($_POST['url_furnizor'])){

    global $wpdb;

    $nume_furnizor = $_POST['nume_furnizor'];
    $url_furnizor = $_POST['url_furnizor'];

    $api_token = uniqid('di',false);



    $table = $wpdb->prefix . 'di_crawler_furnizori';
    $data = array(
        'nume_furnizor'=>$nume_furnizor,
        'url_furnizor'=>$url_furnizor,
        'api_token'=>$api_token,
        'api_status'=>'0',
        'api_status_date'=>date('Y-m-d H:i:s'),
        'add_date'=>date('Y-m-d H:i:s'),
        
    );
    $format = array('%s','%s','%s','%s','%s','%s');
    $wpdb->insert($table,$data,$format);
    

}

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

?>
<style>

.furnizor-activ{
    color: green;
}
.furnizor-inactiv{
    color: red;
}

</style>

<div class="wrap">
    
    <h1>di Crawler de produse</h1>
    <h3>Furnizori</h3>
    
    <button class="button button-secondary">Adauga furnizor</button>
    <a href="admin.php?page=di-crawler-admin-furnizori" class="button button-secondary">Reincarca</a>
    <div>
        <form id='add-furnizor-form' method="POST" action='admin.php?page=di-crawler-admin-furnizori'>
            <table>
                <tbody>

                    <tr>
                        <th scope="row">
                            <label for='nume_furnizor'>Nume furnizor</label>
                        </th>
                        <td>
                            <input name='nume_furnizor' type='text' id='nume_furnizor' class='regulat-text'>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for='url_furnizor'>Url furnizor</label>
                        </th>
                        <td>
                            <input name='url_furnizor' type='url' id='url_furnizor' class='regulat-text'>
                        </td>
                    </tr>
                    <tr>
                        <th>

                        </th>
                        <td>
                            <button  id='add-furnizor-submit' type='submit' class='button button-primary'>Adauga</button>
                        </td>
                    </tr>
                </tbody>
            </table>

        </form>

        
    </div>
    
    <table class="widefat fixed striped " cellspacing="0" >
        <thead>
            <tr>
                <th style='text-align:center;max-width:2.2em'>
                    ID
                </th>
                <th>
                    Nume
                </th>
                <th>
                    Website
                </th>
                <th>
                    API Token
                </th>
                <th>
                    Status token
                </th>
            </tr>
        </thead>
        <tbody>
            <?php
            
                $indexId = 1;

                $lista_furnizori = $wpdb->get_results('
                    SELECT * FROM `' . $wpdb->prefix . 'di_crawler_furnizori` ORDER BY `add_date` DESC
                ');

                foreach($lista_furnizori as $rand_furnizor){
                    if($rand_furnizor->api_status === "1"){
                        $api_status = '<span class="furnizor-activ">Activ</span>';
                    }
                    else{
                        $api_status = '<span class="furnizor-inactiv">Inactiv</span>';
                    }
                    echo "<tr>
                        <td class='check-column' style='text-align:center;'>
                            $indexId 
                        </td>
                        <td class='has-row-actions'>
                            $rand_furnizor->nume_furnizor
                            <div class='row-actions'>
                                <span class='0'>
                                    <a href='admin.php?page=di-crawler-admin-furnizori&action=edit&id=$rand_furnizor->id'>
                                        Modifica
                                    </a>
                                    |
                                </span>
                                <span class='1'>
                                    <a href='admin.php?page=di-crawler-admin-categori&id=$rand_furnizor->id'>
                                        Vezi categorii
                                    </a>
                                    |
                                </span>
                                <span class='3'>
                                    <span class='delete'>
                                        <a href='admin.php?page=di-crawler-admin-furnizori&action=delete&id=$rand_furnizor->id'>Sterge</a>
                                    </span>
                                </span>
                            </div>
                        </td>
                        <td>
                            $rand_furnizor->url_furnizor
                        </td>
                        <td>
                            $rand_furnizor->api_token
                        </td>
                        <td>
                            $api_status
                        </td>
        
                    
                    </tr>";
                    $indexId++;
                }
            
            ?>
        </tbody>
    </table>


</div>

<script src='<?php echo plugin_dir_url(__DIR__); ?>add-furnizor.js'></script>