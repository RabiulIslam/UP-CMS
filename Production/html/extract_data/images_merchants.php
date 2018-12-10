<?php
    require_once((__DIR__)."/includes/conn.php");
    require_once((__DIR__)."/includes/curl.php");
    require_once((__DIR__)."/includes/inc.php");
    $outlets_counts = 0;
    $merchants_counts = 0;
    $data_table = "merchants";
    $sql = "SELECT `id`, `image` FROM `$data_table` WHERE `image` != '' AND `image` IS NOT NULL AND `image` NOT LIKE '%merchants_image%'";
    //--//
    $query = mysqli_query($connection, $sql);
    while($row = mysqli_fetch_object($query)){
        $row->image = explode("/", $row->image);
        $row->image = implode("/", array_filter($row->image));
        $row->image = trim(str_replace(" ", "", $row->image));
        $row->image = trim(str_replace("//", "/", $row->image));
        $row->image = (string)trim($row->image);
        if($row->image === "null" || $row->image === "NULL"){
            $row->image = "";
        }
        if(!empty($row->image)){
            $extension = explode(".", $row->image);
            $extension = trim(strtolower(end($extension)));
            if(!empty($extension)){
                $file_name = strtolower(trim($row->id.'.'.$extension));
                $merchant_image = 'merchants_image_'.$file_name;
                $outlet_image = 'outlets_image_'.$file_name;
                //--//
                $source = trim($baseMerchantsUrl.$row->image);
                $destination = trim($imagesBasePath.$merchant_image);
                $destination2 = trim($imagesBasePath.$outlet_image);
                //--//
                $outlet = new stdClass();
                $outlet->merchant_id = $row->id;
                $outlet->image = "";
                //--//
                $merchant = new stdClass();
                $merchant->id = $row->id;
                $merchant->image = "";
                //--//
                $old_path = $imagesBasePath.$row->image;
                if(!empty($row->image) && !file_exists($old_path)){
                    if(copy($source, $destination)){
                        copy($source, $destination2);
                        //--//
                        $outlet->image = $outlet_image;
                        $merchant->image = $merchant_image;
                    }
                }
                else{
                    $outlet->image = $row->image;
                    $merchant->image = $row->image;
                }
                update_row($merchant, "`merchants`", "id", $connection);
                $merchants_counts++;
                //--//
                update_row($outlet, "`outlets`", "merchant_id", $connection);
                $outlets_counts++;
            }
        }
    }
    //--//
    $response = [
        'status'  => "1",
        'message' => "$merchants_counts merchants and $outlets_counts outlets images extracted...",
    ];
    echo json_encode($response, true);
    exit();
