<?php
    function connect_db(){
        static $conn;
        if(empty($conn)){
            $conn = mysqli_connect("updater.cvbaznhpajws.eu-central-1.rds.amazonaws.com", "up_qatar2018", "up!2018&bymayar?h", "up_qatar");
            if(mysqli_connect_errno()){
                die("Error connecting MySQL: ".mysqli_connect_error());
                exit();
            }
            if(!mysqli_set_charset($conn, "utf8")){
                die("Error loading character set utf8: ".mysqli_error($conn));
                exit();
            }
        }
        return $conn;
    }
    
    function disconnect_db($conn){
        if(!mysqli_close($conn)){
            die("Error disconnecting MySQL: ".mysqli_error($conn));
            exit();
        }
    }
    
    function escape_values($row, $connection){
        $params = [];
        foreach($row as $column => $value){
            $column = "`".$column."`";
            $value = trim($value);
            if($value === "" || $value === "null"){
                $value = null;
                $params[] = "$column = null";
            }
            else{
                $value = "'".mysqli_real_escape_string($connection, $value)."'";
                $params[] = "$column = $value";
            }
        }
        return implode(", ", $params);
    }
    
    function fetch_row($table, $primary_key, $primary_value, $connection){
        if(!empty($primary_key = trim($primary_key)) && !empty($primary_value = mysqli_real_escape_string($connection, trim($primary_value)))){
            $select = "SELECT * FROM $table";
            $condition = "`$primary_key`='$primary_value'";
            $query = mysqli_query($connection, "$select WHERE $condition");// or die(mysqli_error($connection));
            $fetched_object = (object)mysqli_fetch_object($query);
            if(!empty($fetched_object) && !empty($fetched_object->{$primary_key})){
                return $fetched_object;
            }
        }
    }
    
    function add_row($row, $table, $primary_key, $connection){
        if(!empty($primary_key = trim($primary_key)) && !empty($primary_value = mysqli_real_escape_string($connection, trim($row->{$primary_key})))){
            $params = escape_values($row, $connection);
            $sql = "INSERT INTO $table SET $params";
            mysqli_query($connection, $sql);// or die(mysqli_error($connection));
            return mysqli_insert_id($connection);
        }
    }
    
    function update_row($row, $table, $primary_key, $connection){
        if(!empty($primary_key = trim($primary_key)) && !empty($primary_value = mysqli_real_escape_string($connection, trim($row->{$primary_key})))){
            $params = escape_values($row, $connection);
            $condition = "`$primary_key`='$primary_value'";
            $sql = "UPDATE $table SET $params WHERE $condition";
            mysqli_query($connection, $sql);// or die(mysqli_error($connection));
            return $row->{$primary_key};
        }
    }
    
    function add_update_row($row, $table, $primary_key, $connection){
        if(!empty($primary_key = trim($primary_key)) && !empty($primary_value = mysqli_real_escape_string($connection, trim($row->{$primary_key})))){
            $_fetched_object = fetch_row($table, $primary_key, $primary_value, $connection);
            if(empty($_fetched_object)){
                return add_row($row, $table, $primary_key, $connection);
            }
            else{
                return update_row($row, $table, $primary_key, $connection);
            }
        }
    }
    
    function log_request($connection, $function, $params = []){
        $function = "'".mysqli_real_escape_string($connection, $function)."'";
        $params = "'".mysqli_real_escape_string($connection, json_encode($params, true))."'";
        $fields = "`function` = $function, `params` = $params";
        $sql = "INSERT INTO `logs` SET $fields";
        mysqli_query($connection, $sql);// or die(mysqli_error($connection));
    }
    
    $connection = connect_db();
