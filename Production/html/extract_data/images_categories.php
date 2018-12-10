<?php
    require_once((__DIR__)."/includes/conn.php");
    require_once((__DIR__)."/includes/curl.php");
    require_once((__DIR__)."/includes/inc.php");
    $counts = 0;
    $data_table = "category";
    $sql = "SELECT `id`, `image` FROM `$data_table` WHERE `image` != '' AND `image` IS NOT NULL";
    //--//
    $query = mysqli_query($connection, $sql);
    while($row = mysqli_fetch_object($query)){
        $row->image = explode("/", $row->image);
        $row->image = implode("/", array_filter($row->image));
        $row->image = trim(str_replace(" ", "", $row->image));
        $row->image = trim(str_replace("//", "/", $row->image));
        $row->image = trim($row->image);
        if(!empty($row->image)){
            $extension = explode(".", $row->image);
            $extension = trim(strtolower(end($extension)));
            if(!empty($extension)){
                $file_name = strtolower(trim($row->id.'.'.$extension));
                $category_image = 'categories_image_'.$file_name;
                //--//
                $source = trim($baseCategoryUrl.$row->image);
                $destination = trim($imagesBasePath.$category_image);
                //--//
                $new_row = new stdClass();
                $new_row->id = $row->id;
                //--//
                if(copy($source, $destination)){
                    $new_row->image = $category_image;
                }
                else{
                    $new_row->image = "";
                }
                $counts++;
                //--//
                add_update_row($new_row, "`$data_table`", "id", $connection);
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
