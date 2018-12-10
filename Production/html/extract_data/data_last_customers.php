<?php
    require_once((__DIR__)."/includes/conn.php");
    require_once((__DIR__)."/includes/curl.php");
    require_once((__DIR__)."/includes/inc.php");
    $counts = 0;
    $loop = true;
    $page_count = 0;
    $function = "getLastCustomers";
    $url = $api_base_url.$function;
    //--//
    $page_count++;
    $data_table = "customers";
    $post_fields = ['page_count' => $page_count];
    log_request($connection, $function, $post_fields);
    $data = json_decode(curl_data($url, $post_fields));
    $count = check_data_exists($data, $data_table, $page_count);
    //--//
    foreach($data->{$data_table} as &$row){
        $row->last_name = strtolower(trim($row->last_name));
        $row->first_name = strtolower(trim($row->first_name));
        //--//
        $row->customer_name = [$row->first_name, $row->last_name];
        $row->customer_name = array_unique(array_filter($row->customer_name));
        $row->customer_name = ucwords(implode(" ", $row->customer_name));
        //--//
        $row->emails = str_replace([" ", "n/a"], "", strtolower(trim($row->email)));
        $row->phones = str_replace([" ", "n/a"], "", strtolower(trim($row->phone)));
        //--//
        $delimiters = [',', ';'];
        //--//
        $row->emails = explode($delimiters[0], str_replace($delimiters, $delimiters[0], $row->emails));
        $row->emails = array_unique(array_filter($row->emails));
        $row->email = current($row->emails);
        $row->emails = implode(",", $row->emails);
        //--//
        $row->phones = explode($delimiters[0], str_replace($delimiters, $delimiters[0], $row->phones));
        $row->phones = array_unique(array_filter($row->phones));
        $row->phone = current($row->phones);
        $row->phones = implode(",", $row->phones);
        //--//
        $row->network = "";
        if($row->ooredooCustomer == "1"){
            $row->network = "ooredoo";
        }
        else if($row->vodafoneCustomer == "1"){
            $row->network = "vodafone";
        }
        //--//
        if(!empty($row->date_of_birth = strtotime($row->date_of_birth))){
            $row->date_of_birth = date("Y-m-d", $row->date_of_birth);
        }
        //--//
        $row->gender = strtolower(trim($row->gender));
        if($row->gender == "1"){
            $row->gender = "male";
        }
        else if($row->gender == "2"){
            $row->gender = "female";
        }
        else{
            $row->gender = "";
        }
    }
    foreach($data->{$data_table} as &$row){
        $row = (array)$row;
        $customer = [
            'id'          => $row['customer_id'],
            'name'        => $row['customer_name'],
            'email'       => $row['email'],
            'phone'       => $row['phone'],
            'password'    => $row['password'],
            'gender'      => $row['gender'],
            'DOB'         => $row['date_of_birth'],
            'network'     => $row['network'],
            'nationality' => $row['nationality'],
            'app_id'      => 1,
            'offer_id'    => null,
            'status'      => $row['status'],
            'created_at'  => $row['created_at'],
            'updated_at'  => $row['updated_at'],
        ];
        /*//add_update_row((object)$customer, "`users`", "id", $connection);*/
        $fetch = fetch_row("`users`", "id", $customer['id'], $connection);
        if(empty($fetch)){
            add_row((object)$customer, "`users`", "id", $connection);
        }
    }
    $counts += $count;
    $response = [
        'status'  => "1",
        'pages'   => $page_count,
        'message' => "$counts $data_table data extracted...",
    ];
    echo json_encode($response, true);
    exit();
