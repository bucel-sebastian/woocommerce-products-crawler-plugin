<?php
global $wpdb;

$orders = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."di_crawler_orders` ORDER BY `id` DESC");

?>


<div class="wrap">
        
    <div>
        <h1>Retailromania API</h1>
        <h3>Comenzi</h3>
        

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


        </div>
                
           
        
        
        
    </div>
    
    

    <table class="wp-list-table widefat fixed striped table-view-list posts" cellspacing="0" >
        <thead>
            <tr>
                <th>
                    Nr. Crt.
                </th>
                <th>
                    Numar comanda
                </th>
                <th>
                    Furnizor
                </th>
                <th>
                    Numar comanda furnizor
                </th>
                <th>
                    Total        
                </th>
                <th>
                    AWB
                </th>
                <th>
                    Status
                </th>
                <th>
                    Actiuni
                </th>
            </tr>
        </thead>
        <tbody>
            <?php
                $order_index = 1;
                foreach ($orders as $order ) {
                    $order_furnizor_id = $order->furnizor_id;
                    $order_extern_id = $order->furnizor_order_id;
                    $order_id = $order->parent_order_id;
                    $order_status = $order->order_status;

                    $nume_furnizor = $wpdb->get_var("SELECT `nume_furnizor` FROM `".$wpdb->prefix."di_crawler_furnizor` WHERE `id`='".$order_furnizor_id."'");

                    $intern_order = wc_get_order($order_id);
                    $order_url = $intern_order->get_edit_order_url();
                    $intern_order_number = $intern_order->get_order_number();


                    if($order_status === ""){
                        $status = '
                            <span class="di-order-status status-unknown">
                                Necunoscut
                            </span>
                        ';
                    }
                    else if($order_status === "0"){   
                    $status = '
                            <span class="di-order-status status-new">
                                Comanda noua
                            </span>
                        ';
                    }
                    else if($order_status === "1"){
                        $status = '
                            <span class="di-order-status status-seen">
                                Comanda vizualizata
                            </span>
                        ';
                    }
                    else if($order_status === "2"){
                        $status = '
                            <span class="di-order-status status-ready">
                                Comanda pregatita
                            </span>
                        ';
                    }
                    else if($order_status === "3"){
                        $status = '
                            <span class="di-order-status status-sent">
                                Comanda trimisa
                            </span>
                        ';
                    }

                    echo '
                        <tr>
                            <td>
                                '.$order_index.'
                            </td>
                            <td>
                                

                                <a style="cursor:pointer;" class="order-view" href="'.$order_url.'"><strong>#'.$intern_order_number.' '.$intern_order->get_billing_first_name().' ' . $intern_order->get_billing_last_name() .  '</strong></a>
                            </td>
                            <td>
                                '.$nume_furnizor.'
                            </td>
                            <td>
                                '.$order_extern_id.'
                            </td>
                            <td>
                                '.$order_total.'
                            </td>
                            <td>
                                '.$order_awb.'
                            </td>
                            <td>
                                '.$status.'
                            </td>
                            <td>
                            
                            </td>
                        
                        </tr>
                    ';

                    $order_index++;
                }


            ?>
        </tbody>
    </table>

    <?php
        if(isset($num_of_pages)){
            for($page_index=1;$page_index<=$num_of_pages;$page_index++){
                ?><a href='admin.php?page=di-crawler-admin-produse&pagenumber=<?php echo $page_index; ?> ' class='button button-secondary'><?php echo $page_index;?></a>
                <?php
            }
        }
        
    ?>


</div>