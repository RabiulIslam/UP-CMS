<?php
    require_once((__DIR__)."/includes/conn.php");
    require_once((__DIR__)."/includes/curl.php");
    require_once((__DIR__)."/includes/inc.php");
    $counts = 0;
    $loop = true;
    $page_count = 0;
    $function = "getProducts";
    $url = $api_base_url.$function;
    while($loop){
        $page_count++;
        //--//
        $data_table = "products";
        $post_fields = ['page_count' => $page_count];
        log_request($connection, $function, $post_fields);
        $data = json_decode(curl_data($url, $post_fields));
        $count = check_data_exists($data, $data_table, $page_count);
        //--//
        foreach($data->{$data_table} as &$row){
            $delimiters = [',', ';', '-', '_', ' '];
            $row->tag = strtolower(trim($row->tag));
            $row->tag = explode($delimiters[0], str_replace($delimiters, $delimiters[0], $row->tag));
            $row->tag = array_unique(array_filter($row->tag));
            $row->tag = implode(" ", $row->tag);
            //--//
            $row->special = "0";
            $row->festival = strtolower(trim($row->festival));
            $row->festival = (!empty($row->festival)) ? $row->festival : null;
            if(!empty($row->festival) && !in_array($row->festival, ['ramadan', 'biryani', 'burger'])){
                $row->festival = "other";
            }
            if(!empty($row->festival)){
                $row->special = "1";
            }
            //--//
            $row->gender = (int)trim($row->gender);
            if(empty($row->gender)){
                $row->gender = 0;
            }
            if(!empty($row->gender) && $row->gender > 0){
                $row->gender = $row->gender - 1;
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
            $outlet_id = null;
            $merchant_id = (int)mysqli_real_escape_string($connection, $row->merchant);
            if(!empty($merchant_id) && $merchant_id > 0){
                $merchant = fetch_row("`merchants`", "id", $merchant_id, $connection);
                if(!empty($merchant)){
                    $fields = [
                        "id"                   => $merchant_id,
                        "pin"                  => $row->merchantpin,
                        "gender"               => null,
                        "search_tags"          => $row->tag,
                        "contract_start_date"  => $row->special_from_date,
                        "contract_expiry_date" => $row->special_to_date
                    ];
                    update_row((object)$fields, "`merchants`", "id", $connection);
                    //--//
                    $outlet = fetch_row("`outlets`", "merchant_id", $merchant_id, $connection);
                    if(!empty($outlet)){
                        $fields = [
                            "merchant_id" => $merchant_id,
                            "pin"         => $row->merchantpin,
                            "type"        => $row->gender,
                            "special"     => $row->festival,
                            "search_tags" => $row->tag
                        ];
                        update_row((object)$fields, "`outlets`", "merchant_id", $connection);
                        //--//
                        $outlet = (array)$outlet;
                        $outlet_id = (int)$outlet['id'];
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
            }
            //--//
            $row = (array)$row;
            if(!empty($outlet_id) && $outlet_id > 0){
                $product = [
                    'id'             => $row['entity_id'],
                    'outlet_id'      => $outlet_id,
                    'title'          => $row['name'],
                    'image'          => $row['image'],
                    'SKU'            => $row['sku'],
                    'search_tags'    => $row['tag'],
                    'price'          => $row['price'],
                    'special_price'  => $row['special_price'],
                    'approx_saving'  => (float)$row['approximatesavings'],
                    'start_datetime' => $row['special_from_date'],
                    'end_datetime'   => $row['special_to_date'],
                    'valid_for'      => $row['fineprint'],
                    'description'    => $row['description'],
                    'special'        => $row['special'],
                    'special_type'   => $row['festival'],
                    'renew'          => "1",
                    'active'         => "1",
                    'per_user'       => "1",
                    'created_at'     => $row['created_at'],
                    'updated_at'     => $row['updated_at'],
                ];
                /*//add_update_row((object)$product, "`offers`", "id", $connection);*/
                $fetch = fetch_row("`offers`", "id", $product['id'], $connection);
                if(empty($fetch)){
                    add_row((object)$product, "`offers`", "id", $connection);
                }
                else{
                    if(!empty($product['updated_at']) && !empty($fetch->updated_at)){
                        if($product['updated_at'] > $fetch->updated_at){
                            update_row((object)$product, "`offers`", "id", $connection);
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
