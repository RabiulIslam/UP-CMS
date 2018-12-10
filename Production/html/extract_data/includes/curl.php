<?php
    function init_curl(){
        static $curl;
        if(empty($curl)){
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_HEADER, ["Content-Type:application/json"]);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        }
        return $curl;
    }
    
    function curl_data($url, $post_fields = []){
        $curl = init_curl();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query((array)$post_fields));
        $response = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        //$headers = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        return $body;
    }
    
    function close_curl($curl){
        curl_close($curl);
    }
    
    function check_data_exists($data, $key, $page){
        if(empty($data->{$key}) || ($count = count($data->{$key})) <= 0){
            $response = [
                'status'  => "0",
                'data'    => $data,
                'page'    => $page,
                'message' => "$key data not found",
            ];
            echo json_encode($response, true);
            exit();
        }
        return $count;
    }
