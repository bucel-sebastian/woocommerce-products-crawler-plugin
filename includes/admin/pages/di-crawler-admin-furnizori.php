<?php

global $wpdb;
di_crawler_get_api_status();

if(isset($_POST['nume_furnizor']) && isset($_POST['url_furnizor'])){

    global $wpdb;

    $nume_furnizor = $_POST['nume_furnizor'];
    $url_furnizor = $_POST['url_furnizor'];
    $url_furnizor = rtrim($url_furnizor,"/");
    $shipping = $_POST['shipping_furnizor'];
    $shipping_rate = $_POST['shipping_rate_furnizor'];
    $shipping_free = $_POST['shipping_free_furnizor'];
    
    $shipping_class_array = array(
        "rate"=>$shipping_rate,
        "free"=>$shipping_free
    );

    $api_token = uniqid('di',false);



    $table = $wpdb->prefix . 'di_crawler_furnizori';
    $data = array(
        'nume_furnizor'=>$nume_furnizor,
        'url_furnizor'=>$url_furnizor,
        'api_token'=>$api_token,
        'api_status'=>'0',
        'api_status_date'=>date('Y-m-d H:i:s'),
        'add_date'=>date('Y-m-d H:i:s'),
        'shipping'=>$shipping,
        'shipping_class'=>json_encode($shipping_class_array)
    );
    $format = array('%s','%s','%s','%s','%s','%s','%s','%s');
    $wpdb->insert($table,$data,$format);
    echo '<div class="notice notice-success notice-alt"><p>Partenerul a fost adaugat cu succes!</p></div>';
}

if(isset($_GET['action'])){
    if($_GET['action'] === "delete"){
        $id = $_GET['id'];
        $wpdb->delete($wpdb->prefix . 'di_crawler_furnizori', array(
            "id"=>$id
        ));
        
        echo '<div class="notice notice-success notice-alt"><p>Partenerul a fost sters cu succes!</p></div>';

    }
}

function di_crawler_get_api_status(){
    global $wpdb;

    $furnizori = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."di_crawler_furnizori`");

    foreach ($furnizori as $furnizor) {
        $response = wp_remote_get($furnizor->url_furnizor . '/wp-json/' . $furnizor->api_token . '/status/');
        $response = wp_remote_retrieve_body($response);
        $response = json_decode($response);

// 
// De citit si api token si verificat
// 

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
    
    <h1>Retailromania API</h1>
    <h3>Parteneri</h3>
    <div style="margin-bottom: 10px;">
    <button class="button button-secondary" onclick="switchAddForm();">Adauga partener</button>
    <a href="admin.php?page=di-crawler-admin-furnizori" class="button button-secondary">Reincarca</a>
    </div>
    
    <div id="add-furnizor-form-container" style="display:none;margin:0 0 10px 0;">
        <form id='add-furnizor-form' method="POST" action='admin.php?page=di-crawler-admin-furnizori'>
            <table>
                <tbody>

                    <tr>
                        <th scope="row" style="text-align:right;">
                            <label for='nume_furnizor' style="text-align:right;">Nume partener</label>
                        </th>
                        <td>
                            <input name='nume_furnizor' type='text' id='nume-okfurnizor' class='regulat-text'>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" style="text-align:right;">
                            <label for='url_furnizor' style="text-align:right;">Url partener</label>
                        </th>
                        <td>
                            <input name='url_furnizor' type='url' id='url-furnizor' class='regulat-text'>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" style="text-align:right;">
                            <label for='shipping_furnizor' style="text-align:right;">AWB in numele</label>
                        </th>
                        <td>
                            <select name='shipping_furnizor' id='shipping-furnizor'>
                                <option value='1'>Retailromania</option>
                                <option value='0'>Partenerul</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" style="text-align:right;">
                            <label for='shipping_rate_furnizor' style="text-align:right;">Valoare taxa de livrare</label>
                        </th>
                        <td>
                            <input name='shipping_rate_furnizor' type='number' id='shipping-rate-furnizor' class='regulat-text'>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" style="text-align:right;">
                            <label for='shipping_free_furnizor' style="text-align:right;">Limită transport gratuit</label>
                        </th>
                        <td>
                            <input name='shipping_free_furnizor' type='number' id='shipping-free-furnizor' class='regulat-text'>
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
                    Cine livreaza
                </th>
                <th>
                    Taxa de livrare
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

                    $shipping_class = json_decode($rand_furnizor->shipping_class);

                    if($rand_furnizor->shipping === "0"){
                        $shipping_name = $rand_furnizor->nume_furnizor;
                    }
                    else if($rand_furnizor->shipping === "1"){
                        $shipping_name = "Retailromania";
                    }
                    else{
                        $shipping_name = "Unknown";
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
                            $shipping_name
                        </td>
                        <td>
                            $shipping_class->rate RON
                            (pana in $shipping_class->free RON)
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
<script>

    function switchAddForm(){
        let formContainer = document.getElementById("add-furnizor-form-container");
        
        if(formContainer.style.display === "none"){
            formContainer.style.display = "block";
        } else {
            formContainer.style.display = "none";
        }
    }
</script>