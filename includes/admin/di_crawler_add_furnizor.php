<?php
    
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
    


?>