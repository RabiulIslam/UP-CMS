<?php
    require_once((__DIR__)."/includes/conn.php");
    require_once((__DIR__)."/includes/curl.php");
    require_once((__DIR__)."/includes/inc.php");
    $counts = 0;
    $data_table = "outlets";
    $sql = "SELECT `id`, `logo` FROM `$data_table` WHERE `logo` != '' AND `logo` IS NOT NULL AND `logo` NOT LIKE '%outlets_logo%'";
    //--//
    $query = mysqli_query($connection, $sql);
    while($row = mysqli_fetch_object($query)){
        $row->logo = explode("/", $row->logo);
        $row->logo = implode("/", array_filter($row->logo));
        $row->logo = trim(str_replace(" ", "", $row->logo));
        $row->logo = trim(str_replace("//", "/", $row->logo));
        $row->logo = (string)trim($row->logo);
        if($row->logo === "null" || $row->logo === "NULL"){
            $row->logo = "";
        }
        if(!empty($row->logo)){
            $extension = explode(".", $row->logo);
            $extension = trim(strtolower(end($extension)));
            if(!empty($extension)){
                $file_name = strtolower(trim($row->id.'.'.$extension));
                $outlet_logo = 'outlets_logo_'.$file_name;
                //--//
                $source = trim($baseMerchantsUrl.$row->logo);
                $destination = trim($imagesBasePath.$outlet_logo);
                //--//
                $outlet = new stdClass();
                $outlet->id = $row->id;
                $outlet->logo = "";
                //--//
                $old_path = $imagesBasePath.$row->logo;
                if(!empty($row->logo) && !file_exists($old_path)){
                    if(copy($source, $destination)){
                        $outlet->logo = $outlet_logo;
                    }
                }
                else{
                    $outlet->logo = $row->logo;
                }
                update_row($outlet, "`$data_table`", "id", $connection);
                $counts++;
            }
        }
    }
    //--//
    $response = [
        'status'  => "1",
        'message' => "$counts $data_table logos extracted...",
    ];
    echo json_encode($response, true);
    exit();
