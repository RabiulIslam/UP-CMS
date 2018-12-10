<?php
    require_once((__DIR__)."/includes/conn.php");
    require_once((__DIR__)."/includes/curl.php");
    require_once((__DIR__)."/includes/inc.php");
    $counts = 0;
    $loop = true;
    $page_count = 1;
    $page_count = 0;
    $function = 'all_subscriptions';
    $url = "http://13.126.174.129/iosweb/dataexport/?export=all_subscriptions";
    while($loop){
        $page_count++;
        //--//
        $data_table = "data";
        $post_fields = ['page_count' => $page_count];
        log_request($connection, $function, $post_fields);
        $data = json_decode(curl_data($url, $post_fields));
        $counts = count($data->{$data_table});
        //--//
        foreach($data->{$data_table} as &$row){
            $row = (object)$row;
            //--//
            $row->user_id = (int)trim($row->user_id);
            if(!empty($row->user_id) && $row->user_id > 0){
                $row = (array)$row;
                $subscription = [
                    'user_id'                => $row['user_id'],
                    'phone'                  => $row['phone'],
                    'network'                => $row['network'],
                    'start_datetime'         => $row['start_datetime'],
                    'expiry_datetime'        => $row['expiry_datetime'],
                    'status'                 => $row['status'],
                    'premier_user'           => $row['premier_user'],
                    'accesscode_id'          => $row['accesscode_id'],
                    'subscriptionContractId' => $row['subscriptionContractId'],
                    'strip_charged_id'       => null,
                    'created_at'             => $row['created_at'],
                    'updated_at'             => $row['updated_at'],
                ];
                //add_update_row((object)$subscription, "`subscriptions`", "user_id", $connection);
                $fetch = fetch_row("`subscriptions`", "user_id", $subscription['user_id'], $connection);
                if(!empty($fetch)){
                    $fetch_up_time = strtotime($fetch->updated_at);
                    $row_up_time = strtotime($subscription['updated_at']);
                    if($row_up_time > $fetch_up_time){
                        update_row((object)$subscription, "`subscriptions`", "user_id", $connection);
                    }
                }
                else{
                    add_row((object)$subscription, "`subscriptions`", "user_id", $connection);
                }
            }
        }
        //--//
        $loop = false;
    }
    $response = [
        'status'  => "1",
        'pages'   => $page_count,
        'message' => "$counts subscriptions data extracted...",
    ];
    echo json_encode($response, true);
    exit();
