<?php
    error_reporting(0);
    /*
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    */
    header('content-type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
    header('Access-Control-Max-Age: 1000');
    header('Access-Control-Allow-Headers: Content-Type, Authorization,lang, X-Requested-With,AccessToken');
    include_once 'appMethods.php';
    include_once 'outh.php';
    Page::Load();
    
    class Page{
        static function Load(){
            try{
                $params = [];
                $method = "";
                $methodName = "";
                $pattern = '/[^0-9]/';
                $patternemail = "/([\w\-]+\@[\w\-]+\.[\w\-]+)/";
                $regdecimal = '/^[0-9]+(\.[0-9]{1,2})?$/';
                $reglat = '/^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/';
                $reglong = '/^(\+|-)?(?:180(?:(?:\.0{1,6})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,6})?))$/';
                $bothmethods = outh::methodParamscheck();
                extract($bothmethods);
                $response = ["status" => 400, "message" => null, "data" => null];
                switch([$methodName, $method]){
                    /* 1:------------------------------method start here signIn------------------------------*/
                    case ["signIn", "POST"]: {
                        $errors = [];
                        if($params['email'] == '' || !preg_match($patternemail, $params['email']) || (strlen($params['email']) > 50)){
                            $errors['Name'] = "email Required";
                            $errors['type'] = "alphanumeric";
                            $errors['formate_size'] = "Max size 50 characters and must be valid email address";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['password'] == '' || (strlen($params['password']) > 20)){
                            $errors['Name'] = "password Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 20 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::signIn($params);
                        break;
                    }
                    /* 1:------------------------------method start here checkEmail------------------------------*/
                    case ["checkEmail", "POST"]: {
                        $errors = [];
                        if($params['email'] == '' || !preg_match($patternemail, $params['email']) || (strlen($params['email']) > 50)){
                            $errors['Name'] = "email Required";
                            $errors['type'] = "alphanumeric";
                            $errors['formate_size'] = "Max size 50 characters and must be valid email address";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::checkEmail($params);
                        break;
                    }
                    /* 1:------------------------------method start here checkPhone------------------------------*/
                    case ["checkPhone", "POST"]: {
                        $errors = [];
                        if($params['phone'] == '' || strlen($params['phone']) >= 20 || (!(is_numeric($params['phone'])))){
                            $errors['Name'] = "phone Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Must be 20 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::checkPhone($params);
                        break;
                    }
                    /* 1:------------------------------method start here forgotPassword------------------------------*/
                    case ["forgotPassword", "POST"]: {
                        $errors = [];
                        if($params['email'] == '' || !preg_match($patternemail, $params['email']) || (strlen($params['email']) > 50)){
                            $errors['Name'] = "email Required";
                            $errors['type'] = "alphanumeric";
                            $errors['formate_size'] = "Max size 50 characters and must be valid email address";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::forgotPassword($params);
                        break;
                    }
                    /* 1:------------------------------method start here changePassword------------------------------*/
                    case ["changePassword", "POST"]: {
                        $errors = [];
                        if(($params['type'] == '') || ($params['type'] != 'user' && $params['type'] != 'admin')){
                            $errors['Name'] = "type";
                            $errors['type'] = "Enum";
                            $errors['formate_size'] = "user or admin";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['type'] == 'user'){
                            $password = preg_match($pattern, $params['password']);
                            if($params['password'] == '' || (strlen($params['password']) > 4) || ($password > 0)){
                                $errors['Name'] = "password Required";
                                $errors['type'] = "int";
                                $errors['formate_size'] = "Max size 4 int";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        else{
                            if($params['password'] == '' || (strlen($params['password']) > 20) || ($password > 0)){
                                $errors['Name'] = "password Required";
                                $errors['type'] = "int";
                                $errors['formate_size'] = "Max size 4 characters";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if($params['password_reset_token'] == ''){
                            $errors['Name'] = "password_reset_token Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "alphanumeric";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::changePassword($params);
                        break;
                    }
                    /* 1:------------------------------method start here getProfile------------------------------*/
                    case ["getProfile", "GET"]: {
                        echo AppMethods::getProfile($params);
                        break;
                    }
                    /* 1:------------------------------method start here addAdmin------------------------------*/
                    case ["addAdmin", "POST"]: {
                        $errors = [];
                        $password = preg_match($pattern, $params['password']);
                        if($params['name'] == '' || (strlen($params['name']) > 50)){
                            $errors['Name'] = "name Required";
                            $errors['type'] = "alphanumeric";
                            $errors['formate_size'] = "Max size 50 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['phone'] == '' || (strlen($params['phone']) > 50)){
                            $errors['Name'] = "phone Required";
                            $errors['type'] = "alphanumeric";
                            $errors['formate_size'] = "Max size 50 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['email'] == '' || !preg_match($patternemail, $params['email']) || (strlen($params['email']) > 50)){
                            $errors['Name'] = "email Required";
                            $errors['type'] = "alphanumeric";
                            $errors['formate_size'] = "Max size 50 characters and must be valid email address";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['password'] == '' || (strlen($params['password']) > 50)){
                            $errors['Name'] = "password Required";
                            $errors['type'] = "alphanumeric";
                            $errors['formate_size'] = "Max size 50 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::addAdmin($params);
                        break;
                    }
                    /* 1:------------------------------method start here updateAdmin------------------------------*/
                    case ["updateAdmin", "POST"]: {
                        $errors = [];
                        $id = preg_match($pattern, $params['id']);
                        if($params['id'] == '' || ($id > 0 || $params['id'] == 0)){
                            $errors['Name'] = "id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 11 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['email'] != ''){
                            if(!preg_match($patternemail, $params['email']) || (strlen($params['email']) > 50)){
                                $errors['Name'] = "email";
                                $errors['type'] = "alphanumeric";
                                $errors['formate_size'] = "Max size 50 characters and must be valid email address";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        echo AppMethods::updateAdmin($params);
                        break;
                    }
                    /* 1:------------------------------method start here deleteAdmin------------------------------*/
                    case ["deleteAdmin", "POST"]: {
                        $errors = [];
                        $id = preg_match($pattern, $params['id']);
                        if($params['id'] == '' || ($id > 0 || $params['id'] == 0)){
                            $errors['Name'] = "id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 11 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::deleteAdmin($params);
                        break;
                    }
                    /* 1:------------------------------method start here getAdmins------------------------------*/
                    case ["getAdmins", "GET"]: {
                        echo AppMethods::getAdmins($params);
                        break;
                    }
                    /* 1:------------------------------method start here addCategory------------------------------*/
                    case ["addCategory", "POST"]: {
                        $errors = [];
                        if($params['name'] == '' || (strlen($params['name']) > 50)){
                            $errors['Name'] = "name Required";
                            $errors['type'] = "alphanumeric";
                            $errors['formate_size'] = "Max size 50 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if(empty($_FILES['image']['name'])){
                            $errors['Name'] = "image Required";
                            $errors['type'] = "file";
                            $errors['formate_size'] = "type jpg,jpeg,JPG,png";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if(!empty($_FILES['image']['name'])){
                            $params['image'] = Page::uploadimg('image', $params['image_name']);
                        }
                        else if($params['image_name'] != "" && (preg_match("/^[^\?]+\.(jpg|jpeg|gif|png)(?:\?|$)/", $params['image_name']) == 1)){
                            $params['image'] = $params['image_name'];
                        }
                        echo AppMethods::addCategory($params);
                        break;
                    }
                    /* 1:------------------------------method start here updateCategory------------------------------*/
                    case ["updateCategory", "POST"]: {
                        $errors = [];
                        $id = preg_match($pattern, $params['id']);
                        if($params['id'] == '' || ($id > 0 || $params['id'] == 0)){
                            $errors['Name'] = "id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 11 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if(!empty($_FILES['image']['name'])){
                            $params['image'] = Page::uploadimg('image', $params['image_name']);
                        }
                        else if($params['image_name'] != "" && (preg_match("/^[^\?]+\.(jpg|jpeg|gif|png)(?:\?|$)/", $params['image_name']) == 1)){
                            $params['image'] = $params['image_name'];
                        }
                        echo AppMethods::updateCategory($params);
                        break;
                    }
                    /* 1:------------------------------method start here deleteCategory------------------------------*/
                    case ["deleteCategory", "POST"]: {
                        $errors = [];
                        $id = preg_match($pattern, $params['id']);
                        if($params['id'] == '' || ($id > 0 || $params['id'] == 0)){
                            $errors['Name'] = "id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 11 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::deleteCategory($params);
                        break;
                    }
                    /* 1:------------------------------method start here getCategories------------------------------*/
                    case ["getCategories", "GET"]: {
                        echo AppMethods::getCategories($params);
                        break;
                    }
                    /* 1:------------------------------method start here addMerchant------------------------------*/
                    case ["addMerchant", "POST"]: {
                        $errors = [];
                        if($params['name'] == '' || (strlen($params['name']) > 50)){
                            $errors['Name'] = "name Required";
                            $errors['type'] = "alphanumeric";
                            $errors['formate_size'] = "Max size 50 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['phone'] != ''){
                            if(strlen($params['phone']) >= 20 || (!(is_numeric($params['phone'])))){
                                $errors['Name'] = "phone Required";
                                $errors['type'] = "int";
                                $errors['formate_size'] = "Must be 20 characters";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if($params['gender'] != ''){
                            if(($params['gender'] == '' || ($params['gender'] != 'male' && $params['gender'] != 'female'))){
                                $errors['Name'] = "gender";
                                $errors['type'] = "Enum";
                                $errors['formate_size'] = "male or female";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if($params['contract_start_date'] != ''){
                            if($params['contract_start_date'] == '' || (!preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $params['contract_start_date']))){
                                $errors['Name'] = "contract_start_date Required";
                                $errors['type'] = "datetime";
                                $errors['formate_size'] = "datetime formate etc  2018-02-13";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                            if($params['contract_expiry_date'] == '' || (!preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $params['contract_expiry_date']))){
                                $errors['Name'] = "contract_expiry_date Required";
                                $errors['type'] = "datetime";
                                $errors['formate_size'] = "datetime formate etc  2018-02-13";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                            $contract_start_date = new DateTime($params['contract_start_date']);
                            $contract_expiry_date = new DateTime($params['contract_expiry_date']);
                            if($contract_expiry_date <= $contract_start_date){
                                $errors['Name'] = "contract_expiry_date must be greater then contract_start_date";
                                $errors['type'] = "datetime";
                                $errors['formate_size'] = "datetime formate etc  2018-02-13";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        /*if(!empty($_FILES['image']['name']))
			$params['image']=Page::uploadimg('image',$params['image_name']);		*/
                        echo AppMethods::addMerchant($params);
                        break;
                    }
                    /* 1:------------------------------method start here addMerchants------------------------------*/
                    case ["addMerchants", "POST"]: {
                        if($params['merchants'] == ''){
                            $errors['Name'] = "merchants Required";
                            $errors['type'] = "json";
                            $errors['formate_size'] = "Required valid json formate";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['merchants'] != ''){
                            $string = $params['merchants'];
                            $string = stripslashes($string);
                            $varr = '';
                            $varr = is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? '1' : '2';
                            if($varr == 2){
                                $errors['Name'] = "merchants Required";
                                $errors['type'] = "json";
                                $errors['formate_size'] = "Required valid json formate";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        echo AppMethods::addMerchants($params);
                        break;
                    }
                    /* 1:------------------------------method start here updateMerchant------------------------------*/
                    case ["updateMerchant", "POST"]: {
                        $errors = [];
                        $id = preg_match($pattern, $params['id']);
                        if($params['id'] == '' || ($id > 0 || $params['id'] == 0)){
                            $errors['Name'] = "id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 11 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['email'] != ''){
                            if($params['email'] == '' || !preg_match($patternemail, $params['email']) || (strlen($params['email']) > 50)){
                                $errors['Name'] = "email Required";
                                $errors['type'] = "alphanumeric";
                                $errors['formate_size'] = "Max size 50 characters and must be valid email address";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if($params['phone'] != ''){
                            if(strlen($params['phone']) >= 20 || (!(is_numeric($params['phone'])))){
                                $errors['Name'] = "phone Required";
                                $errors['type'] = "int";
                                $errors['formate_size'] = "Must be 20 characters";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if($params['gender'] != ''){
                            if((($params['gender'] != 'male' && $params['gender'] != 'female'))){
                                $errors['Name'] = "gender";
                                $errors['type'] = "Enum";
                                $errors['formate_size'] = "male or female";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if($params['contract_start_date'] != ''){
                            if($params['contract_start_date'] == '' || (!preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $params['contract_start_date']))){
                                $errors['Name'] = "contract_start_date Required";
                                $errors['type'] = "datetime";
                                $errors['formate_size'] = "datetime formate etc  2018-02-13";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                            if($params['contract_expiry_date'] == '' || (!preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $params['contract_expiry_date']))){
                                $errors['Name'] = "contract_expiry_date Required";
                                $errors['type'] = "datetime";
                                $errors['formate_size'] = "datetime formate etc  2018-02-13";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                            $contract_start_date = new DateTime($params['contract_start_date']);
                            $contract_expiry_date = new DateTime($params['contract_expiry_date']);
                            if($contract_expiry_date <= $contract_start_date){
                                $errors['Name'] = "contract_expiry_date must be greater then contract_start_date";
                                $errors['type'] = "datetime";
                                $errors['formate_size'] = "datetime formate etc  2018-02-13";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        /*if(!empty($_FILES['image']['name']))
			$params['image']=Page::uploadimg('image',$params['image_name']);	*/
                        echo AppMethods::updateMerchant($params);
                        break;
                    }
                    /* 1:------------------------------method start here deleteMerchant------------------------------*/
                    case ["deleteMerchant", "POST"]: {
                        $errors = [];
                        $id = preg_match($pattern, $params['id']);
                        if($params['id'] == '' || ($id > 0 || $params['id'] == 0)){
                            $errors['Name'] = "id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 11 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::deleteMerchant($params);
                        break;
                    }
                    /* 1:------------------------------method start here ADMerchant------------------------------*/
                    case ["ADMerchant", "POST"]: {
                        $errors = [];
                        $id = preg_match($pattern, $params['id']);
                        if($params['id'] == '' || ($id > 0 || $params['id'] == 0)){
                            $errors['Name'] = "id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 11 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if(($params['active'] == '') || ($params['active'] != '0' && $params['active'] != '1')){
                            $errors['Name'] = "active";
                            $errors['type'] = "Enum";
                            $errors['formate_size'] = "0 or 1";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::ADMerchant($params);
                        break;
                    }
                    /* 1:------------------------------method start here getMerchants------------------------------*/
                    case ["getMerchants", "GET"]: {
                        $errors = [];
                        if($params['sortby'] != ''){
                            if(($params['orderby'] == '') || ($params['orderby'] != 'ASC' && $params['orderby'] != 'DESC')){
                                $errors['Name'] = "orderby";
                                $errors['type'] = "Enum";
                                $errors['formate_size'] = "ASC or DESC";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        echo AppMethods::getMerchants($params);
                        break;
                    }
                    /* 1:------------------------------method start here getAllMerchants------------------------------*/
                    case ["getAllMerchants", "GET"]: {
                        echo AppMethods::getAllMerchants($params);
                        break;
                    }
                    /* 1:------------------------------method start here addOutlet------------------------------*/
                    case ["addOutlet", "POST"]: {
                        $errors = [];
                        $merchant_id = preg_match($pattern, $params['merchant_id']);
                        //  $category_id = preg_match($pattern, $params['category_id']);
                        if($params['merchant_id'] == '' || ($merchant_id > 0 || $params['merchant_id'] == 0)){
                            $errors['Name'] = "merchant_id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 11 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        $regexcomma = '/^[1-9,]+$/';
                        if($params['category_ids'] == '' || !preg_match($regexcomma, $params['category_ids'])){
                            $errors['Name'] = "category_ids Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "comma separated category_ids";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['name'] == '' || (strlen($params['name']) > 50)){
                            $errors['Name'] = "name Required";
                            $errors['type'] = "alphanumeric";
                            $errors['formate_size'] = "Max size 50 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['search_tags'] == '' || (strlen($params['search_tags']) > 100)){
                            $errors['Name'] = "search_tags Required";
                            $errors['type'] = "alphanumeric";
                            $errors['formate_size'] = "Max size 100 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['phone'] != ''){
                            if(strlen($params['phone']) > 20 || (!(is_numeric($params['phone'])))){
                                $errors['Name'] = "phone Required";
                                $errors['type'] = "int";
                                $errors['formate_size'] = "Must be 10 characters";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        $pin = preg_match($pattern, $params['pin']);
                        if($params['pin'] == '' || (strlen($params['pin']) > 4) || ($pin > 0)){
                            $errors['Name'] = "pin Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 4 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['address'] == '' || (strlen($params['address']) > 200)){
                            $errors['Name'] = "address Required";
                            $errors['type'] = "alphanumeric";
                            $errors['formate_size'] = "Max size 50 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['description'] == '' || (strlen($params['description']) > 500)){
                            $errors['Name'] = "description Required";
                            $errors['type'] = "alphanumeric";
                            $errors['formate_size'] = "Max size 500 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['timings'] == ''){
                            $errors['Name'] = "timings Required";
                            $errors['type'] = "time";
                            $errors['formate_size'] = "Max size 500 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        $params['latitude'] = floatval($params['latitude']);
                        $params['longitude'] = floatval($params['longitude']);
                        if(!is_double($params['latitude']) || $params['latitude'] == 0){
                            $errors['Name'] = "latitude Required";
                            $errors['type'] = "double";
                            $errors['formate_size'] = "Max size 50 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if(!is_double($params['longitude']) || $params['longitude'] == 0){
                            $errors['Name'] = "longitude Required";
                            $errors['type'] = "double";
                            $errors['formate_size'] = "Max size 50 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if(($params['special'] != "0" && $params['special'] != "1")){
                            $errors['Name'] = "special Required";
                            $errors['type'] = "enum";
                            $errors['formate_size'] = "0 or 1";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if(!empty($_FILES['image']['name'])){
                            $params['image'] = Page::uploadimg('image', $params['image_name']);
                        }
                        else if($params['image_name'] != "" && (preg_match("/^[^\?]+\.(jpg|jpeg|gif|png)(?:\?|$)/", $params['image_name']) == 1)){
                            $params['image'] = $params['image_name'];
                        }
                        if(!empty($_FILES['logo']['name'])){
                            $params['logo'] = Page::uploadimg('logo', $params['logo_name']);
                        }
                        else if($params['logo_name'] != "" && (preg_match("/^[^\?]+\.(jpg|jpeg|gif|png)(?:\?|$)/", $params['logo_name']) == 1)){
                            $params['logo'] = $params['logo_name'];
                        }
                        echo AppMethods::addOutlet($params);
                        break;
                    }
                    /* 1:------------------------------method start here addOutlets------------------------------*/
                    case ["addOutlets", "POST"]: {
                        if($params['outlets'] == ''){
                            $errors['Name'] = "outlets Required";
                            $errors['type'] = "json";
                            $errors['formate_size'] = "Required valid json formate";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['outlets'] != ''){
                            $string = $params['outlets'];
                            $string = stripslashes($string);
                            $varr = '';
                            $varr = is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? '1' : '2';
                            if($varr == 2){
                                $errors['Name'] = "outlets Required";
                                $errors['type'] = "json";
                                $errors['formate_size'] = "Required valid json formate";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        echo AppMethods::addOutlets($params);
                        break;
                    }
                    /* 1:------------------------------method start here updateOutlet------------------------------*/
                    case ["updateOutlet", "POST"]: {
                        $errors = [];
                        $id = preg_match($pattern, $params['id']);
                        if($params['id'] == '' || ($id > 0 || $params['id'] == 0)){
                            $errors['Name'] = "id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 11 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['pin'] != ''){
                            $pin = preg_match($pattern, $params['pin']);
                            if($params['pin'] == '' || (strlen($params['pin']) > 4) || ($pin > 0)){
                                $errors['Name'] = "pin Required";
                                $errors['type'] = "int";
                                $errors['formate_size'] = "Max size 4 characters";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if($params['category_ids'] != ''){
                            $regexcomma = '/^[1-9,]+$/';
                            if($params['category_ids'] == '' || !preg_match($regexcomma, $params['category_ids'])){
                                $errors['Name'] = "category_ids Required";
                                $errors['type'] = "int";
                                $errors['formate_size'] = "comma separated category_ids";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if($params['latitude'] != '' || $params['longitude'] != ''){
                            $params['latitude'] = floatval($params['latitude']);
                            $params['longitude'] = floatval($params['longitude']);
                            if(!is_double($params['latitude']) || $params['latitude'] == 0){
                                $errors['Name'] = "latitude Required";
                                $errors['type'] = "double";
                                $errors['formate_size'] = "Max size 50 characters";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                            if(!is_double($params['longitude']) || $params['longitude'] == 0){
                                $errors['Name'] = "longitude Required";
                                $errors['type'] = "double";
                                $errors['formate_size'] = "Max size 50 characters";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if($params['special'] != ''){
                            if($params['special'] != "0" && $params['special'] != "1"){
                                $errors['Name'] = "special Required";
                                $errors['type'] = "enum";
                                $errors['formate_size'] = "0 or 1";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if(!empty($_FILES['image']['name'])){
                            $params['image'] = Page::uploadimg('image', $params['image_name']);
                        }
                        else if($params['image_name'] != "" && (preg_match("/^[^\?]+\.(jpg|jpeg|gif|png)(?:\?|$)/", $params['image_name']) == 1)){
                            $params['image'] = $params['image_name'];
                        }
                        if(!empty($_FILES['logo']['name'])){
                            $params['logo'] = Page::uploadimg('logo', $params['logo_name']);
                        }
                        else if($params['logo_name'] != "" && (preg_match("/^[^\?]+\.(jpg|jpeg|gif|png)(?:\?|$)/", $params['logo_name']) == 1)){
                            $params['logo'] = $params['logo_name'];
                        }
                        echo AppMethods::updateOutlet($params);
                        break;
                    }
                    /* 1:------------------------------method start here deleteOutlet------------------------------*/
                    case ["deleteOutlet", "POST"]: {
                        $errors = [];
                        $id = preg_match($pattern, $params['id']);
                        if($params['id'] == '' || ($id > 0 || $params['id'] == 0)){
                            $errors['Name'] = "id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 11 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::deleteOutlet($params);
                        break;
                    }
                    /* 1:------------------------------method start here ADOutlet------------------------------*/
                    case ["ADOutlet", "POST"]: {
                        $errors = [];
                        $id = preg_match($pattern, $params['id']);
                        if($params['id'] == '' || ($id > 0 || $params['id'] == 0)){
                            $errors['Name'] = "id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 11 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if(($params['active'] == '' || ($params['active'] != '0' && $params['active'] != '1'))){
                            $errors['Name'] = "active";
                            $errors['type'] = "Enum";
                            $errors['formate_size'] = "0 or 1";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::ADOutlet($params);
                        break;
                    }
                    /* 1:------------------------------method start here getOutlets------------------------------*/
                    case ["getOutlets", "GET"]: {
                        $errors = [];
                        if($params['sortby'] != ''){
                            if(($params['orderby'] == '') || ($params['orderby'] != 'ASC' && $params['orderby'] != 'DESC')){
                                $errors['Name'] = "orderby";
                                $errors['type'] = "Enum";
                                $errors['formate_size'] = "ASC or DESC";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        echo AppMethods::getOutlets($params);
                        break;
                    }
                    /* 1:------------------------------method start here getAllOutlets------------------------------*/
                    case ["getAllOutlets", "GET"]: {
                        $errors = [];
                        echo AppMethods::getAllOutlets($params);
                        break;
                    }
                    /* 1:------------------------------method start here addOffer------------------------------*/
                    case ["addOffer", "POST"]: {
                        $errors = [];
                        $outlet_id = preg_match($pattern, $params['outlet_id']);
                        if($params['outlet_id'] == '' || ($outlet_id > 0 || $params['outlet_id'] == 0)){
                            $errors['Name'] = "outlet_id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 11 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['title'] == '' || (strlen($params['title']) > 90)){
                            $errors['Name'] = "title Required";
                            $errors['type'] = "alphanumeric";
                            $errors['formate_size'] = "Max size 90 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['search_tags'] == '' || (strlen($params['search_tags']) > 100)){
                            $errors['Name'] = "search_tags Required";
                            $errors['type'] = "alphanumeric";
                            $errors['formate_size'] = "Max size 100 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['description'] == '' || (strlen($params['description']) > 500)){
                            $errors['Name'] = "description Required";
                            $errors['type'] = "alphanumeric";
                            $errors['formate_size'] = "Max size 500 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if(($params['special'] == '' || ($params['special'] != '0' && $params['special'] != '1'))){
                            $errors['Name'] = "special";
                            $errors['type'] = "Enum";
                            $errors['formate_size'] = "0 or 1";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        else{
                            if($params['special'] == '1'){
                                if($params['special_type'] == ''){
                                    $errors['Name'] = "special_type";
                                    $errors['type'] = "alphanumeric";
                                    $errors['formate_size'] = "Max size 90 characters";
                                    throw new Exception(implode(" | ", $errors), 400);
                                }
                            }
                            else if($$params['special'] == '0'){
                                unset($params['special_type']);
                            }
                        }
                        if($params['price'] != ''){
                            if($params['price'] == '' || (!is_numeric($params['price']))){
                                $errors['Name'] = "price Required";
                                $errors['type'] = "decimal";
                                $errors['formate_size'] = "Max size 50 characters";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if($params['special_price'] != ''){
                            if($params['special_price'] == '' || (!is_numeric($params['special_price']))){
                                $errors['Name'] = "special_price Required";
                                $errors['type'] = "decimal";
                                $errors['formate_size'] = "Max size 50 characters";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if($params['approx_saving'] == '' || (!is_numeric($params['approx_saving']))){
                            $errors['Name'] = "approx_saving Required";
                            $errors['type'] = "decimal";
                            $errors['formate_size'] = "Max size 50 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['start_datetime'] == '' || (!preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $params['start_datetime']))){
                            $errors['Name'] = "start_datetime Required";
                            $errors['type'] = "datetime";
                            $errors['formate_size'] = "datetime formate etc  2018-02-13 11:37:58";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['end_datetime'] == '' || (!preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $params['end_datetime']))){
                            $errors['Name'] = "end_datetime Required";
                            $errors['type'] = "datetime";
                            $errors['formate_size'] = "datetime formate etc  2018-02-13 11:37:58";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        $start_datetime = new DateTime($params['start_datetime']);
                        $end_datetime = new DateTime($params['end_datetime']);
                        if($end_datetime <= $start_datetime){
                            $errors['Name'] = "end_datetime must be greater then start_datetime";
                            $errors['type'] = "datetime";
                            $errors['formate_size'] = "datetime formate etc  2018-02-13 11:37:58";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if(($params['renew'] == '' || ($params['renew'] != '0' && $params['renew'] != '1'))){
                            $errors['Name'] = "renew";
                            $errors['type'] = "Enum";
                            $errors['formate_size'] = "0 or 1";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['renew'] == '0'){
                            $redemptions = preg_match($pattern, $params['redemptions']);
                            if($params['redemptions'] == '' || ($redemptions > 0 || $params['redemptions'] == 0)){
                                $errors['Name'] = "redemptions Required";
                                $errors['type'] = "int";
                                $errors['formate_size'] = "Max size 11 characters";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        else{
                            $params['redemptions'] = '0';
                        }
                        if($params['per_user'] != ''){
                            $per_user = preg_match($pattern, $params['per_user']);
                            if($params['per_user'] == '' || ($per_user > 0 || $params['per_user'] == 0)){
                                $errors['Name'] = "per_user Required";
                                $errors['type'] = "int";
                                $errors['formate_size'] = "Max size 11 characters";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        else{
                            $params['redemptions'] = '1';
                        }
                        if(empty($_FILES['image']['name'])){
                            $errors['Name'] = "image Required";
                            $errors['type'] = "file";
                            $errors['formate_size'] = "type jpg,jpeg,JPG,png";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if(!empty($_FILES['image']['name'])){
                            $params['image'] = Page::uploadimg('image', $params['image_name']);
                        }
                        else if($params['image_name'] != "" && (preg_match("/^[^\?]+\.(jpg|jpeg|gif|png)(?:\?|$)/", $params['image_name']) == 1)){
                            $params['image'] = $params['image_name'];
                        }
                        echo AppMethods::addOffer($params);
                        break;
                    }
                    /* 1:------------------------------method start here addOffers------------------------------*/
                    case ["addOffers", "POST"]: {
                        if($params['offers'] == ''){
                            $errors['Name'] = "offers Required";
                            $errors['type'] = "json";
                            $errors['formate_size'] = "Required valid json formate";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['offers'] != ''){
                            $string = $params['offers'];
                            $string = stripslashes($string);
                            $varr = '';
                            $varr = is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? '1' : '2';
                            if($varr == 2){
                                $errors['Name'] = "offers Required";
                                $errors['type'] = "json";
                                $errors['formate_size'] = "Required valid json formate";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        echo AppMethods::addOffers($params);
                        break;
                    }
                    /* 1:------------------------------method start here updateOffer------------------------------*/
                    case ["updateOffer", "POST"]: {
                        $errors = [];
                        $id = preg_match($pattern, $params['id']);
                        if($params['id'] == '' || ($id > 0 || $params['id'] == 0)){
                            $errors['Name'] = "id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 11 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['price'] != ''){
                            if((!is_numeric($params['price']))){
                                $errors['Name'] = "price Required";
                                $errors['type'] = "decimal";
                                $errors['formate_size'] = "Max size 50 characters";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if($params['special_price'] != ''){
                            if((!is_numeric($params['special_price']))){
                                $errors['Name'] = "special_price Required";
                                $errors['type'] = "decimal";
                                $errors['formate_size'] = "Max size 50 characters";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if($params['approx_saving'] != ''){
                            if((!is_numeric($params['approx_saving']))){
                                $errors['Name'] = "approx_saving Required";
                                $errors['type'] = "decimal";
                                $errors['formate_size'] = "Max size 50 characters";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if($params['start_datetime'] != '' || $params['end_datetime'] != ''){
                            if($params['start_datetime'] == '' || (!preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $params['start_datetime']))){
                                $errors['Name'] = "start_datetime Required";
                                $errors['type'] = "datetime";
                                $errors['formate_size'] = "datetime formate etc  2018-02-13 11:37:58";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                            if($params['end_datetime'] == '' || (!preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $params['end_datetime']))){
                                $errors['Name'] = "end_datetime Required";
                                $errors['type'] = "datetime";
                                $errors['formate_size'] = "datetime formate etc  2018-02-13 11:37:58";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                            $start_datetime = new DateTime($params['start_datetime']);
                            $end_datetime = new DateTime($params['end_datetime']);
                            if($end_datetime <= $start_datetime){
                                $errors['Name'] = "end_datetime must be greater then start_datetime";
                                $errors['type'] = "datetime";
                                $errors['formate_size'] = "datetime formate etc  2018-02-13 11:37:58";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if($params['special'] != ''){
                            if(($params['special'] == '' || ($params['special'] != '0' && $params['special'] != '1'))){
                                $errors['Name'] = "special";
                                $errors['type'] = "Enum";
                                $errors['formate_size'] = "0 or 1";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                            else{
                                if($params['special'] == '1'){
                                    if($params['special_type'] == ''){
                                        $errors['Name'] = "special_type";
                                        $errors['type'] = "alphanumeric";
                                        $errors['formate_size'] = "Max size 90 characters";
                                        throw new Exception(implode(" | ", $errors), 400);
                                    }
                                }
                                else if($$params['special'] == '0'){
                                    unset($params['special_type']);
                                }
                            }
                        }
                        if($params['renew'] != ''){
                            if(($params['renew'] == '' || ($params['renew'] != '0' && $params['renew'] != '1'))){
                                $errors['Name'] = "renew";
                                $errors['type'] = "Enum";
                                $errors['formate_size'] = "0 or 1";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if($params['redemptions'] != ''){
                            $redemptions = preg_match($pattern, $params['redemptions']);
                            if($params['redemptions'] == '' || ($redemptions > 0 || $params['redemptions'] == 0)){
                                $errors['Name'] = "redemptions Required";
                                $errors['type'] = "int";
                                $errors['formate_size'] = "Max size 11 characters";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if($params['per_user'] != ''){
                            $per_user = preg_match($pattern, $params['per_user']);
                            if($params['per_user'] == '' || ($per_user > 0 || $params['per_user'] == 0)){
                                $errors['Name'] = "per_user Required";
                                $errors['type'] = "int";
                                $errors['formate_size'] = "Max size 11 characters";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if(!empty($_FILES['image']['name'])){
                            $params['image'] = Page::uploadimg('image', $params['image_name']);
                        }
                        else if($params['image_name'] != "" && (preg_match("/^[^\?]+\.(jpg|jpeg|gif|png)(?:\?|$)/", $params['image_name']) == 1)){
                            $params['image'] = $params['image_name'];
                        }
                        echo AppMethods::updateOffer($params);
                        break;
                    }
                    /* 1:------------------------------method start here updateMultipleOffer------------------------------*/
                    case ["updateMultipleOffer", "POST"]: {
                        $errors = [];
                        $regexcomma = '/^[1-9,]+$/';
                        if($params['ids'] == '' || !preg_match($regexcomma, $params['ids'])){
                            $errors['Name'] = "ids Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "comma separated ids";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['start_datetime'] != '' || $params['end_datetime'] != ''){
                            if($params['start_datetime'] == '' || (!preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $params['start_datetime']))){
                                $errors['Name'] = "start_datetime Required";
                                $errors['type'] = "datetime";
                                $errors['formate_size'] = "datetime formate etc  2018-02-13 11:37:58";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                            if($params['end_datetime'] == '' || (!preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $params['end_datetime']))){
                                $errors['Name'] = "end_datetime Required";
                                $errors['type'] = "datetime";
                                $errors['formate_size'] = "datetime formate etc  2018-02-13 11:37:58";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                            $start_datetime = new DateTime($params['start_datetime']);
                            $end_datetime = new DateTime($params['end_datetime']);
                            if($end_datetime <= $start_datetime){
                                $errors['Name'] = "end_datetime must be greater then start_datetime";
                                $errors['type'] = "datetime";
                                $errors['formate_size'] = "datetime formate etc  2018-02-13 11:37:58";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if(($params['active'] == '' || ($params['active'] != '0' && $params['active'] != '1'))){
                            $errors['Name'] = "active";
                            $errors['type'] = "Enum";
                            $errors['formate_size'] = "0 or 1";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::updateMultipleOffer($params);
                        break;
                    }
                    /* 1:------------------------------method start here deleteOffer------------------------------*/
                    case ["deleteOffer", "POST"]: {
                        $errors = [];
                        $id = preg_match($pattern, $params['id']);
                        if($params['id'] == '' || ($id > 0 || $params['id'] == 0)){
                            $errors['Name'] = "id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 11 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::deleteOffer($params);
                        break;
                    }
                    /* 1:------------------------------method start here ADOffer------------------------------*/
                    case ["ADOffer", "POST"]: {
                        $errors = [];
                        $id = preg_match($pattern, $params['id']);
                        if($params['id'] == '' || ($id > 0 || $params['id'] == 0)){
                            $errors['Name'] = "id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 11 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if(($params['active'] == '' || ($params['active'] != '0' && $params['active'] != '1'))){
                            $errors['Name'] = "active";
                            $errors['type'] = "Enum";
                            $errors['formate_size'] = "0 or 1";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::ADOffer($params);
                        break;
                    }
                    /* 1:------------------------------method start here getOffers------------------------------*/
                    case ["getOffers", "GET"]: {
                        $errors = [];
                        if($params['sortby'] != ''){
                            if(($params['orderby'] == '') || ($params['orderby'] != 'ASC' && $params['orderby'] != 'DESC')){
                                $errors['Name'] = "orderby";
                                $errors['type'] = "Enum";
                                $errors['formate_size'] = "ASC or DESC";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        echo AppMethods::getOffers($params);
                        break;
                    }
                    /* 1:------------------------------method start here getOfferDetail------------------------------*/
                    case ["getOfferDetail", "GET"]: {
                        $errors = [];
                        $id = preg_match($pattern, $params['id']);
                        if($params['id'] == '' || ($id > 0 || $params['id'] == 0)){
                            $errors['Name'] = "id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 11 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::getOfferDetail($params);
                        break;
                    }
                    /* 1:------------------------------method start here addNotification------------------------------*/
                    case ["addNotification", "POST"]: {
                        $errors = [];
                        if($params['title'] == '' || (strlen($params['title']) > 50)){
                            $errors['Name'] = "title Required";
                            $errors['type'] = "alphanumeric";
                            $errors['formate_size'] = "Max size 50 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['push'] == '' || ($params['push'] != "0" && $params['push'] != "1")){
                            $errors['Name'] = "push Required";
                            $errors['type'] = "enum";
                            $errors['formate_size'] = "0, 1";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if(($params['audience']) == '' || ($params['audience'] != "userCreatedDate" && $params['audience'] != "specificusers" && $params['audience'] != "allusers")){
                            $errors['Name'] = "audience Required";
                            $errors['type'] = "enum";
                            $errors['formate_size'] = "userCreatedDate, specificusers, allusers";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if(($params['platform']) == '' || ($params['platform'] != "Both" && $params['platform'] != "android" && $params['platform'] != "ios")){
                            $errors['Name'] = "platform Required";
                            $errors['type'] = "enum";
                            $errors['formate_size'] = "Both, android, ios";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['audience'] == 'userCreatedDate'){
                            $params['specificUsers'] = null;
                            if(($params['dates']) == '' || ($params['dates'] != "Both" && $params['dates'] != "Greater" && $params['dates'] != "Less")){
                                $errors['Name'] = "dates Required";
                                $errors['type'] = "enum";
                                $errors['formate_size'] = "Both, Greater, Less";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                            if($params['dates'] == "Both"){
                                if($params['greater_than'] == '' || (!preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $params['greater_than']))){
                                    $errors['Name'] = "greater_than Required";
                                    $errors['type'] = "datetime";
                                    $errors['formate_size'] = "datetime formate etc  2018-02-13 11:37:58";
                                    throw new Exception(implode(" | ", $errors), 400);
                                }
                                if($params['less_than'] == '' || (!preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $params['less_than']))){
                                    $errors['Name'] = "less_than Required";
                                    $errors['type'] = "datetime";
                                    $errors['formate_size'] = "datetime formate etc  2018-02-13 11:37:58";
                                    throw new Exception(implode(" | ", $errors), 400);
                                }
                            }
                            if($params['dates'] == "Greater"){
                                $params['less_than'] = null;
                                if($params['greater_than'] == '' || (!preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $params['greater_than']))){
                                    $errors['Name'] = "greater_than Required";
                                    $errors['type'] = "datetime";
                                    $errors['formate_size'] = "datetime formate etc  2018-02-13 11:37:58";
                                    throw new Exception(implode(" | ", $errors), 400);
                                }
                            }
                            if($params['dates'] == "Less"){
                                $params['greater_than'] = null;
                                if($params['less_than'] == '' || (!preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $params['less_than']))){
                                    $errors['Name'] = "less_than Required";
                                    $errors['type'] = "datetime";
                                    $errors['formate_size'] = "datetime formate etc  2018-02-13 11:37:58";
                                    throw new Exception(implode(" | ", $errors), 400);
                                }
                            }
                        }
                        if($params['audience'] == 'specificusers'){
                            $params['greater_than'] = null;
                            $params['less_than'] = null;
                            $string = $params['specificUsers'];
                            $string = stripslashes($string);
                            $varr = '';
                            $varr = is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? '1' : '2';
                            if($varr == 2){
                                $errors['Name'] = "specificUsers Required";
                                $errors['type'] = "json";
                                $errors['formate_size'] = "Required valid json formate";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if($params['audience'] == 'allusers'){
                            $params['specificUsers'] = null;
                            $params['start_datetime'] = null;
                            $params['end_datetime'] = null;
                        }
                        if($params['message'] == '' || (strlen($params['message']) > 250)){
                            $errors['Name'] = "message Required";
                            $errors['type'] = "alphanumeric";
                            $errors['formate_size'] = "Max size 50 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::addNotification($params);
                        break;
                    }
                    /* 1:------------------------------method start here reSendNotification------------------------------*/
                    case ["reSendNotification", "POST"]: {
                        $errors = [];
                        $notification_id = preg_match($pattern, $params['notification_id']);
                        if($params['notification_id'] == '' || ($notification_id > 0 || $params['notification_id'] == 0)){
                            $errors['Name'] = "notification_id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 11 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::reSendNotification($params);
                        break;
                    }
                    /* 1:------------------------------method start here updateNotification------------------------------*/
                    case ["updateNotification", "POST"]: {
                        $errors = [];
                        $id = preg_match($pattern, $params['id']);
                        if($params['id'] == '' || ($id > 0 || $params['id'] == 0)){
                            $errors['Name'] = "id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 11 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::updateNotification($params);
                        break;
                    }
                    /* 1:------------------------------method start here deleteNotification------------------------------*/
                    case ["deleteNotification", "POST"]: {
                        $errors = [];
                        $id = preg_match($pattern, $params['id']);
                        if($params['id'] == '' || ($id > 0 || $params['id'] == 0)){
                            $errors['Name'] = "id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 11 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::deleteNotification($params);
                        break;
                    }
                    /* 1:------------------------------method start here getNotifications------------------------------*/
                    case ["getNotifications", "GET"]: {
                        echo AppMethods::getNotifications($params);
                        break;
                    }
                    /* 1:------------------------------method start here addAccessCode------------------------------*/
                    case ["addAccessCode", "POST"]: {
                        $errors = [];
                        $redemptions = preg_match($pattern, $params['redemptions']);
                        $days = preg_match($pattern, $params['days']);
                        $number = preg_match($pattern, $params['number']);
                        if($params['title'] == '' || (strlen($params['title']) > 50)){
                            $errors['Name'] = "title Required";
                            $errors['type'] = "alphanumeric";
                            $errors['formate_size'] = "Max size 50 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['number'] != ""){
                            if(($number > 0 || $params['number'] == 0)){
                                $errors['Name'] = "number Required";
                                $errors['type'] = "int";
                                $errors['formate_size'] = "Max size 11 characters";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        else{
                            $params['number'] = 1;
                        }
                        if($params['redemptions'] == '' || ($redemptions > 0 || $params['redemptions'] == 0 || $params['redemptions'] > 50000)){
                            $errors['Name'] = "redemptions Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max 50000 integers";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['expiry_datetime'] == '' || (!preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $params['expiry_datetime']))){
                            $errors['Name'] = "expiry_datetime Required";
                            $errors['type'] = "datetime";
                            $errors['formate_size'] = "datetime formate etc  0000-00-00 00:00:00";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['expiry_datetime'] < date('Y-m-d H:i:s')){
                            $errors['Name'] = "expiry_datetime Required";
                            $errors['type'] = "datetime";
                            $errors['formate_size'] = "datetime formate etc  2018-02-13 11:37:58 and must be greater then curent date time";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['days'] == '' || ($days > 0 || $params['days'] == 0) || ($params['days'] > 5000)){
                            $errors['Name'] = "days Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max 5000 integers";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['number'] == 1){
                            if($params['code'] != ""){
                                if(strlen($params['code']) > 10){
                                    $errors['Name'] = "code Required";
                                    $errors['type'] = "alphanumeric";
                                    $errors['formate_size'] = "Max size 10 characters";
                                    throw new Exception(implode(" | ", $errors), 400);
                                }
                            }
                        }
                        else{
                            $params['code'] = "";
                        }
                        echo AppMethods::addAccessCode($params);
                        break;
                    }
                    /* 1:------------------------------method start here updateOffer------------------------------*/
                    case ["updateAccessCode", "POST"]: {
                        $errors = [];
                        $id = preg_match($pattern, $params['id']);
                        if($params['id'] == '' || ($id > 0 || $params['id'] == 0)){
                            $errors['Name'] = "id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 11 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::updateAccessCode($params);
                        break;
                    }
                    /* 1:------------------------------method start here deleteAccessCode------------------------------*/
                    case ["deleteAccessCode", "POST"]: {
                        /* $errors=array();
			 $id = preg_match($pattern, $params['id']);
			  if ($params['id'] == '' || ($id > 0 || $params['id'] ==0)   )
			  {
				  $errors['Name']= "id Required";
				  $errors['type']= "int";
				  $errors['formate_size']= "Max size 11 characters";
				  throw new Exception(implode(" | ",$errors),400);
			  }
			echo  AppMethods::deleteAccessCode($params);
			break;	*/
                    }
                    /* 1:------------------------------method start here getAccessCodes------------------------------*/
                    case ["getAccessCodes", "GET"]: {
                        echo AppMethods::getAccessCodes($params);
                        break;
                    }
                    /* 1:------------------------------method start here updateOffer------------------------------*/
                    case ["getMultipleAccessCode", "GET"]: {
                        $errors = [];
                        $id = preg_match($pattern, $params['id']);
                        if($params['id'] == '' || ($id > 0 || $params['id'] == 0)){
                            $errors['Name'] = "id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 11 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::getMultipleAccessCode($params);
                        break;
                    }
                    /* 1:------------------------------method start here getUsers------------------------------*/
                    case ["getUsers", "GET"]: {
                        $errors = [];
                        if($params['greater_than'] != ""){
                            if($params['greater_than'] == '' || (!preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $params['greater_than']))){
                                $errors['Name'] = "greater_than Required";
                                $errors['type'] = "datetime";
                                $errors['formate_size'] = "datetime formate etc  2018-02-13 11:37:58";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                            if($params['less_than'] == '' || (!preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $params['less_than']))){
                                $errors['Name'] = "less_than Required";
                                $errors['type'] = "datetime";
                                $errors['formate_size'] = "datetime formate etc  2018-02-13 11:37:58";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if($params['network'] != ''){
                            if(($params['network'] == '' || ($params['network'] != "ooredoo" && $params['network'] != "vodafone"))){
                                $errors['Name'] = "network";
                                $errors['type'] = "Enum";
                                $errors['formate_size'] = "ooredoo, vodafone";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if($params['sortby'] != ''){
                            if(($params['orderby'] == '') || ($params['orderby'] != 'ASC' && $params['orderby'] != 'DESC')){
                                $errors['Name'] = "orderby";
                                $errors['type'] = "Enum";
                                $errors['formate_size'] = "ASC or DESC";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        echo AppMethods::getUsers($params);
                        break;
                    }
                    /* 1:------------------------------method start here getNonUsers------------------------------*/
                    case ["getNonUsers", "GET"]: {
                        $errors = [];
                        if($params['greater_than'] != ""){
                            if($params['greater_than'] == '' || (!preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $params['greater_than']))){
                                $errors['Name'] = "greater_than Required";
                                $errors['type'] = "datetime";
                                $errors['formate_size'] = "datetime formate etc  2018-02-13 11:37:58";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                            if($params['less_than'] == '' || (!preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $params['less_than']))){
                                $errors['Name'] = "less_than Required";
                                $errors['type'] = "datetime";
                                $errors['formate_size'] = "datetime formate etc  2018-02-13 11:37:58";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if($params['sortby'] != ''){
                            if(($params['orderby'] == '') || ($params['orderby'] != 'ASC' && $params['orderby'] != 'DESC')){
                                $errors['Name'] = "orderby";
                                $errors['type'] = "Enum";
                                $errors['formate_size'] = "ASC or DESC";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        echo AppMethods::getNonUsers($params);
                        break;
                    }
                    /* 1:------------------------------method start here updateUser------------------------------*/
                    case ["updateUser", "POST"]: {
                        $errors = [];
                        $id = preg_match($pattern, $params['id']);
                        if($params['id'] == '' || ($id > 0 || $params['id'] == 0)){
                            $errors['Name'] = "id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 11 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['email'] != ''){
                            if(!preg_match($patternemail, $params['email']) || (strlen($params['email']) > 50)){
                                $errors['Name'] = "email";
                                $errors['type'] = "alphanumeric";
                                $errors['formate_size'] = "Max size 50 characters and must be valid email address";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if($params['gender'] != ''){
                            if((($params['gender'] != 'male' && $params['gender'] != 'female'))){
                                $errors['Name'] = "gender";
                                $errors['type'] = "Enum";
                                $errors['formate_size'] = "male or female";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if($params['network'] != ''){
                            if($params['network'] == '' || ($params['network'] != "ooredoo" && $params['network'] != "vodafone")){
                                $errors['Name'] = "network";
                                $errors['type'] = "Enum";
                                $errors['formate_size'] = "ooredoo, vodafone";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if($params['phone'] != ''){
                            if(strlen($params['phone']) >= 20 || (!(is_numeric($params['phone'])))){
                                $errors['Name'] = "phone Required";
                                $errors['type'] = "int";
                                $errors['formate_size'] = "Must be 20 characters";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if($params['DOB'] != ''){
                            if((!preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $params['DOB']))){
                                $errors['Name'] = "DOB Required";
                                $errors['type'] = "alphanumeric";
                                $errors['formate_size'] = "Must be date formate etc 2018-01-12";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        if($params['password'] != ''){
                            $password = preg_match($pattern, $params['password']);
                            $old_password = preg_match($pattern, $params['old_password']);
                            if((strlen($params['password']) > 4) || ($password > 0)){
                                $errors['Name'] = "password Required";
                                $errors['type'] = "int";
                                $errors['formate_size'] = "Max size 4 characters";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                            if($params['old_password'] == '' || (strlen($params['old_password']) > 4) || ($old_password > 0)){
                                $errors['Name'] = "old_password  Required";
                                $errors['type'] = "int";
                                $errors['formate_size'] = "Max size 4 characters";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        echo AppMethods::updateUser($params);
                        break;
                    }
                    /* 1:------------------------------method start here getSubscriptions------------------------------*/
                    case ["getSubscriptions", "GET"]: {
                        $errors = [];
                        if($params['user_id'] != ''){
                            $user_id = preg_match($pattern, $params['user_id']);
                            if($params['user_id'] == '' || ($user_id > 0 || $params['user_id'] == 0)){
                                $errors['Name'] = "user_id Required";
                                $errors['type'] = "int";
                                $errors['formate_size'] = "Max size 11 characters";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        echo AppMethods::getSubscriptions($params);
                        break;
                    }
                    /* 1:------------------------------method start here getSubscriptionLogs------------------------------*/
                    case ["getSubscriptionLogs", "GET"]: {
                        $errors = [];
                        if($params['user_id'] != ''){
                            $user_id = preg_match($pattern, $params['user_id']);
                            if($params['user_id'] == '' || ($user_id > 0 || $params['user_id'] == 0)){
                                $errors['Name'] = "user_id Required";
                                $errors['type'] = "int";
                                $errors['formate_size'] = "Max size 11 characters";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        echo AppMethods::getSubscriptionLogs($params);
                        break;
                    }
                    /* 1:------------------------------method start here unsubscribe------------------------------*/
                    case ["unsubscribe", "POST"]: {
                        $errors = [];
                        if($params['user_id'] != ''){
                            $user_id = preg_match($pattern, $params['user_id']);
                            if($params['user_id'] == '' || ($user_id > 0 || $params['user_id'] == 0)){
                                $errors['Name'] = "user_id Required";
                                $errors['type'] = "int";
                                $errors['formate_size'] = "Max size 11 characters";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        echo AppMethods::unsubscribe($params);
                        break;
                    }
                    /* 1:------------------------------method start here getFavouriteOffers------------------------------*/
                    case ["getFavouriteOffers", "GET"]: {
                        $errors = [];
                        $user_id = preg_match($pattern, $params['user_id']);
                        if($params['user_id'] == '' || ($user_id > 0 || $params['user_id'] == 0)){
                            $errors['Name'] = "user_id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 11 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::getFavouriteOffers($params);
                        break;
                    }
                    /* 1:------------------------------method start here getOrders------------------------------*/
                    case ["getOrders", "GET"]: {
                        if($params['user_id'] != ''){
                            $user_id = preg_match($pattern, $params['user_id']);
                            if($params['user_id'] == '' || ($user_id > 0 || $params['user_id'] == 0)){
                                $errors['Name'] = "user_id Required";
                                $errors['type'] = "int";
                                $errors['formate_size'] = "Max size 11 characters";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                        }
                        echo AppMethods::getOrders($params);
                        break;
                    }
                    /* 1:------------------------------method start here getOrderReviews------------------------------*/
                    case ["getOrderReviews", "GET"]: {
                        $errors = [];
                        $order_id = preg_match($pattern, $params['order_id']);
                        if($params['order_id'] == '' || ($order_id > 0 || $params['order_id'] == 0)){
                            $errors['Name'] = "order_id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 11 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::getOrderReviews($params);
                        break;
                    }
                    /* 1:------------------------------method start here getVersion------------------------------*/
                    case ["getVersion", "GET"]: {
                        echo AppMethods::getVersion($params);
                        break;
                    }
                    /* 1:------------------------------method start here updateVersion------------------------------*/
                    case ["updateVersion", "POST"]: {
                        $errors = [];
                        if(($params['type'] == '') || ($params['type'] != "ios" && $params['type'] != "android")){
                            $errors['Name'] = "type";
                            $errors['type'] = "Enum";
                            $errors['formate_size'] = "ios,android";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if(($params['forcefully_updated'] == '') || ($params['forcefully_updated'] != "0" && $params['forcefully_updated'] != "1")){
                            $errors['Name'] = "forcefully_updated";
                            $errors['type'] = "Enum";
                            $errors['formate_size'] = "0,1";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if(($params['upDown'] == '') || ($params['upDown'] != "0" && $params['upDown'] != "1")){
                            $errors['Name'] = "upDown";
                            $errors['type'] = "Enum";
                            $errors['formate_size'] = "0,1";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::updateVersion($params);
                        break;
                    }
                    /* 1:------------------------------method start here getDefaults------------------------------*/
                    case ["getDefaults", "GET"]: {
                        echo AppMethods::getDefaults($params);
                        break;
                    }
                    /* 1:------------------------------method start here updateDefault------------------------------*/
                    case ["addUpdateDefault", "POST"]: {
                        $errors = [];
                        if(($params['type'] == '') || ($params['type'] != "home-page" && $params['type'] != "subscription" && $params['type'] != "uber")){
                            $errors['Name'] = "type";
                            $errors['type'] = "Enum";
                            $errors['formate_size'] = "home-page,subscription,uber";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['type'] == "uber"){
                            if(($params['uber'] == '') || ($params['uber'] != "0" && $params['uber'] != "1")){
                                $errors['Name'] = "type";
                                $errors['type'] = "Enum";
                                $errors['formate_size'] = "0,1";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                            $params['text'] = null;
                        }
                        else{
                            if($params['text'] == '' || (strlen($params['text']) > 250)){
                                $errors['Name'] = "text Required";
                                $errors['type'] = "alphanumeric";
                                $errors['formate_size'] = "Max size 50 characters";
                                throw new Exception(implode(" | ", $errors), 400);
                            }
                            if($params['type'] == "subscription"){
                                if(($params['paragraph'] == '') || ($params['paragraph'] != "1" && $params['paragraph'] != "2")){
                                    $errors['Name'] = "paragraph";
                                    $errors['type'] = "Enum";
                                    $errors['formate_size'] = "1,2";
                                    throw new Exception(implode(" | ", $errors), 400);
                                }
                            }
                        }
                        echo AppMethods::addUpdateDefault($params);
                        break;
                    }
                    /* 1:------------------------------method start here getCreditcardPackages------------------------------*/
                    case ["getCreditcardPackages", "GET"]: {
                        echo AppMethods::getCreditcardPackages($params);
                        break;
                    }
                    /* 1:------------------------------method start here updateCreditcardPackages------------------------------*/
                    case ["updateCreditcardPackages", "POST"]: {
                        $errors = [];
                        $id = preg_match($pattern, $params['id']);
                        if($params['id'] == '' || ($id > 0 || $params['id'] == 0)){
                            $errors['Name'] = "id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 11 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::updateCreditcardPackages($params);
                        break;
                    }
                    /* 1:------------------------------method start here logs------------------------------*/
                    case ["logs", "POST"]: {
                        echo AppMethods::logs($params);
                        break;
                    }
                    /* 1:------------------------------method start here logout------------------------------*/
                    case ["logout", "GET"]: {
                        echo AppMethods::logout($params);
                        break;
                    }
                    /* 1:------------------------------default default default default default------------------------------*/
                    default : {
                        $errors['Name'] = "method Required";
                        $errors['type'] = "string";
                        $errors['formate_size'] = 'Invalid Method Name '.$methodName." Or Routes ".$_SERVER['REQUEST_METHOD'];
                        throw new Exception(implode(" | ", $errors), 405);
                    }
                }
            }
            catch(Exception $e){
                $response["status"] = $e->getCode();
                $response["message"] = $e->getMessage();
                echo json_encode($response);
                header("HTTP/1.1 ".$e->getCode()." ".$e->getMessage()."");
            }
        }
        /* 2:------------------------------method start here uploadimg 1------------------------------*/
        static function uploadimg($name, $image_name = null){
            $uploaddir = '../../../uploads/';
            $thumbs = '../../../thumbs/';
            $file_type = $_FILES[$name]['type']; //returns the mimetype
            $array = explode('.', $_FILES[$name]['name']);
            $extension = end($array);
            $allowedex = ["jpeg", "pg", "gif", "png", "bmp", "tiff", "jpg", "JPG"];
            if(!in_array($extension, $allowedex)){
                $response["status"] = 400;
                $response["message"] = 'Invalid image1 type , not allowed extension with '.$extension.'';
                echo json_encode($response);
                header("HTTP/1.1 400");
                exit();
            }
            $allowed = ["image/jpeg", "image/jpg", "image/JPG", "image/gif", "image/png", "image/bmp", "image/tiff"];
            if(in_array($file_type, $allowed)){
                if($image_name != null){
                    $image_name_explod = explode('.', $image_name);
                }
                if($image_name == null || (preg_match("/^[^\?]+\.(jpg|jpeg|gif|png)(?:\?|$)/", $image_name) == 0) || $image_name_explod[1] != $extension){
                    $temp = explode(".", $_FILES[$name]["name"]);
                    $file = round(microtime(true)).rand(1, 999).'.'.end($temp);
                }
                else{
                    $file = $image_name;
                }
                $uploadfile = $uploaddir.$file;
                if(move_uploaded_file($_FILES[$name]['tmp_name'], $uploadfile)){
                    $maxDim = 250;
                    $image_info = getimagesize($uploadfile);
                    $image_width = $image_info[0];
                    $image_height = $image_info[1];
                    $ratio = $image_width / $image_height;
                    if($image_width < 250 && $image_height < 250){
                        $width = $image_width;
                        $height = $image_height;
                    }
                    else{
                        if($ratio > 1){
                            $width = $maxDim;
                            $height = $maxDim / $ratio;
                        }
                        else{
                            $width = $maxDim * $ratio;
                            $height = $maxDim;
                        }
                    }
                    $field_name = $name;
                    $target_folder = $uploaddir;
                    $thumb_folder = $thumbs;
                    $file_name = '';
                    $thumb = true;
                    $thumb_width = $width;
                    $thumb_height = $height;
                    $file_ext = end($temp);
                    $fileName = $file;
                    //Page::cwUpload($field_name,$target_folder,'',TRUE,$thumb_folder,$thumb_width,$thumb_height,$fileName,$file_ext);
                    return $file;
                }
                else{
                    $response["status"] = 400;
                    $response["message"] = ''.$name.' is not uploaded due to unknown reason';
                    echo json_encode($response);
                    header("HTTP/1.1 400");
                    exit();
                }
            }
            else{
                $response["status"] = 400;
                $response["message"] = 'Invalid image1 type';
                echo json_encode($response);
                header("HTTP/1.1 400");
                exit();
            }
        }
        /* 2:------------------------------method start here cwUpload 1------------------------------*/
        static function cwUpload($field_name = '', $target_path = '', $file_name = '', $thumb = false, $thumb_path = '', $thumb_width = '', $thumb_height = '', $fileName = '', $file_ext = ''){
            //upload image path
            $upload_image = $target_path.basename($fileName);
            //thumbnail creation
            if($thumb == true){
                $thumbnail = $thumb_path.$fileName;
                list($width, $height) = getimagesize($upload_image);
                //echo $target_path.$fileName;
                $source = @imagecreatefromstring(file_get_contents($target_path.$fileName));
                $thumb_create = @imagecreatetruecolor($thumb_width, $thumb_height);
                imagecopyresampled($thumb_create, $source, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
                switch($file_ext){
                    case 'jpg' || 'jpeg':
                        imagejpeg($thumb_create, $thumbnail, 90);
                        break;
                    case 'png':
                        imagepng($thumb_create, $thumbnail, 90);
                        break;
                    case 'gif':
                        imagegif($thumb_create, $thumbnail, 90);
                        break;
                    default:
                        imagejpeg($thumb_create, $thumbnail, 90);
                }
            }
        }
    }
