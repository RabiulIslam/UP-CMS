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
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With,AccessToken');
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
                $regdouble = '/^[0-9]+(\.[0-9]{1,20})?$/';
                $bothmethods = outh::methodParamscheck();
                //print_r($bothmethods);
                extract($bothmethods);
                switch([$methodName, $method]){
                    /* 1:------------------------------method start here validatemsisdn------------------------------*/
                    case ["validatemsisdn", "POST"]: {
                        $errors = [];
                        if($params['phone'] == ''){
                            $errors['Name'] = "phone Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = phoneinvalidmsg;
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::validatemsisdn($params);
                        break;
                    }
                    /* 1:------------------------------method start here validateCode------------------------------*/
                    case ["validateCode", "POST"]: {
                        $errors = [];
                        $limit = 6;
                        $code = preg_match($pattern, $params['code']);
                        if($params['phone'] == ''){
                            $errors['Name'] = "phone Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = phoneinvalidmsg;
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['code'] == '' || (strlen($params['code']) != $limit) || ($code > 0)){
                            $errors['Name'] = "code Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = "Max size 6 characters";
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::validateCode($params);
                        break;
                    }
                    /* 1:------------------------------method start here sendMT------------------------------*/
                    case ["sendMT", "POST"]: {
                        $errors = [];
                        if($params['phone'] == ''){
                            $errors['Name'] = "phone Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = phoneinvalidmsg;
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::sendMT($params);
                        break;
                    }
                    /* 1:------------------------------method start here checksubstatus------------------------------*/
                    case ["checksubstatus", "POST"]: {
                        $errors = [];
                        if($params['phone'] == ''){
                            $errors['Name'] = "phone Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = phoneinvalidmsg;
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::checksubstatus($params);
                        break;
                    }
                    /* 1:------------------------------method start here subscribe------------------------------*/
                    case ["subscribe", "POST"]: {
                        $errors = [];
                        if($params['phone'] == ''){
                            $errors['Name'] = "phone Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = phoneinvalidmsg;
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::subscribe($params);
                        break;
                    }
                    /* 1:------------------------------method start here unsubscribe------------------------------*/
                    case ["unsubscribe", "GET"]: {
                        $errors = [];
                        if($params['Origin']){
                            $params['Msisdn'] = $params['Origin'];
                        }
                        else if($params['origin']){
                            $params['Msisdn'] = $params['origin'];
                        }
                        $params['phone'] = $params['Msisdn'];
                        if($params['phone'] == ''){
                            $errors['Name'] = "phone Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = phoneinvalidmsg;
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::unsubscribe($params);
                        break;
                    }
                    /* 1:------------------------------method start here unsubscribe------------------------------*/
                    case ["unsubscribe", "POST"]: {
                        $errors = [];
                        if($params['phone'] == ''){
                            $errors['Name'] = "phone Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = phoneinvalidmsg;
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::unsubscribe($params);
                        break;
                    }
                    /* 1:------------------------------method start here mobileSubscription------------------------------*/
                    case ["mobileSubscription", "POST"]: {
                        $errors = [];
                        header('Content-type: text/html; charset=utf-8');
                        //--//
                        if($params['Msisdn'] != ''){
                            $params['phone'] = $params['Msisdn'];
                        }
                        else if($params['msisdn'] != ''){
                            $params['phone'] = $params['msisdn'];
                        }
                        else if($params['Origin'] != ''){
                            $params['phone'] = $params['Origin'];
                        }
                        else if($params['origin'] != ''){
                            $params['phone'] = $params['origin'];
                        }
                        else if($params['Destination']){
                            $params['phone'] = $params['Destination'];
                        }
                        //--//
                        unset($params['Msisdn']);
                        unset($params['msisdn']);
                        unset($params['Origin']);
                        unset($params['origin']);
                        unset($params['Destination']);
                        unset($params['customer_id']);
                        //--//
                        if($params['phone'] == ''){
                            echo '0';
                            exit();
                        }
                        echo AppMethods::mobileSubscription($params);
                        break;
                    }
                    /* 1:------------------------------method start here mobileUnsubscribe------------------------------*/
                    case ["mobileUnsubscribe", "GET"]: {
                        $errors = [];
                        header('Content-type: text/html; charset=utf-8');
                        $letter_sub = ['subup', 'sub up', 'SUB UP', 'SUBUP', 'sub', 'SUB'];
                        $letter_unsub = [
                            'unsub',
                            'UNSUB',
                            'UNSUB URBAN POINT',
                            'UNSUBURBAN POINT',
                            'UNSUB URBANPOINT',
                            'UNSUBURBANPOINT',
                            'STOPCCT 7207',
                            'STOPCCT7207',
                            'STOPCCT 7019',
                            'STOPCCT7019',
                        ];
                        if($params['Msisdn'] != ''){
                            $params['phone'] = $params['Msisdn'];
                        }
                        else if($params['msisdn'] != ''){
                            $params['phone'] = $params['msisdn'];
                        }
                        else if($params['Origin'] != ''){
                            $params['phone'] = $params['Origin'];
                        }
                        else if($params['origin'] != ''){
                            $params['phone'] = $params['origin'];
                        }
                        else if($params['Destination']){
                            $params['phone'] = $params['Destination'];
                        }
                        unset($params['Msisdn']);
                        unset($params['msisdn']);
                        unset($params['Origin']);
                        unset($params['origin']);
                        unset($params['Destination']);
                        unset($params['customer_id']);
                        if($params['phone'] == ''){
                            echo '0';
                            exit();
                        }
                        if(isset($params['Text']) && in_array($params['Text'], $letter_sub)){
                            echo $addAdmin = AppMethods::mobileSubscription($params);
                            exit();
                        }
                        else if(isset($params['Text']) && in_array($params['Text'], $letter_unsub)){
                            echo $addAdmin = AppMethods::mobileUnsubscribe($params);
                            exit();
                        }
                        else{
                            echo '0';
                            exit();
                        }
                        break;
                    }
                    /* 1:------------------------------method start here mobileUnsubscribe------------------------------*/
                    /*case ["mobileUnsubscribe", "POST"]: {
                        $errors = [];
                        header('Content-type: text/html; charset=utf-8');
                        $letter_sub = ['subup', 'sub up', 'SUB UP', 'SUBUP', 'sub', 'SUB'];
                        $letter_unsub = [
                            'unsub',
                            'UNSUB',
                            'UNSUB URBAN POINT',
                            'UNSUBURBAN POINT',
                            'UNSUB URBANPOINT',
                            'UNSUBURBANPOINT',
                            'STOPCCT 7207',
                            'STOPCCT7207',
                            'STOPCCT 7019',
                            'STOPCCT7019',
                        ];
                        if($params['Msisdn'] != ''){
                            $params['phone'] = $params['Msisdn'];
                        }
                        else if($params['msisdn'] != ''){
                            $params['phone'] = $params['msisdn'];
                        }
                        else if($params['Origin'] != ''){
                            $params['phone'] = $params['Origin'];
                        }
                        else if($params['origin'] != ''){
                            $params['phone'] = $params['origin'];
                        }
                        else if($params['Destination']){
                            $params['phone'] = $params['Destination'];
                        }
                        unset($params['Msisdn']);
                        unset($params['msisdn']);
                        unset($params['Origin']);
                        unset($params['origin']);
                        unset($params['Destination']);
                        unset($params['customer_id']);
                        if($params['phone'] == ''){
                            echo '0';
                            exit();
                        }
                        if(isset($params['Text']) && in_array($params['Text'], $letter_sub)){
                            echo $addAdmin = AppMethods::mobileSubscription($params);
                            exit();
                        }
                        else if(isset($params['Text']) && in_array($params['Text'], $letter_unsub)){
                            echo $addAdmin = AppMethods::mobileUnsubscribe($params);
                            exit();
                        }
                        else{
                            echo '0';
                            exit();
                        }
                        break;
                    }*/
                    /* 1:------------------------------method start here mobileUnsubscribe------------------------------*/
                    case ["mobileUnsubscribe", "POST"]: {
                        $errors = [];
                        header('Content-type: text/html; charset=utf-8');
                        /*$letter_sub = ['subup', 'sub up', 'SUB UP', 'SUBUP', 'sub', 'SUB'];*/
                        /*$letter_unsub = [
                            'unsub',
                            'UNSUB',
                            'UNSUB URBAN POINT',
                            'UNSUBURBAN POINT',
                            'UNSUB URBANPOINT',
                            'UNSUBURBANPOINT',
                            'STOPCCT 7207',
                            'STOPCCT7207',
                            'STOPCCT 7019',
                            'STOPCCT7019',
                        ];*/
                        $letter_sub = [
                            'sub',
                            'subup',
                            'sub up',
                            'suburban',
                            'sub urban',
                            'suburbanpoint',
                            'suburban point',
                            'sub urbanpoint',
                            'sub urban point',
                        ];
                        $letter_unsub = [
                            'unsub',
                            'unsubup',
                            'unsub up',
                            'unsuburban',
                            'unsub urban',
                            'unsuburbanpoint',
                            'unsuburban point',
                            'unsub urbanpoint',
                            'unsub urban point',
                            'stopcct7019',
                            'stopcct7207',
                            'stopcct 7207',
                            'stopcct 7019',
                        ];
                        if($params['Msisdn'] != ''){
                            $params['phone'] = $params['Msisdn'];
                        }
                        else if($params['msisdn'] != ''){
                            $params['phone'] = $params['msisdn'];
                        }
                        else if($params['Origin'] != ''){
                            $params['phone'] = $params['Origin'];
                        }
                        else if($params['origin'] != ''){
                            $params['phone'] = $params['origin'];
                        }
                        else if($params['Destination']){
                            $params['phone'] = $params['Destination'];
                        }
                        unset($params['Msisdn']);
                        unset($params['msisdn']);
                        unset($params['Origin']);
                        unset($params['origin']);
                        unset($params['Destination']);
                        unset($params['customer_id']);
                        if($params['phone'] == ''){
                            echo '0';
                            exit();
                        }
                        $params_text = strtolower(trim($params['Text']));
                        if(!empty($params['bilal']) && $params['bilal'] == 'jafar'){
                            var_dump($params_text);
                            var_dump((!empty($params_text) && in_array($params_text, $letter_sub)));
                            var_dump((!empty($params_text) && in_array($params_text, $letter_unsub)));
                            exit();
                        }
                        if(!empty($params_text) && in_array($params_text, $letter_sub)){
                            echo $addAdmin = AppMethods::mobileSubscription($params);
                            exit();
                        }
                        else if(!empty($params_text) && in_array($params_text, $letter_unsub)){
                            echo $addAdmin = AppMethods::mobileUnsubscribe($params);
                            exit();
                        }
                        else{
                            echo '0';
                            exit();
                        }
                        break;
                    }
                    /* 1:------------------------------method start here subExtra------------------------------*/
                    case ["subExtra", "POST"]: {
                        $errors = [];
                        if($params['msisdn'] == ''){
                            $resultoo = ["code" => '0'];
                            echo json_encode($resultoo);
                            exit();
                        }
                        unset($params['customer_id']);
                        $params['phone'] = $params['msisdn'];
                        unset($params['msisdn']);
                        echo AppMethods::subExtra($params);
                        break;
                    }
                    /* 1:------------------------------method start here unsubExtra------------------------------*/
                    case ["unsubExtra", "POST"]: {
                        $errors = [];
                        if($params['msisdn'] == ''){
                            $resultoo = ["code" => '0'];
                            echo json_encode($resultoo);
                            exit();
                        }
                        $params['phone'] = $params['msisdn'];
                        unset($params['msisdn']);
                        echo AppMethods::unsubExtra($params);
                        break;
                    }
                    /* 1:------------------------------method start here addPremierUser------------------------------*/
                    case ["addPremierUser", "POST"]: {
                        $errors = [];
                        if($params['msisdn'] == ''){
                            $resultoo = ["code" => '0'];
                            echo json_encode($resultoo);
                            exit();
                        }
                        $params['phone'] = $params['msisdn'];
                        unset($params['msisdn']);
                        echo AppMethods::addPremierUser($params);
                        break;
                    }
                    /* 1:------------------------------method start here addPremierUser------------------------------*/
                    case ["unsubPremierUser", "POST"]: {
                        $errors = [];
                        if($params['msisdn'] == ''){
                            $resultoo = ["code" => '0'];
                            echo json_encode($resultoo);
                            exit();
                        }
                        $params['phone'] = $params['msisdn'];
                        unset($params['msisdn']);
                        echo AppMethods::unsubPremierUser($params);
                        break;
                    }
                    /* 1:------------------------------method start here doChargeMonthly------------------------------*/
                    case ["doChargeMonthly", "POST"]: {
                        $errors = [];
                        $user_id = preg_match($pattern, $params['user_id']);
                        if($params['user_id'] == ''){
                            $errors['Name'] = "user_id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = 'Max size 11';
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['phone'] == ''){
                            $errors['Name'] = "phone Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = phoneinvalidmsg;
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::doChargeMonthly($params);
                        break;
                    }
                    /* 1:------------------------------method start here doChargeWeekly------------------------------*/
                    case ["doChargeWeekly", "POST"]: {
                        $errors = [];
                        $user_id = preg_match($pattern, $params['user_id']);
                        if($params['user_id'] == ''){
                            $errors['Name'] = "user_id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = 'Max size 11';
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['phone'] == ''){
                            $errors['Name'] = "phone Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = phoneinvalidmsg;
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::doChargeWeekly($params);
                        break;
                    }
                    /* 1:------------------------------method start here doChargeDaily------------------------------*/
                    case ["doChargeDaily", "POST"]: {
                        $errors = [];
                        $user_id = preg_match($pattern, $params['user_id']);
                        if($params['user_id'] == ''){
                            $errors['Name'] = "user_id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = 'Max size 11';
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['phone'] == ''){
                            $errors['Name'] = "phone Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = phoneinvalidmsg;
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::doChargeDaily($params);
                        break;
                    }
                    /* 1:------------------------------method start here eligibilitychecker------------------------------*/
                    case ["eligibilitychecker", "POST"]: {
                        $errors = [];
                        if($params['phone'] == ''){
                            $errors['Name'] = "phone Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = phoneinvalidmsg;
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::eligibilitychecker($params);
                        break;
                    }
                    /* 1:------------------------------method start here cronjob------------------------------*/
                    case ["cronjob", "POST"]: {
                        echo AppMethods::cronjob($params);
                        break;
                    }
                    /* 1:------------------------------method start here runNonRCronJob------------------------------*/
                    case ["runNonRCronJob", "POST"]: {
                        echo AppMethods::runNonRCronJob($params);
                        break;
                    }
                    /* 1:------------------------------method start here addprimeir------------------------------*/
                    case ["addprimeir", "POST"]: {
                        echo AppMethods::addprimeir($params);
                        break;
                    }
                    /* 1:------------------------------method start here AddSubscriptionContractRequest------------------------------*/
                    case ["AddSubscriptionContractRequest", "POST"]: {
                        $errors = [];
                        $user_id = preg_match($pattern, $params['user_id']);
                        if($params['user_id'] == ''){
                            $errors['Name'] = "user_id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = 'Max size 11';
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['phone'] == ''){
                            $errors['Name'] = "phone Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = phoneinvalidmsg;
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::AddSubscriptionContractRequest($params);
                        break;
                    }
                    /* 1:------------------------------method start here subscriptionContractId------------------------------*/
                    case ["SendSubscriptionContractVerificationSMS", "POST"]: {
                        $errors = [];
                        if($params['subscriptionContractId'] == ''){
                            $errors['Name'] = "subscriptionContractId Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = 'Max size 100';
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::SendSubscriptionContractVerificationSMS($params);
                        break;
                    }
                    /* 1:------------------------------method start here VerifySubscriptionContract------------------------------*/
                    case ["VerifySubscriptionContract", "POST"]: {
                        $errors = [];
                        if($params['subscriptionContractId'] == ''){
                            $errors['Name'] = "subscriptionContractId Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = 'Max size 100';
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['pinCode'] == ''){
                            $errors['Name'] = "pinCode Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = 'Max size 100';
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::VerifySubscriptionContract($params);
                        break;
                    }
                    /* 1:------------------------------method start here CancelSubscriptionContractRequest------------------------------*/
                    case ["CancelSubscriptionContractRequest", "POST"]: {
                        $errors = [];
                        if($params['user_id'] == ''){
                            $errors['Name'] = "user_id Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = 'Max size 100';
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::CancelSubscriptionContractRequest($params);
                        break;
                    }
                    /* 1:------------------------------method start here SendFreeMTMessage------------------------------*/
                    case ["SendFreeMTMessage", "POST"]: {
                        $errors = [];
                        if($params['phone'] == ''){
                            $errors['Name'] = "phone Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = phoneinvalidmsg;
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        if($params['messageBody'] == ''){
                            $errors['Name'] = "messageBody Required";
                            $errors['type'] = "int";
                            $errors['formate_size'] = 'max length 200';
                            throw new Exception(implode(" | ", $errors), 400);
                        }
                        echo AppMethods::SendFreeMTMessage($params);
                        break;
                    }
                    /* 1:------------------------------method start here CancelSubscriptionContractRequest------------------------------*/
                    case ["tpayCallback", "GET"]: {
                        echo AppMethods::tpayCallback($params);
                        break;
                    }
                    /* 1:------------------------------default default default default default------------------------------*/
                    default : {
                        $response = [
                            "status"  => 406,
                            "message" => 'Invalid Method Name '.$methodName." Or Routes ".$_SERVER['REQUEST_METHOD'],
                            "data"    => null
                        ];
                        echo json_encode($response);
                        exit();
                    }
                }
            }
            catch(Exception $e){
                $response["status"] = $e->getCode();
                $response["message"] = $e->getMessage();
                echo json_encode($response);
                header("HTTP/1.1 ".$e->getCode()."");
            }
        }
    }
