<?php
    require_once((__DIR__)."/includes/conn.php");
    require_once((__DIR__)."/includes/curl.php");
    require_once((__DIR__)."/includes/inc.php");
    $counts = 0;
    $loop = true;
    $page_count = 0;
    $function = "getSales";
    $url = $api_base_url.$function;
    while($loop){
        $page_count++;
        //--//
        $data_table = "orders";
        $post_fields = ['page_count' => $page_count];
        log_request($connection, $function, $post_fields);
        $data = json_decode(curl_data($url, $post_fields));
        $count = check_data_exists($data, $data_table, $page_count);
        //--//
        foreach($data->{$data_table} as &$row){
            $row->customer_email = str_replace([" ", "n/a"], "", strtolower(trim($row->customer_email)));
            $delimiters = [',', ';'];
            $row->customer_email = explode($delimiters[0], str_replace($delimiters, $delimiters[0], $row->customer_email));
            $row->customer_email = array_unique(array_filter($row->customer_email));
            $row->customer_email = current($row->customer_email);
        }
        foreach($data->{$data_table} as &$row){
            $row = (array)$row;
            /*$customer = [
                'id'     => $row['customer_id'],
                'email'  => $row['customer_email'],
                'status' => '1'
            ];
            add_update_row((object)$customer, "`users`", "id", $connection);*/
            /*$product = [
                'id'          => $row['product_id'],
                'title'       => $row['name'],
                'SKU'         => $row['sku'],
                'description' => $row['description'],
                'valid_for'   => "Both",
                'special'     => "0",
                'renew'       => "1",
                'active'      => "1",
            ];
            add_update_row((object)$product, "`offers`", "id", $connection);*/
            $order = [
                'id'            => $row['order_id'],
                'user_id'       => $row['customer_id'],
                'offer_id'      => $row['product_id'],
                'approx_saving' => $row['discount_amount'],
                'created_at'    => $row['created_at'],
                'updated_at'    => $row['updated_at'],
            ];
            /*//add_update_row((object)$order, "`orders`", "id", $connection);*/
            $fetch = fetch_row("`orders`", "id", $order['id'], $connection);
            if(empty($fetch)){
                add_row((object)$order, "`orders`", "id", $connection);
            }
            else{
                if(!empty($order['updated_at']) && !empty($fetch->updated_at)){
                    if($order['updated_at'] > $fetch->updated_at){
                        update_row((object)$order, "`orders`", "id", $connection);
                    }
                }
            }
        }
        //--//
        if($count < 100){
            $loop = false;
        }
        $counts += $count;
    }
    $response = [
        'status'  => "1",
        'pages'   => $page_count,
        'message' => "$counts $data_table data extracted...",
    ];
    echo json_encode($response, true);
    exit();
