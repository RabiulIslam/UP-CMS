<?php
    require_once((__DIR__)."/includes/conn.php");
    require_once((__DIR__)."/includes/curl.php");
    require_once((__DIR__)."/includes/inc.php");
    $counts = 0;
    $loop = true;
    $page_count = 0;
    $function = "getMerchants";
    $url = $api_base_url.$function;
    while($loop){
        $page_count++;
        //--//
        $data_table = "merchants";
        $post_fields = ['page_count' => $page_count];
        log_request($connection, $function, $post_fields);
        $data = json_decode(curl_data($url, $post_fields));
        $count = check_data_exists($data, $data_table, $page_count);
        //--//
        foreach($data->{$data_table} as &$row){
            unset($row->extra);
            $location = $row->additional_info2;
            $row->latitude = null;
            $row->longitude = null;
            $row->additional_info2 = null;
            $location = explode(",", trim(str_replace(" ", "", $location)));
            if(!empty($location) && count($location) > 1){
                $latitude = number_format((float)$location[0], 6, '.', '');
                $longitude = number_format((float)$location[1], 6, '.', '');
                $row->latitude = $latitude;
                $row->longitude = $longitude;
                $location = implode(",", [$latitude, $longitude]);
                $row->additional_info2 = $location;
            }
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
            $row->category = (int)$row->category;
            if(empty($row->category) || $row->category <= 0){
                $row->category = null;
            }
        }
        /*foreach($data->{$data_table} as &$row){
            $row->category_id = (int)$row->category_id;
            $row->category_image = trim($row->category_image);
            $row->category_name = ucwords(trim($row->category_name));
            $row->category_description = trim($row->category_description);
            if(empty($row->category_id) || $row->category_id <= 0){
                $row->category_id = null;
            }
            if(empty($row->category_name)){
                $row->category_name = null;
            }
            if(empty($row->category_image)){
                $row->category_image = null;
            }
            if(empty($row->category_description)){
                $row->category_description = null;
            }
            //--//
            if(!empty($row->category_id) && $row->category_id > 0){
                $category = [
                    'id'          => $row->category_id,
                    'name'        => $row->category_name,
                    'image'       => $row->category_image,
                    'description' => $row->category_description,
                ];
                add_update_row((object)$category, "`category`", "id", $connection);
            }
        }*/
        foreach($data->{$data_table} as &$row){
            $row = (array)$row;
            $merchant = [
                'id'                   => $row['user_id'],
                'name'                 => trim(ucwords(implode(" ", [
                    trim($row['firstname']),
                    trim($row['lastname'])
                ]))),
                'email'                => trim($row['email']),
                'emails'               => trim($row['emails']),
                'phone'                => trim($row['phone']),
                'phones'               => trim($row['phones']),
                'image'                => trim($row['base_image']),
                'address'              => trim($row['address']),
                'authorize_name'       => trim($row['authorize_name']),
                'term_and_conditions'  => trim($row['termsdoc']),
                'active'               => trim($row['is_active']),
                'created_at'           => trim($row['created']),
                'updated_at'           => trim($row['modified']),
                'TAC_accepted'         => "1",
                'pin'                  => null,
                'gender'               => null,
                'search_tags'          => null,
                'contract_start_date'  => null,
                'contract_expiry_date' => null,
            ];
            /*//$merchant_id = add_update_row((object)$merchant, "`merchants`", "id", $connection);*/
            $merchant_id = null;
            $fetch = fetch_row("`merchants`", "id", $merchant['id'], $connection);
            if(empty($fetch)){
                $merchant_id = add_row((object)$merchant, "`merchants`", "id", $connection);
            }
            else{
                if(!empty($merchant['updated_at']) && !empty($fetch->updated_at)){
                    if($merchant['updated_at'] > $fetch->updated_at){
                        $merchant_id = update_row((object)$merchant, "`merchants`", "id", $connection);
                    }
                }
            }
            //--//
            if(!empty($merchant_id) && $merchant_id > 0){
                $outlet = [
                    'merchant_id'  => $merchant_id,
                    'name'         => ucwords($row['merchant_name']),
                    'phone'        => $row['phone'],
                    'logo'         => $row['avatar_name'],
                    'image'        => $row['base_image'],
                    'address'      => $row['address'],
                    'latitude'     => $row['latitude'],
                    'longitude'    => $row['longitude'],
                    'neighborhood' => $row['address'],
                    'description'  => $row['description'],
                    'timings'      => $row['additional_info'],
                    'pin'          => null,
                    'type'         => null,
                    'special'      => null,
                    'search_tags'  => null,
                    'active'       => $row['is_active'],
                    'created_at'   => $row['created'],
                    'updated_at'   => $row['modified'],
                ];
                /*//add_update_row((object)$outlet, "`outlets`", "merchant_id", $connection);*/
                $fetch = fetch_row("`outlets`", "merchant_id", $outlet['merchant_id'], $connection);
                if(empty($fetch)){
                    add_row((object)$outlet, "`outlets`", "merchant_id", $connection);
                }
                else{
                    if(!empty($outlet['updated_at']) && !empty($fetch->updated_at)){
                        if($outlet['updated_at'] > $fetch->updated_at){
                            update_row((object)$outlet, "`outlets`", "merchant_id", $connection);
                        }
                    }
                }
                //--//
                $fetch = fetch_row("`outlets`", "merchant_id", $outlet['merchant_id'], $connection);
                $outlet_id = null;
                if(!empty($fetch)){
                    $fetch = (array)$fetch;
                    $outlet_id = (int)$fetch['id'];
                }
                if(!empty($outlet_id) && $outlet_id > 0){
                    $category_obj = (object)$row;
                    if(!empty($category_obj->category_id) && $category_obj->category_id > 0){
                        if(in_array($category_obj->category_id, [15, 31])){
                            $category_obj->category_id = 15;
                        }
                        else if(in_array($category_obj->category_id, [17, 29])){
                            $category_obj->category_id = 17;
                        }
                        else if(in_array($category_obj->category_id, [18, 28, 30, 64])){
                            $category_obj->category_id = 64;
                        }
                        else if(in_array($category_obj->category_id, [65])){
                            $category_obj->category_id = 65;
                        }
                        if(in_array($category_obj->category_id, [15, 17, 64, 65])){
                            $outlet_category = [
                                'outlet_id'   => $outlet_id,
                                'category_id' => $category_obj->category_id,
                            ];
                            add_row((object)$outlet_category, "`outlet_category`", "outlet_id", $connection);
                        }
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
