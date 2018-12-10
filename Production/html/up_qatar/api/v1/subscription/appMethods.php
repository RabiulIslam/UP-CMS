<?php
    include_once 'dbMethods.php';
    include_once 'nonRegisteredUserSub.php';
    include_once 'premierUser.php';
    
    class AppMethods{
        /* 1:------------------------------method start here validatemsisdn 1------------------------------*/
        static function validatemsisdn($params){
            $response = ["status" => null, "message" => null, "data" => null];
            $result = DbMethods::validatemsisdn($params);
            $error = strpos($result, 'mysql_Error:-');
            if($error === 0){
                $response["status"] = 500;
                $response["message"] = "Your device has lost connection due to database error.";
                $response["data"] = $result;
                header("HTTP/1.1 500");
            }
            else{
                if($result['code'] == 1){
                    $response["status"] = 200;
                    $response["message"] = "Response";
                    $response["data"] = $result['data'];
                    header("HTTP/1.1 200");
                }
                else{
                    $response["status"] = 409;
                    if($result['data'] == 'Invalid MSISDN'){
                        $result['data'] = 'Invalid Phone Number';
                    }
                    $response["message"] = $result['data'];
                    $response["data"] = $result['data'];
                    header("HTTP/1.1 409");
                }
            }
            return json_encode($response);
        }
        /* 1:------------------------------method start here validateCode 1------------------------------*/
        static function validateCode($params){
            $response = ["status" => null, "message" => null, "data" => null];
            $result = DbMethods::validateCode($params);
            $error = strpos($result, 'mysql_Error:-');
            if($error === 0){
                $response["status"] = 500;
                $response["message"] = "Your device has lost connection due to database error.";
                $response["data"] = $result;
                header("HTTP/1.1 500");
            }
            else{
                if($result == "match"){
                    $response["status"] = 200;
                    $response["message"] = "matched";
                    header("HTTP/1.1 200");
                }
                else{
                    $response["status"] = 409;
                    $response["message"] = "notmatched";
                    header("HTTP/1.1 409");
                }
            }
            return json_encode($response);
        }
        /* 1:------------------------------method start here sendMT 1------------------------------*/
        static function sendMT($params){
            $response = ["status" => null, "message" => null, "data" => null];
            $result = DbMethods::sendMT($params);
            $error = strpos($result, 'mysql_Error:-');
            if($error === 0){
                $response["status"] = 500;
                $response["message"] = "Your device has lost connection due to database error.";
                $response["data"] = $result;
                header("HTTP/1.1 500");
            }
            else{
                if($result == "limitReached"){
                    $response["status"] = 409;
                    $response["message"] = "SMS limit reached";
                    //$response["data"] = $result['data'];
                    header("HTTP/1.1 409");
                }
                else{
                    $response["status"] = 200;
                    $response["message"] = "Response";
                    $response["data"] = $result;
                    header("HTTP/1.1 200");
                }
            }
            return json_encode($response);
        }
        /* 1:------------------------------method start here checksubstatus 1------------------------------*/
        static function checksubstatus($params){
            $response = ["status" => null, "message" => null, "data" => null];
            $result = DbMethods::checksubstatus($params);
            $error = strpos($result, 'mysql_Error:-');
            if($error === 0){
                $response["status"] = 500;
                $response["message"] = "Your device has lost connection due to database error.";
                $response["data"] = $result;
                header("HTTP/1.1 500");
            }
            else{
                if($result['code'] == 1){
                    $response["status"] = 200;
                    $response["message"] = "Response";
                    $response["data"] = $result['data'];
                    header("HTTP/1.1 200");
                }
                else{
                    $response["status"] = 409;
                    $response["message"] = $result['data'];
                    $response["data"] = $result['data'];
                    header("HTTP/1.1 409");
                }
            }
            return json_encode($response);
        }
        /* 1:------------------------------method start here subscribe 1------------------------------*/
        static function subscribe($params){
            $response = ["status" => null, "message" => null, "data" => null];
            $result = DbMethods::subscribe($params);
            $error = strpos($result, 'mysql_Error:-');
            if($error === 0){
                $response["status"] = 500;
                $response["message"] = "Your device has lost connection due to database error.";
                $response["data"] = $result;
                header("HTTP/1.1 500");
            }
            else{
                if($result['code'] == 0){
                    $response["status"] = 200;
                    $response["message"] = "Successfully subscribed";
                    header("HTTP/1.1 200");
                }
                else{
                    $response["status"] = 409;
                    $response["message"] = $result['data'];
                    $response["data"] = $result['data'];;
                    header("HTTP/1.1 409");
                }
            }
            return json_encode($response);
        }
        /* 1:------------------------------method start here unsubscribe 1------------------------------*/
        static function unsubscribe($params){
            $response = ["status" => null, "message" => null, "data" => null];
            $result = DbMethods::unsubscribe($params);
            $error = strpos($result, 'mysql_Error:-');
            if($error === 0){
                $response["status"] = 500;
                $response["message"] = "Your device has lost connection due to database error.";
                $response["data"] = $result;
                header("HTTP/1.1 500");
            }
            else{
                if($result['response']['responsestatus']['code'] == '-80'){
                    $response["status"] = 409;
                    $response["message"] = "Customer not found.";
                    $response["data"] = $result;
                    header("HTTP/1.1 409 Conflict");
                }
                else{
                    $response["status"] = 200;
                    $response["message"] = "Successfully unsubscribed.";
                    $response["data"] = $result;
                    header("HTTP/1.1 200 OK");
                }
            }
            return json_encode($response);
        }
        /* 1:------------------------------method start here mobileSubscription 1------------------------------*/
        static function mobileSubscription($params){
            $params['mobile'] = 'sms_sub';
            $result = DbMethods::subscribe($params);
            return "1";
        }
        /* 1:------------------------------method start here mobileUnsubscribe 1------------------------------*/
        static function mobileUnsubscribe($params){
            $params['mobile'] = 'sms_unsub';
            $result = DbMethods::unsubscribe($params);
            return "1";
            exit();
        }
        /* 1:------------------------------method start here subExtra 1------------------------------*/
        static function subExtra($params){
            $params['mobile'] = 'sms_sub';
            $result = DbMethods::subscribe($params);
            $resultoo = ["code" => '0'];
            if($result){
                if($result['code'] == '1' || $result['code'] == '-72' || $result['data'] == 'Success'){
                    $resultoo["code"] = '1';
                }
            }
            return json_encode($resultoo);
        }
        /* 1:------------------------------method start here unsubExtra 1------------------------------*/
        static function unsubExtra($params){
            $params['mobile'] = 'sms_unsub';
            $result = DbMethods::unsubscribe($params);
            $result['response']['responsestatus']['code'];
            $resultoo = ["code" => '0'];
            if($result){
                if($result['response']['responsestatus']['code'] == '1' || $result['response']['responsestatus']['code'] == '-77'){
                    $resultoo["code"] = '1';
                }
            }
            return json_encode($resultoo);
        }
        /* 1:------------------------------method start here addPremierUser 1------------------------------*/
        static function addPremierUser($params){
            $result = premierUserMethods::addPremierUser($params);
            $resultoo = ["code" => '0'];
            if($result){
                $resultoo["code"] = '1';
            }
            return json_encode($resultoo);
        }
        /* 1:------------------------------method start here unsubPremierUser 1------------------------------*/
        static function unsubPremierUser($params){
            $result = premierUserMethods::unsubscribe($params);
            $resultoo = ["code" => '0'];
            //if($result['response']['responsestatus']['code']=='1' ||  $result['response']['responsestatus']['code']=='-77')
            $resultoo["code"] = '1';
            return json_encode($resultoo);
        }
        /* 1:------------------------------method start here doChargeMonthly 1------------------------------*/
        static function doChargeMonthly($params){
            $response = ["status" => null, "message" => null, "data" => null];
            $result = DbMethods::doChargeMonthly($params);
            $error = strpos($result, 'mysql_Error:-');
            if($error === 0){
                $response["status"] = 500;
                $response["message"] = "Your device has lost connection due to database error.";
                $response["data"] = $result;
                header("HTTP/1.1 500");
            }
            else{
                if($result['code'] == 0){
                    $response["status"] = 200;
                    $response["message"] = "Successfully subscribed";
                    $response["data"] = $result['data'];
                    header("HTTP/1.1 200");
                }
                else{
                    $response["status"] = 409;
                    $response["message"] = $result['data'];
                    $response["data"] = $result['data'];
                    header("HTTP/1.1 409");
                }
            }
            return json_encode($response);
        }
        /* 1:------------------------------method start here doChargeWeekly 1------------------------------*/
        static function doChargeWeekly($params){
            $response = ["status" => null, "message" => null, "data" => null];
            $result = DbMethods::dochargeweek($params);
            $error = strpos($result, 'mysql_Error:-');
            if($error === 0){
                $response["status"] = 500;
                $response["message"] = "Your device has lost connection due to database error.";
                $response["data"] = $result;
                header("HTTP/1.1 500");
            }
            else{
                if($result['code'] == 0){
                    $response["status"] = 200;
                    $response["message"] = "Successfully subscribed";
                    $response["data"] = $result['data'];
                    header("HTTP/1.1 200");
                }
                else{
                    $response["status"] = 409;
                    $response["message"] = $result['data'];
                    $response["data"] = $result['data'];
                    header("HTTP/1.1 409");
                }
            }
            return json_encode($response);
        }
        /* 1:------------------------------method start here doChargeDaily 1------------------------------*/
        static function doChargeDaily($params){
            $response = ["status" => null, "message" => null, "data" => null];
            $result = DbMethods::doChargeDaily($params);
            $error = strpos($result, 'mysql_Error:-');
            if($error === 0){
                $response["status"] = 500;
                $response["message"] = "Your device has lost connection due to database error.";
                $response["data"] = $result;
                header("HTTP/1.1 500");
            }
            else{
                if($result['code'] == 0){
                    $response["status"] = 200;
                    $response["message"] = "Successfully subscribed";
                    $response["data"] = $result['data'];
                    header("HTTP/1.1 200");
                }
                else{
                    $response["status"] = 409;
                    $response["message"] = $result['data'];
                    $response["data"] = $result['data'];
                    header("HTTP/1.1 409");
                }
            }
            return json_encode($response);
        }
        /* 1:------------------------------method start here runNonRCronJob 1------------------------------*/
        static function eligibilitychecker($params){
            $response = ["status" => null, "message" => null, "data" => null];
            $result = premierUserMethods::eligibilitychecker($params);
            if($result == 'ELIGIBLE'){
                $premier_user = premierUserMethods::checkPremier($params);
                $response["status"] = 200;
                $response["message"] = 'ELIGIBLE';
                $response["data"] = $premier_user;
                header("HTTP/1.1 200");
                return json_encode($response);
            }
            else{
                $response["status"] = 409;
                $response["message"] = $result;
                header("HTTP/1.1 409");
                return json_encode($response);
            }
        }
        /* 1:------------------------------method start here cronjob 1------------------------------*/
        static function cronjob($params){
            $response = ["status" => null, "message" => null, "data" => null];
            $result = DbMethods::cronjob($params);
            $error = strpos($result, 'mysql_Error:-');
            if($error === 0){
                $response["status"] = 500;
                $response["message"] = "Your device has lost connection due to database error.";
                $response["data"] = $result;
                header("HTTP/1.1 500");
            }
            else{
                $response["status"] = 200;
                $response["message"] = "Response";
                $response["data"] = $result;
                header("HTTP/1.1 200");
            }
            return json_encode($response);
        }
        /* 1:------------------------------method start here addprimeir 1------------------------------*/
        static function addprimeir($params){
            $response = ["status" => null, "message" => null, "data" => null];
            $result = premierUserMethods::addprimeir($params);
            $error = strpos($result, 'mysql_Error:-');
            if($error === 0){
                $response["status"] = 500;
                $response["message"] = "Your device has lost connection due to database error.";
                $response["data"] = $result;
                header("HTTP/1.1 500");
            }
            else{
                $response["status"] = 200;
                $response["message"] = "Response";
                $response["data"] = $result;
                header("HTTP/1.1 200");
            }
            return json_encode($response);
        }
        /* 1:------------------------------method start here runNonRCronJob 1------------------------------*/
        static function runNonRCronJob($params){
            $response = ["status" => null, "message" => null, "data" => null];
            $result = nonUsersMethods::runNonRCronJob($params);
            $result = premierUserMethods::runPremierUserCronJob($params);
            $response["status"] = 200;
            $response["message"] = "Response";
            $response["data"] = $result;
            header("HTTP/1.1 200");
            return json_encode($response);
        }
        /* 1:------------------------------method start here AddSubscriptionContractRequest 1------------------------------*/
        static function AddSubscriptionContractRequest($params){
            $response = ["status" => null, "message" => null, "subscriptionContractId" => null];
            $result = DbMethods::AddSubscriptionContractRequest($params);
            $error = strpos($result, 'mysql_Error:-');
            if($error === 0){
                $response["status"] = 500;
                $response["message"] = "Your device has lost connection due to database error.";
                $response["data"] = $result;
                header("HTTP/1.1 500");
            }
            else{
                if($result['code'] == 0){
                    $response["status"] = 200;
                    $response["message"] = "Response";
                    $response["subscriptionContractId"] = $result['data'];
                    header("HTTP/1.1 200");
                }
                else{
                    $response["status"] = 409;
                    $response["message"] = $result['data'];
                    header("HTTP/1.1 409");
                }
            }
            return json_encode($response);
        }
        /* 1:------------------------------method start here AddSubscriptionContractRequest 1------------------------------*/
        static function SendSubscriptionContractVerificationSMS($params){
            $response = ["status" => null, "message" => null, "subscriptionContractId" => null];
            $result = DbMethods::SendSubscriptionContractVerificationSMS($params);
            $error = strpos($result, 'mysql_Error:-');
            if($error === 0){
                $response["status"] = 500;
                $response["message"] = "Your device has lost connection due to database error.";
                $response["data"] = $result;
                header("HTTP/1.1 500");
            }
            else{
                if($result['code'] == 0){
                    $response["status"] = 200;
                    $response["message"] = "Response";
                    $response["subscriptionContractId"] = $result['data'];
                    header("HTTP/1.1 200");
                }
                else{
                    $response["status"] = 409;
                    $response["message"] = $result['data'];
                    header("HTTP/1.1 409");
                }
            }
            return json_encode($response);
        }
        /* 1:------------------------------method start here AddSubscriptionContractRequest 1------------------------------*/
        static function VerifySubscriptionContract($params){
            $response = ["status" => null, "message" => null, "subscriptionContractId" => null];
            $result = DbMethods::VerifySubscriptionContract($params);
            $error = strpos($result, 'mysql_Error:-');
            if($error === 0){
                $response["status"] = 500;
                $response["message"] = "Your device has lost connection due to database error.";
                $response["data"] = $result;
                header("HTTP/1.1 500");
            }
            else{
                if($result['code'] == 0){
                    $response["status"] = 200;
                    $response["message"] = "Response";
                    $response["subscriptionContractId"] = $result['data'];
                    header("HTTP/1.1 200");
                }
                else{
                    $response["status"] = 409;
                    $response["message"] = $result['data'];
                    header("HTTP/1.1 409");
                }
            }
            return json_encode($response);
        }
        /* 1:------------------------------method start here CancelSubscriptionContractRequest 1------------------------------*/
        static function CancelSubscriptionContractRequest($params){
            $response = ["status" => null, "message" => null, "subscriptionContractId" => null];
            $result = DbMethods::CancelSubscriptionContractRequest($params);
            $error = strpos($result, 'mysql_Error:-');
            if($error === 0){
                $response["status"] = 500;
                $response["message"] = "Your device has lost connection due to database error.";
                $response["data"] = $result;
                header("HTTP/1.1 500");
            }
            else{
                if($result['code'] == 0){
                    $response["status"] = 200;
                    $response["message"] = "Contract is successfully Canceled.";
                    $response["subscriptionContractId"] = $result['data'];
                    header("HTTP/1.1 200");
                }
                else{
                    $response["status"] = 409;
                    $response["message"] = $result['data'];
                    header("HTTP/1.1 409");
                }
            }
            return json_encode($response);
        }
        /* 1:------------------------------method start here SendFreeMTMessage 1------------------------------*/
        static function SendFreeMTMessage($params){
            $response = ["status" => null, "message" => null, "data" => null];
            $result = DbMethods::SendFreeMTMessage($params);
            $error = strpos($result, 'mysql_Error:-');
            if($error === 0){
                $response["status"] = 500;
                $response["message"] = "Your device has lost connection due to database error.";
                $response["data"] = $result;
                header("HTTP/1.1 500");
            }
            else{
                if($result['code'] == 0){
                    $response["status"] = 200;
                    $response["message"] = "Message send.";
                    $response["data"] = 'sent';
                    header("HTTP/1.1 200");
                }
                else{
                    $response["status"] = 409;
                    $response["message"] = $result['data'];
                    header("HTTP/1.1 409");
                }
            }
            return json_encode($response);
        }
        /* 1:------------------------------method start here CancelSubscriptionContractRequest 1------------------------------*/
        static function tpayCallback($params){
            $response = ["status" => null, "message" => null, "data" => null];
            $result = DbMethods::tpayCallback($params);
            $error = strpos($result, 'mysql_Error:-');
            if($error === 0){
                $response["status"] = 500;
                $response["message"] = "Your device has lost connection due to database error.";
                $response["data"] = $result;
                header("HTTP/1.1 500");
            }
            else{
                $response["status"] = 200;
                $response["message"] = "Response";
                $response["data"] = $result;
                header("HTTP/1.1 200");
            }
            return json_encode($response);
        }
        /* END-----------------------------END END END END-----------------------------END-*/
    }
