<?php
    require_once((__DIR__)."/includes/conn.php");
    require_once((__DIR__)."/includes/curl.php");
    require_once((__DIR__)."/includes/inc.php");
    $counts = 0;
    $data_table = "offers";
    $sql = "SELECT `id`, `image` FROM `$data_table` WHERE `image` != '' AND `image` IS NOT NULL AND `image` NOT LIKE '%products_image%'";
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
                $product_image = 'products_image_'.$file_name;
                //--//
                $source = trim($baseProductsUrl.$row->image);
                $destination = trim($imagesBasePath.$product_image);
                //--//
                $new_row = new stdClass();
                $new_row->id = $row->id;
                $new_row->image = "";
                //--//
                $old_path = $imagesBasePath.$row->image;
                if(!empty($row->image) && !file_exists($old_path)){
                    if(copy($source, $destination)){
                        $new_row->image = $product_image;
                    }
                }
                else{
                    $new_row->image = $row->image;
                }
                update_row($new_row, "`$data_table`", "id", $connection);
                $counts++;
            }
        }
    }
    //--//
    $response = [
        'status'  => "1",
        'message' => "$counts $data_table images extracted...",
    ];
    echo json_encode($response, true);
    exit();
